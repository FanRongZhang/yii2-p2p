<?php
namespace common\service;

use Yii;
use common\models\QfbProductDetail;

class AssetService extends BaseService
{
	/**
     * 创建标的
     * @param 
     * @return mixed
     */
    public function createByProject($data)
    {
    	//获取产品简介
    	$content = QfbProductDetail::find()->select(['content'])->where(['product_id' => $data->id])->one();
    	$rate = $data->year_rate/100;
        $hkyh = \Yii::$app->Hkyh;
        // 创建标的
        $serviceName = 'ESTABLISH_PROJECT';

        //平台用户编号  -必填
        $reqData['platformUserNo'] = $data['member_id'];
        // 请求流水号  --流水号 --不允许重复
        $reqData['requestNo'] = $this->getBindSn();
        // 标的号 --必填
        $reqData['projectNo'] = $data->sn; 
        // 标的金额 --必填
        $reqData['projectAmount'] = $data->stock_money;  
        // 标的名称 --必填
        $reqData['projectName'] = $data->product_name; 
        // 标的描述 --非必填
        $reqData['projectDescription'] = $content->content; 
        // 见【标的类型】 --必填
        $reqData['projectType'] = 'STANDARDPOWDER'; 
        // 标的期限（单位：天） --非必填
        $reqData['projectPeriod'] = $data->invest_day; 
        // 年化利率 --必填
        $reqData['annualInterestRate'] = $rate; 
        // 见【还款方式】（只做记录，不做严格校验） --必填
        $reqData['repaymentWay'] = 'ONE_TIME_SERVICING'; 
        // 标的扩展信息 --非必填
        // $reqData['extend'] = 'hhhh'; 

        $result = $hkyh->createPostParam($serviceName,$reqData);
        //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，
        return $result;
        // var_dump($result);
        // exit;
    }


    /**
     * 用户预处理---支付
     * @param 
     * @return mixed
     */
    public function preTransaction($data)
    {
        $hkyh = \Yii::$app->Hkyh;

        // 用户预处理
        $serviceName = 'USER_PRE_TRANSACTION';

        // 流水号
        // $liushui = $this->getBindSn('UPT');

        // 请求流水号
        $reqData['requestNo'] = $data['liushui']; //$sn; 
        // 出款人平台用户编号
        $reqData['platformUserNo'] = $data['member_id'];
        // 根据业务的不同，需要传入不同的值，见【预处理业务类型】。
        $reqData['bizType'] = $data['type'];//'TENDER';'REPAYMENT'
        // 冻结金额
        $reqData['amount'] = $data['money'];
        // 预备使用的红包金额，只记录不冻结，仅限投标业务类型
        // $reqData['preMarketingAmount'] = '';
        // 超过此时间即页面过期
        $reqData['expired'] = date('YmdHis', time()+5*60);
        // 备注
        // $reqData['remark'] = 'Dq20170606170522ERD9XT';
        // 页面回跳 URL
        $reqData['redirectUrl'] = $hkyh->RETURN_URL; //页面回跳 URL --必填*/
        $reqData['projectNo'] = $data['sn'];//$data['sn'];Dq20170606170522ERD9XT 标的号 --必填

        // 到银行页面投标
        $hkyh->createPostParam($serviceName,$reqData);
        
    }


    /**
     * 批量处理
     * @param 
     * @return mixed
     */
    public function batchTrato($data, $product)
    {
        $hkyh = \Yii::$app->Hkyh;

        $serviceName = 'ASYNC_TRANSACTION';

        if(count($data) != count($data, 1)){
            foreach ($data as $key => $value) {
                $details[$key]['bizType'] = $value['type'];  // 业务类型 -- 投标确认
                $details[$key]['freezeRequestNo'] = $value['sn'];  // 预处理请求流水号 -- 订单里面的sn
                $details[$key]['sourcePlatformUserNo'] = (int)$value['member_id'];  // 出款方用户编号 -- 投资人编号
                $details[$key]['targetPlatformUserNo'] = $product->member_id;  // 收款方用户编号 -- 借款人编号
                $details[$key]['amount'] = $value['pay_money'];  // 交易金额 -- 投资人投资额
                $details[$key]['income'] = $value['pay_money']*($product['year_rate']/100)*$product['invest_day']/$this->yearDay();  //利息
            }
        }else{

            $details = [
                // 投标订单1
                [
                    // 业务类型 -- 投标确认
                    'bizType'=>$data['type'],
                    // 预处理请求流水号
                    'freezeRequestNo'=>$data['sn'],   //订单里面的sn
                    // 出款方用户编号
                    'sourcePlatformUserNo'=>$data['member_id'],  //投资人编号
                    // 收款方用户编号
                    'targetPlatformUserNo'=>$product->member_id,  //借款人编号
                    // 交易金额
                    'amount'=>$data['pay_money'],  //投资人投资额
                    // 平台商户自定义参数，平台交易时传入的自定义参数
                    // 'customDefine'=>'',
                ],
                // 收取借款人佣金
                [
                    // 业务类型  -- 平台佣金
                    'bizType'=>'COMMISSION',
                    // 出款账户
                    'sourcePlatformUserNo'=>$product->member_id,  //借款人编号
                    // 金额
                    'amount'=>$data['pay_money']*0.05,
                    // 自定义参数
                    'customDefine'=>'1111',
                ],
            ];

            $bizDetails =
                [
                    [
                        // 交易预处理号
                        'requestNo'=>$this->getBindSn(),
                        // 标的号
                        'projectNo'=>$product->sn,   //产品表的sn
                        // 投标
                        'tradeType'=>$data['type'],
                        'details'=>($details)
                    ]
                ];

        }

        $reqData['batchNo'] = $product->id;  //产品(标的)id
        $reqData['bizDetails'] = ($bizDetails);

        $result = $hkyh->createPostParam($serviceName,($reqData));
        return $result;

    }


    /**
     * 设置流水号
     */
    public function getBindSn($type='')
    {
        //生成随机字母+数字
        $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";
        $len = strlen($str);
        for ($i = 0; $i < 6; $i++) {
            $code .= $str{rand(0, $len - 1)};
        }
        return $type . date('YmdHis') . $code;
    }

    /**
     * 计算今年是否是闰年
     * @return int
     */
    protected function yearDay()
    {
        $year = date('Y', time());
        $day = 365;
        if(($year%4 == 0 && $year%100 != 0) || $year%400 == 0){
            $day = 366;
        }

        return $day;
    }
}