<?php
namespace common\service;

use common\service\UcenterService;
use common\service\MemberService;
use common\models\QfbVouchers;
use common\service\MemberInfoService;
use common\service\OrderService;
use api\common\helpers\ReseponseCode as Code;

use yii\base\Exception;
use yii;

/**
 * 分享注册送流量业务逻辑
 * @author xiaoma <xiaomalover@gmail.com>
 *
 */
class ShareServiceYn extends BaseService
{

    /**
     * params array 
     * @param String mobile 注册人手机号
     * @param String r_mobile 分享人（推荐人）手机号
     * @param String password  密码
     * 注册一能积金
     */
    public static function register($params)
    {

        foreach ($params as $key => $value) {
            if($key == 'mobile' || $key == 'verify' || $key == 'password') 
                if(empty($value)) return Json::encode(['code'=>Code::COMMON_ERROR_CODE, 'msg'=>'您的信息输入不完整']);
        }

        $UCmemberServ = new UcenterService();

        // 验证会员中心数据库是否存在
        $checkMobile = $UCmemberServ->findUserByMobile($params['mobile']);

        if(!$checkMobile) {
            
            /**验证推荐人是否存在 应该省去*/
            if(!empty($params['r_mobile'])){
                $checkR_mobile = $UCmemberServ->findUserByMobile($params['r_mobile']);
                if (!$checkR_mobile) return ['code' => Code::COMMON_ERROR_CODE, 'msg' => "您填写的推荐人不存在"];
            }
           
            // 非默认数据库添加数据
            $result = $UCmemberServ->createUcMember($params);
            
            if($result) {

                $tran = Yii::$app->db->beginTransaction();

                try {

                    $QFBmemberServ = new MemberService();

                    $params['id'] = $result->id;

                    /**创建用户基本信息*/
                    $result = $QFBmemberServ->createMember($params);

                    if (empty($result['errors'])) {

                        /**创建用户钱包信息*/
                        $params['member_id'] = $result->id;

                        $result = $QFBmemberServ->createMemberMoney($params);

                        if (empty($result['errors'])) {

                            /**创建用户详细信息*/
                            $params['member_id'] = $result->member_id;
                            $result =$QFBmemberServ->createMemberInfo($params);

                            if (empty($result['errors'])) {

                                // 确定只送一个红包，如果表中存在多种红包呢？？？？？？？ 
                                /******注册送红包 START*******/
                                $vouchers = QfbVouchers::find()->where('type=1 AND status=1 AND end_time>'.time())->one();
                                
                                if($vouchers){

                                    $QFBmemberServ = new MemberService();
                                    
                                    $OrderService = new OrderService();
                                    $random_numbers = $OrderService->random_numbers(6);
                                    
                                    $data['vouchers_id']    = $vouchers->id;
                                    $data['member_id']      = $result->member_id;
                                    $data['receive_time']   = time();
                                    $data['invalid_time']   = time()+(86400*$vouchers->valid_days);
                                    $data['remark']         = '注册送代金券';
                                    $data['sn']             = 'ZC'.$random_numbers;
                                    
                                    $result = $QFBmemberServ->sendVouchers($data);
                                   
                                    if($result['errors']){
                                        throw new Exception("发放代金券失败");
                                    }
                                }

                                /******注册送红包 END*******/
                                $tran->commit();
                                return ['code' => Code::HTTP_OK, 'msg' => "注册成功", 'data'=>['member_id'=>$result->member_id]];
                            } else
                                throw new Exception("创建用户详细信息失败");
                        } else
                            throw new Exception("创建用户钱包数据失败");
                    } else
                        throw new Exception("创建用户基本信息失败");
                } catch (\Exception $e) {
                    $errors = $e->getMessage();
                    $tran->rollback();
                    return array('code' => Code::COMMON_ERROR_CODE, 'msg' => $errors);
                }
            } else
                return array('code' => Code::COMMON_ERROR_CODE, 'msg' => "创建用户中心数据失败");
        }else
            return array('code' => Code::COMMON_ERROR_CODE, 'msg' => "该手机号码已存在");
    }

   
   
}

?>