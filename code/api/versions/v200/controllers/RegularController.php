<?php
namespace api\versions\v200\controllers;
use common\enum\ChannelEnum;
use common\models\QfbBank;
use common\models\QfbOrderFix;
use yii;
use common\service\ApiService;
use api\common\helpers\ReseponseCode as Code;
use api\common\BaseController;
use common\service\OrderFixService;
use common\models\orderFix\OrderFixSmall;
use common\service\MemberService;
use common\service\OrderFixLogService;
use Exception;
/**
 * 定期
 * @auther xl
 */
class RegularController extends BaseController{

	/**
	 * 购买此定期的用户列表
	 * [actionRecode description]
	 * @return [type] [description]
	 */
	public function actionRecord(){
		$params = $this->getParams();
		$service =new OrderFixService();
		$data=$service->buyMemberList(intval($params['id']),$params['page'],$params['limit']);
		return ApiService::send(Code::HTTP_OK,'',$data);
	}


	/**
	 * 购买定期
	 * [actionCreate description]
	 * @return [type] [description]
	 */
	public function actionCreate(){
		$params = $this->getParams();
		if($params['money']<=0) return ApiService::send(Code::COMMON_ERROR_CODE,'请输入金额');
		if($params['id']=='')return ApiService::send(Code::COMMON_ERROR_CODE,'产品不存在');
		$tran = yii::$app->db->beginTransaction();
		try{
			$orderFixService = new OrderFixService();
			$data=[
				'product_id'=>intval($params['id']),
				'money'=>$params['money'],
				'member_voucher_id'=>intval($params['member_voucher_id']),
				'member_id'=>1,
                'bank_id'=>isset($params['bank_id'])?$params['bank_id']:"",
                'payment'=>$params['payment'],
			];
            if($params['payment'] == 3) {
                if($params['zf_pwd']=='') return ApiService::send(Code::ZFPWD_ERROR_CODE);
                if (MemberService::checkZfPwd($this->member_id, $params['zf_pwd']) == false) return ApiService::send(Code::ZFPWD_ERROR_CODE);
                /** 零钱支付创建订单 */
                if ($orderFixService->doSaveByMoney($data) == false) {
                    throw new Exception($orderFixService->findOneMessage());
                }
                /**易联支付创建订单*/
            }elseif($params['payment'] == ChannelEnum::YILIAN){
                $res = $orderFixService->payOrder($data);
                if(!$res){
                    throw new Exception($orderFixService->findOneMessage());
                }
            } else if ($params['payment'] == ChannelEnum::HUARONG) {
                $res = $orderFixService->payOrder($data);
                if(!$res){
                    throw new Exception($orderFixService->findOneMessage());
                }
            } else {
                return ApiService::send(Code::COMMON_ERROR_CODE,'请选择支付方式');
            }
			$tran->commit();
		}catch(Exception $e){
			$tran->rollback();
			return ApiService::send(Code::COMMON_ERROR_CODE,$e->getMessage());
		}

		$bank = QfbBank::findOne($params['bank_id']);
        $mobile = $bank->mobile;

		$smallModel = new OrderFixSmall();
		$orderModel = $orderFixService->getModel();
		$smallModel->load([
		    'id'=>$orderModel->id,
            'sn'=>$orderModel->sn,
            'money'=>$orderModel->money,
            'mobile' => $mobile,
        ])->getTips(2);
		return ApiService::send(Code::HTTP_OK,'',$smallModel);
	}

    /**
     * 参数：
     * private String access_token;
     * private int page;//页码，默认为1
     * private int limit;//一页显示数量，默认为10
     *
     */
    public function actionWaitDetail()
    {
        $params = $this->getParams();
        $page = $params['page']?$params['page']:1;
        $limit = $params['limit']?$params['limit']:10;

        $orderFix = new OrderFixLogService();
        $order_financing = $orderFix->getOrderFixLogByMemberId($this->member_id,$page,$limit);

        $money_sum = 0;
        $arr_data = [];
        if ($order_financing)
        {
            foreach ($order_financing as $v)
            {
                $arr_data[$v->order_id]['content'] = $v->remark;
                $arr_data[$v->order_id]['money'] = $v->money;
                $arr_id[] = $v->order_id;
            }
        }

        $arr_id = array();
        $financing_time = array();
        $content = array();
        //待发放推荐奖励
        $order_money = $orderFix->getOrderFixLogByMemberId($this->member_id);
        if ($order_money)
        {
            foreach ($order_money as $val)
            {
                $money_sum += $val->money;
            }
        }
        //获取结束时间
        if ($arr_id)
        {
            $financing_time = QfbOrderFix::find()->select(['id','end_time'])->where(['in','id',$arr_id])->all();
        }
        if ($financing_time)
        {
            foreach ($financing_time as $val)
            {
                $end_time = $val->end_time + 3600 * 24;
                $arr_data[$val->id]['end_time'] = date("Y-m-d",$end_time);
            }
        }
        //拼接content
        if ($arr_data)
        {
            foreach ($arr_data as $arr)
            {
                $content[]['content'] = $arr['content'].'，'.'预计'.$arr['end_time'].'给您发放'.$arr['money'].'元奖励';
            }
        }

        $data = ['money'=>(string)$money_sum,'list'=>$content];
        return [
            'code' => Code::HTTP_OK,
            'msg' => '请求成功',
            'data' => $data
        ];
    }
}