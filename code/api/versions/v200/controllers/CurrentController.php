<?php 
namespace api\versions\v200\controllers;
use common\enum\ChannelEnum;
use common\models\QfbBank;
use yii;
use common\service\ApiService;
use api\common\helpers\ReseponseCode as Code;
use api\common\BaseController;
use common\service\OrderService;
use common\service\MemberService;
use common\models\orderFix\OrderFixSmall;
use common\enum\ProductEnum;
use Exception;
/**
 * 活期订单
 */
class CurrentController extends BaseController{
	public function actionCreate(){
		//获取参数
		$params = $this->getParams();
        $service = new OrderService();
        //零钱获取
        if($params['payment'] == 3) {
            if ($params['zf_pwd'] == '') return ApiService::send(Code::ZFPWD_ERROR_CODE);
            if (MemberService::checkZfPwd($this->member_id, $params['zf_pwd']) == false) return ApiService::send(Code::ZFPWD_ERROR_CODE);
            //$service = new OrderService();
            $tran = Yii::$app->db->beginTransaction();
            try {

                $data = [
                    'money' => bcadd($params['money'], 0, 2),
                    'product_id' => intval($params['product_id'])
                ];
                if ($service->createLive($data) == false) throw new Exception($service->findOneMessage());
                $tran->commit();
            } catch (Exception $e) {
                $tran->rollBack();
                return ApiService::send(Code::COMMON_ERROR_CODE, $e->getMessage());
            }
            //易联支付活期
        }elseif($params['payment'] == ChannelEnum::ZHENGLIAN){
            $tran = Yii::$app->db->beginTransaction();
            try{
                $data = [
                    'money' => bcadd($params['money'], 0, 2),
                    'product_id' => intval($params['product_id']),
                    'payment'=>intval($params['payment']),
                    'bank_id'=>intval($params['bank_id']),
                ];
                if ($service->zlCreateLiveOrder($data) == false) throw new Exception($service->findOneMessage());
                $tran->commit();
            }catch (\Exception $e){
                $tran->rollBack();
                return ApiService::send(Code::COMMON_ERROR_CODE, $e->getMessage());
            }
        } else if($params['payment'] == ChannelEnum::HUARONG || $params['payment'] == ChannelEnum::YILIAN) {  //华融支付,易联支付
            $tran = Yii::$app->db->beginTransaction();
            try{
                //生成活期订单
                $res = $service->current(
                    $this->member_id =1,
                    $params['product_id'],
                    $params['money'],
                    $params['bank_id'],
                    $params['payment']
                );
                if ($res) {
                    $tran->commit();
                } else {
                    throw new Exception($service->message);
                }
            }catch (Exception $e){
                $tran->rollBack();
                return ApiService::send(Code::COMMON_ERROR_CODE, $e->getMessage());
            }
        }

        //获取bank对应的手机号
        $bank = QfbBank::findOne($params['bank_id']);
        $mobile = $bank->mobile;

        //组织返回数据
		$smallModel = new OrderFixSmall();
		$orderModel = $service->getModel();
		$smallModel->load([
		    'id'=>$orderModel->id,
            'sn'=>$orderModel->sn,
            'money'=>$orderModel->money,
            'mobile' => $mobile,
        ])->getTips(ProductEnum::LIVE);
		return ApiService::send(Code::HTTP_OK,'',$smallModel);

	}
}
