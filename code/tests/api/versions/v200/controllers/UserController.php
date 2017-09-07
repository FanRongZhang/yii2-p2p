<?php

namespace api\versions\v200\controllers;

use api\common\BaseController;
use common\models\QfbMember;
use common\models\QfbVouchers;
use common\service\MemberInfoService;
use common\service\OrderService;
use League\Flysystem\Exception;
use yii\rest\Controller;
use yii;
use common\service\AssetService;
use common\service\ApiService;
use common\service\OrderFixService;
use common\service\MemberService;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use common\service\UcenterService;
use common\service\LoginRecordService;
use common\service\BankService;
use api\common\helpers\ReseponseCode as Code;
use common\service\CommonService;
use common\extension\middleware\EncryptService;
use api\common\UserSafeBehavior;
use common\models\QfbProduct;
use common\models\QfbOrderFix;
use common\service\HkyhService;

/**
 * Class UserController
 * @package rest\versions\v1\controllers
 */
class UserController extends BaseController
{
    /**
     * 封装行为
     * @return Array 所有行为的数组
     */
    public function behaviors()
    {
        //继承父类所有行为
        $behaviors = parent::behaviors();

        //登录验签行为
        $behaviors['usersafe'] = [
            'class' => UserSafeBehavior::className(),
            'actions' => [
                'login',
                'reset-password',
                'register',
            ],
        ];

        return $behaviors;
    }


    /** 用户登录
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        $service = new UcenterService();
        $model = $service->getModel();
        $post = $this->getParams();

        foreach ($post as $key=>$v)
        {
            if ($v == '')
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '参数缺失'
                ];
            }
        }

        if ($post && $model->load(['UMember' => $post]))
        {
            $mobile = $service->isMobile($post);
            if (!$mobile)
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '账号不存在',
                ];
            }
            $login = new LoginRecordService($mobile->id);
            $login_record = $login->dayFailLogin($mobile->id);
            $count_record = $login->countFailLogin($mobile->id);

            if($login_record >= 3 && $count_record < 9){
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '密码错误次数过多，请明日再试'
                ];
            }elseif($count_record > 8){
                $service->updateMemberActive($mobile->id); //修改用户状态
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '您的账号存在风险已被冻结，请联系客服解冻!'
                ];
            }
            $memberModel = $service->findBymobile($model->mobile, $model->password);
            if (!$memberModel)
            {
                $ip = Yii::$app->request->userIP;
                $params=array(
                    'flag'=>0,
                    'type'=>1,
                );
                $save = $login->saveRecord($mobile->id,$params,$ip);
                $login_record = $login->dayFailLogin($mobile->id);
                if ($login_record >= 3 && $count_record < 9)
                {
                    return [
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '密码错误次数过多，请明日再试'
                    ];
                }
                elseif($count_record > 8)
                {
                    $service->updateMemberActive($mobile->id); //修改用户状态
                    return [
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '您的账号存在风险已被冻结，请联系客服解冻!'
                    ];
                }
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '密码错误'
                ];
            }
//            if (!$memberModel)
//            {
//                return [
//                    'code' => Code::COMMON_ERROR_CODE,
//                    'msg' => '账号或密码错误'
//                ];
//            }
            /*冻结屏蔽登录(0冻结 1启用)*/
            if (!$memberModel->status)
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '帐号被冻结，请联系客服！'
                ];
            }
            /** 是否激活中视钱包用户 */
            if ($memberModel->status == 2) {
                $is_active = false;
            } else {
                $is_active = true;
            }
            $login->deleteSuccessRecord($memberModel->id);//密码输入正确 删除用户面膜输入错误的记录

            /** 切换数据库 **/
//            \Yii::$app->set('db',yii::$app->components['dm_qfb']);

            $db = Yii::$app->get('db');

            $member = MemberService::findModelById($memberModel['id']);
            $memberInfoService = new MemberInfoService();
            $memberInfo = $memberInfoService->findBySql("select realname,avatar,card_no,nickname,is_verify from {$db->tablePrefix}member_info where member_id = {$memberModel['id']}");
            //推荐人的手机号
            if ($member->r_member_id != 1)
            {
                $r_mobile_user = QfbMember::findOne(['id' => $member->r_member_id]);
                $r_mobile = $r_mobile_user->mobile;
            }

            //用户银行卡数量
            $bank = new BankService();
            if ($memberModel->id)
            {
                $card_num = $bank->getCount($memberModel['id']);
                if (!$card_num)
                {
                    $card_num = 0;
                }
            }
            $walletService = new MemberService();
            $walletMember = $walletService->setAccessToken($memberModel->id, $member);
            $change = $walletService->isChangeDevice($memberModel->id, $post);                           //用户是否切换设备
            if ($change)
            {
                $is_change = true;
            }
            else
            {
                $is_change = false;
            }
            $is_change = false;                                                                             //测试需要
            $params = Yii::$app->params;
            $data = [
                'username' => $member['account'],
                'mobile' => $member['mobile'],
                'id' => $member['id'],
                'realname' => $memberInfo[0]['realname'],
                'avatar' => $params['img_domain'].'/'.$memberInfo[0]['avatar'],
                'idcard' => $memberInfo[0]['card_no'],
                'is_verify' => $memberInfo[0]['is_verify']? true : false,
                'is_zf' => $member['zf_pwd'] ? true : false,
                'access_token' => $walletMember['access_token'],
                'encrypt_key' => $params['encrypt_key'],
                'encrypt_iv' => $params['encrypt_iv'],
                'nickname' => isset($memberInfo[0]['nickname'])?$memberInfo[0]['nickname']:'',
                'r_mobile' => isset($r_mobile) ? $r_mobile : '',
                'card_no' => $card_num,
                'session_id' => session_id(),
                'is_change' => $is_change,
                'is_active' => $is_active,
            ];
            return [ 'code' => Code::HTTP_OK, 'msg' => '登录成功', 'data' => $data ];
        }
    }

    /**
     * 用户注册
     * @orderby lwj
     */
    public function actionRegister(){
        $params=$this->getParams();
        foreach($params as $key=>$val)
        {
            if($key == "mobile" || $key == "password" || $key == "code")
                if (empty($val)) return ['code' => Code::COMMON_ERROR_CODE, 'msg' => "您的信息输入不完整"];
            if(($key == "account" && $val == "") || !isset($params['account'])) $params['account'] = $params['mobile'];
        }
        if(!CommonService::checkVerify($params['code'], $params['mobile'],CommonService::VERIFY_TYPE_REG))
        {
            //return ['code' => Code::COMMON_ERROR_CODE, 'msg' => "您填写的验证码有误"];
        }
        if($params['mobile'] == $params['r_mobile']) return ['code' => Code::COMMON_ERROR_CODE, 'msg' => "推荐人不能是自己"];

        $UCmemberServ = new UcenterService();

        /**验证用户是否注册*/
        $checkMobile = $UCmemberServ->findUserByMobile($params['mobile']);

        if(!$checkMobile) {
            /**验证推荐人是否存在*/
            if($params['r_mobile']!="") {
                $checkR_mobile = $UCmemberServ->findUserByMobile($params['r_mobile']);
                if (!$checkR_mobile) return ['code' => Code::COMMON_ERROR_CODE, 'msg' => "您填写的推荐人不存在"];
            }

            $result = $UCmemberServ->createUcMember($params);
            if ($result) {
                $tran = Yii::$app->db->beginTransaction();
                try {
                    $QFBmemberServ = new MemberService();
                    $params['id']=$result->id;
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
                                /******注册送红包 START*******/
                                $vouchers = QfbVouchers::find()->where('type=1 AND status=1 AND end_time>'.time())->one();
                                if($vouchers){
                                    $data['vouchers_id']    = $vouchers->id;
                                    $data['member_id']      = $result->member_id;
                                    $data['receive_time']   = time();
                                    $data['invalid_time']   = time()+(86400*$vouchers->valid_days);
                                    $data['remark']         = '注册送代金券';
                                    $data['sn']             = 'ZC'.OrderService::random_numbers(6);
                                    $result = $QFBmemberServ->sendVouchers($data);
                                    if($result['errors']){
                                        throw new Exception("发放代金券失败");
                                    }
                                }
                                /******注册送红包 END*******/
                                $tran->commit();
                                return ['code' => Code::HTTP_OK, 'msg' => "注册成功"];
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

    /**
     * 绑卡注册银行虚拟账户
     **/
    public function actionHkyhRegister()
    {
        $params = $this->getParams();

        $memberService = new MemberService();
        $member_data = $memberService->findMemberIdByToken($params['access_token']);

        $member_id = $member_data->id;
        $is_dredge = $member_data->is_dredge;

        if(empty($member_data) || empty($params['access_token']))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'nologin','type'=>'hkyh-register','msg'=>'未登录']);

        if(!empty($is_dredge))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'already','type'=>'hkyh-register','msg'=>'用户已开通过银行账户']);

        // 调用接口查询，确定是否开户
        $getHkyhUser = MemberService::getHkyhUser($member_id);

        // 未开户
        if($getHkyhUser['code'] != code::HTTP_OK){

            $hkyh = \Yii::$app->Hkyh;

            // 个人绑卡注册
            $serviceName = 'PERSONAL_REGISTER_EXPAND';

            // 流水号
            $sn = $this->getBindSn('RT');

            $reqData['platformUserNo'] = $member_id;
            $reqData['requestNo'] = $sn;
            $reqData['idCardType'] = 'PRC_ID';
            $reqData['userRole'] = 'INVESTOR';
            $reqData['userLimitType'] = 'ID_CARD_NO_UNIQUE';
            $reqData['checkType'] = 'LIMIT';
            $reqData['redirectUrl'] =  $hkyh->RETURN_URL;

            // 到银行页面注册
            $hkyh->createPostParam($serviceName,$reqData);
        }

        // 处理下数据
        unset($params);
        $json_de_data = json_decode($getHkyhUser['data']['data'], true);
        $json_de_data ["realName"] = $json_de_data ["name"];
        unset ( $json_de_data ["name"] );

        $params['respData'] = json_encode($json_de_data);

        // 已在银行系统开户且未在平台做标识，处理平台标识处理
        $hkyhService = new HkyhService();

        $result = $hkyhService->hkyhRester($params);

        if($result['code'] == code::HTTP_OK){
            $status = 'success';
        }else{
            $status = 'already';
        }

        return $this->redirect([
            '/v200/notify/hkyh-return',
            'status'=>$status,
            'order_id'=>$result['data']['platformUserNo'],
            'type'=>'hkyh-register',
            'msg'=>$result['msg'],
        ]);

    }

    /**
     * 查询用户预处理
     */
    public function actionGetTransaction(){

        $hkyh = \Yii::$app->Hkyh;

        // 用户预处理
        $serviceName = 'QUERY_PROJECT_INFORMATION';

        $reqData['projectNo'] = 'Dq20170606141200X1NTQZ';  //标的号 --必填

        $result = $hkyh->createPostParam($serviceName,$reqData);
        echo "<pre>";
        var_dump(json_decode($result['data']));die;

    }

    /**
     * 用户预处理---支付
     **/
    public function actionUserPreTransaction()
    {
        $params = $this->getParams();
        if($params['money'] <= 0) return ApiService::send(Code::COMMON_ERROR_CODE,'请输入金额');
        if($params['id']=='')return ApiService::send(Code::COMMON_ERROR_CODE,'产品不存在');

        $memberService = new MemberService();
        $member_data = $memberService->findMemberIdByToken($params['access_token']);

        $member_id = $member_data->id;
        $is_dredge = $member_data->is_dredge;

        if(empty($member_data) || empty($params['access_token']))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'nologin','type'=>'hkyh-userPreTransaction','msg'=>'未登录']);

        $orderFixService = new OrderFixService();

        $data=[
            'product_id' => intval($params['id']),
            'money' => $params['money'],
            'member_voucher_id' => intval($params['member_voucher_id']),
            'member_id' => $member_id,
        ];

        /** 创建订单 */
        if ($orderFixService->doSaveByMoney($data) == false) {
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'error','order_id'=>'0','type'=>'hkyh-userPreTransaction','msg'=>$orderFixService->findOneMessage()]);
        } else {

            $sn = QfbProduct::find()->select('sn')->where('id=:product_id', [':product_id'=>$params['id']])->asArray()->one();

            $liushui = QfbOrderFix::find()->select('sn')->where('product_id=:product_id and member_id=:member_id', [':product_id'=>$params['id'], ':member_id'=>$member_id])->orderBy('id desc')->limit(1)->asArray()->one();

            $result = [
                'member_id' => $member_id,
                'money' => $params['money'],
                'sn' => $sn['sn'],
                'liushui' => $liushui['sn'],
                'type' => 'TENDER', //投标
            ];
            $service = new AssetService;
            $res = $service->preTransaction($result);

            return $this->redirect(['/v200/notify/hkyh-return','status'=>'error','order_id'=>'0','type'=>'hkyh-userPreTransaction','msg'=>'接口异常']);
        }
    }


    /**
     * 用户忘记密码 重新设置密码
     * @return array
     */
    public function actionResetPassword(){
        $params = $this->getParams();
        foreach ($params as $value)
        {
            if ($value == null)
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '参数缺失！'
                ];
            }
        }

        $memberService = new MemberService();
        $mobile = $memberService->findMemberByMobile($params);
        if (!$mobile)
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '手机账号不存在！'
            ];
        }

        $member_id = $mobile->id;
        if (!CommonService::checkVerify($params['code'], $params['mobile'],CommonService::VERIFY_TYPE_FORGET))
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '验证码错误！'
            ];
        }
        $member = new MemberService();
        $result = $member->updateLoginPassword($member_id,$params);
        if (is_bool($result) && $result) {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '修改成功！',
            ];
        } elseif (is_array($result)) {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => $result['msg']
            ];
        } else {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => "修改失败",
            ];
        }
    }

    /**
     * 修改银行交易密码
     */
    public function actionHkyhResetPassword(){

        $params = $this->getParams();

        $memberService = new MemberService();
        $member_data = $memberService->findMemberIdByToken($params['access_token']);

        $member_id = $member_data->id;
        $is_dredge = $member_data->is_dredge;

        if(empty($member_data) || empty($params['access_token']))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'nologin','type'=>'hkyh-userPreTransaction']);

        $hkyh = \Yii::$app->Hkyh;

        // 流水号
        $sn = $this->getBindSn('RP');

        $serviceName = 'RESET_PASSWORD';
        $reqData['requestNo'] = $sn;
        $reqData['platformUserNo'] = $member_id;
        $reqData['redirectUrl'] = $hkyh->RETURN_URL;

        $result = $hkyh->createPostParam($serviceName,$reqData);

        return $this->redirect(['/v200/notify/hkyh-return','status'=>'error', 'order_id'=>'0', 'type'=>'hkyh-resetPassword', 'msg'=>'接口异常']);

    }

    /**
     * 单笔交易测试
     * @return array
     */
    public function actionTest()
    {
        $params = $this->getParams();
        $memberService = new MemberService();
        $member_data = $memberService->findMemberIdByToken($params['access_token']);

        $member_id = $member_data->id;

        if(empty($member_data) || empty($params['access_token']))
            return $this->redirect(['/v200/notify/hkyh-return','status'=>'nologin','type'=>'hkyh-register']);

        $hkyh = \Yii::$app->Hkyh;

        // 用户预处理
        $serviceName = 'SYNC_TRANSACTION';

        $reqData['requestNo'] = 'DQ20170609220424UL5AA5';  //请求流水号 --必填
        $reqData['tradeType'] = 'TENDER';  //交易类型 --必填
        $reqData['projectNo'] = 'Dq20170606141200X1NTQZ';  //标的号 --非必填
        //$reqData['saleRequestNo'] = 'Dq20170606141200X1NTQZ';  //债权出让请求流水号 --非必填
        $reqData['details'] = [
            [
                'sourcePlatformUserNo' => '4a20170606180903',  //出款方用户编号 --非必填
                'bizType' => 'TENDER',  //业务类型 --必填
                'amount' => '10',  //交易金额（有利息时为本息和） --必填
            ]
        ];

        $result = $hkyh->createPostParam($serviceName,$reqData);
        echo "<pre>";
        var_dump(json_decode($result['data']));die;
    }


}
