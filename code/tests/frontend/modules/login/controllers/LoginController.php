<?php

namespace frontend\modules\login\controllers;

use Yii;
use frontend\models\LoginForm;
use common\service\BankService;
use common\service\MemberService;
use common\service\CommonService;
use common\service\UcenterService;
use common\service\MemberInfoService;
use common\service\LoginRecordService;
use frontend\controllers\WebController;
use common\extension\middleware\EncryptService;

/*
 * ---------------------------------------
 * PC端登录注册控制器 
 * @author phphome@qq.com 
 * ---------------------------------------
 */
class LoginController extends WebController
{
	/**
     * ---------------------------------------
     * 验证码
     * @author lijunwei
     * ---------------------------------------
     */
    public function actions()
    {
        return [
             'error' => [
                 'class' => 'yii\web\ErrorAction',
             ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'backColor' => 0xE1E1E1,
                'foreColor' => 0x1d70d8,
                'maxLength' => '4', // 最多生成几个字符
                'minLength' => '4', // 最少生成几个字符
                'width' => '85',
                'height' => '40',
                'padding' => 0,
            ],
        ];
    }


	/**
     * ---------------------------------------
     * 注册方法
     * @author lijunwei
     * ---------------------------------------
     */
	public function actionRegister()
	{
		if(Yii::$app->request->getIsPost()){
			$post = $this->post('UMember');

			foreach($post as $key=>$val){
	            if($key == "mobile" || $key == "password" || $key == "code")
	                if (empty($val)) $this->error('您的信息输入不完整');
	            if(($key == "account" && $val == "") || !isset($post['account'])) $post['account'] = $post['mobile'];
	        }
	        if(isset($post['agreement'])){
	        	$this->error('请选阅读用户协议！');
	        }
	        // if(!CommonService::checkVerify($post['code'], $post['mobile'],CommonService::VERIFY_TYPE_REG)){
	        // 	$this->error('您填写的验证码有误');
	        // }

	        // if($post['mobile'] == $post['r_mobile']) $this->error('推荐人不能是自己!');
	        $UCmemberServ = new UcenterService();
	        /**验证用户是否注册*/
        	$checkMobile = $UCmemberServ->findUserByMobile($post['mobile']);

        	if(!$checkMobile) {
        		if($this->createAction('captcha')->validate( $post['captcha'],false)) {  //图形验证码
        			//加密密码
        			$post['password'] = EncryptService::twiceMd5($post['password']);
		            $result = $UCmemberServ->createUcMember($post);
		            if ($result) {
		                $tran = Yii::$app->db->beginTransaction(); 
		                try {
		                    $QFBmemberServ = new MemberService();
		                    /**验证推荐人是否存在*/
		                    // if($post['r_mobile']!="") {
		                    //     $checkR_mobile = $QFBmemberServ->findUserByRmobile($post['r_mobile']);
		                    //     if (!$checkR_mobile) $this->error('您填写的推荐人不存在');
		                    // }

		                    $post['id'] = $result->id;
		                    /**创建用户基本信息*/
		                    $result = $QFBmemberServ->createMember($post);

		                    if (empty($result['errors'])) {

		                        /**创建用户钱包信息*/
		                        $post['member_id'] = $result->id;
		                        $result = $QFBmemberServ->createMemberMoney($post);
		                        if (empty($result['errors'])) {

		                            /**创建用户详细信息*/
		                            $post['member_id'] = $result->member_id;
		                            $QFBmemberServ->createMemberInfo($post);
		                            if (empty($result['errors'])) {
		                            	\Yii::$app->session->set('PHONE', $post['mobile']);
										\Yii::$app->session->set('LOGIN', $post);
		                                $tran->commit();
		                                $this->success("注册成功",'/login/login/regok');
		                            } else
		                                throw new Exception("创建用户详细信息失败");
		                        } else
		                            throw new Exception("创建用户钱包数据失败");
		                    } else
		                        throw new Exception("创建用户基本信息失败");
		                } catch (\Exception $e) {
		                    $errors = $e->getMessage();
		                    $tran->rollback();
		                    $this->error($errors);
		                }
		            } else {
		            	$this->error('创建用户中心数据失败');
		            }
		        } else {
	                $this->error('验证码错误！');
	            }
	        } else {
	        	$this->error('该手机号码已存在');
	        }
		}

		return $this->render('register');
	}


	/**
     * ---------------------------------------
     * 登录方法
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionLogin()
    {
    	if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $service = new UcenterService();
	    $model = $service->getModel();
        if(Yii::$app->request->getIsPost()){
        	$post = $this->post('UMember');

        	if($this->createAction('captcha')->validate( $post['captcha'],false)) {  //图形验证码
		        foreach ($post as $v) {
		            if ($v == '') {
		            	$this->error('参数缺失');
		            }
		        }

		        if ($model->load(['UMember' => $post]) && $post) {
		        	$mobile = $service->isMobile($post);
		            if (!$mobile) {
		            	$this->error('账号不存在');
		            }
		            $login = new LoginRecordService($mobile->id);
		            $login_record = $login->dayFailLogin($mobile->id);
		            $count_record = $login->countFailLogin($mobile->id);
		            if($login_record > 2 && $count_record < 9){
		            	$this->error('密码错误次数过多，请明日再试');
		            }elseif($count_record > 8){
		                $service->updateMemberActive($mobile->id); //修改用户状态
		                $this->error('您的账号存在风险已被冻结，请联系客服解冻!');
		            }
		            $memberModel = $service->findBymobile($model->mobile, $model->password);
		            if (!$memberModel) {
		                $ip = Yii::$app->request->userIP;
		                $params=array(
		                    'flag'=>0,
		                    'type'=>1,
		                );
		                $save = $login->saveRecord($mobile->id,$params,$ip);
		                if ($login_record > 2 && $count_record < 9) {
		                	$this->error('密码错误次数过多，请明日再试!');
		                } elseif($count_record > 8) {
		                    $service->updateMemberActive($mobile->id); //修改用户状态
		                    $this->error('您的账号存在风险已被冻结，请联系客服解冻!');
		                }
		                $this->error('密码错误!');
		            }
		            /*冻结屏蔽登录(0冻结 1启用)*/
		            if (!$memberModel->status) {
		            	$this->error('帐号被冻结，请联系客服!');
		            }
		            /** 是否激活中视钱包用户 */
		            if ($memberModel->status == 2) {
		                $is_active = false;
		            } else {
		                $is_active = true;
		            }
		            $login->deleteSuccessRecord($memberModel->id);//密码输入正确 删除用户面膜输入错误的记录
		            /** 切换数据库 **/
					// \Yii::$app->set('db',yii::$app->components['dm_qfb']);

		            $db = Yii::$app->get('db');

		            $member = MemberService::findModelById($memberModel['id']);
		            $memberInfoService = new MemberInfoService();
		            $memberInfo = $memberInfoService->findBySql("select realname,avatar,card_no,nickname,is_verify from {$db->tablePrefix}member_info where member_id = {$memberModel['id']}");
		            //推荐人的手机号
		            // if ($member->r_member_id != 1) {
		            //     $r_mobile_user = QfbMember::findOne(['id' => $member->r_member_id]);
		            //     $r_mobile = $r_mobile_user->mobile;
		            // }

		            //用户银行卡数量
		            $bank = new BankService();
		            if ($memberModel->id) {
		                $card_num = $bank->getCount($memberModel['id']);
		                if (!$card_num) {
		                    $card_num = 0;
		                }
		            }

		            $walletService = new MemberService();
	            	$walletMember = $walletService->setAccessToken($memberModel->id, $member);
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

	            	\Yii::$app->session->set('PHONE', $member['mobile']);
					\Yii::$app->session->set('LOGIN', $data);
					return $this->redirect(['/index/index/index']);
		            // return $this->goBack();
		        }
		    } else {
                $this->error('验证码错误！');
            }
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * ---------------------------------------
     * 忘记密码
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionResetpassword()
    {
    	if(Yii::$app->request->getIsPost()){
	        $params = $this->post('UMember');
	        foreach ($params as $value) {
	            if ($value == null) {
	            	$this->error('参数缺失!');
	            }
	        }

	        $memberService = new MemberService();
	        $mobile = $memberService->findMemberByMobile($params);
	        if (!$mobile) {
	        	$this->error('手机账号不存在!');
	        }

	        $member_id = $mobile->id;
	        // if (!CommonService::checkVerify($params['code'], $params['mobile'],CommonService::VERIFY_TYPE_FORGET)) {
	        // 	$this->error('验证码错误!');
	        // }
	        $member = new MemberService();
	        $result = $member->updateLoginPassword($member_id,$params);
	        if ($result) {
	        	return $this->redirect(['/login/login/login']);
	        	// $this->success('修改成功!');
	        } else {
	        	$this->error('修改失败!');
	        }
	    }

        return $this->render('reset', [
            // 'model' => $model,
        ]);
    }


    /**
     * ---------------------------------------
     * 退出登录方法
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionLogout()
    {
 
    	Yii::$app->session->set('LOGIN','');
    	Yii::$app->session->set('PHONE','');

        return $this->redirect('/index/index/index');
    }

    //注册成功页面
    public function actionRegok()
    {
    	$memberdata=Yii::$app->session->get('LOGIN');
    	return $this->render('regok',['memberdata'=>$memberdata]);
    }

    //银行开户
    public function actionRegBank()
    {
    	$member_id=Yii::$app->request->get('member_id');
    	$member_type=Yii::$app->request->get('member_type');
    	//var_dump($member_type);exit;
    	if (empty($member_type) || empty($member_id)) {
    		$this->error('参数错误1');
    	}
    	if ($member_type==1) {
    		$userRole='INVESTOR';
    	} else {
    		$userRole='BORROWERS';
    	}
    	//拼接数据跳到银行页面
    	$hkyh = \Yii::$app->Hkyh;

        // 个人绑卡注册
        $serviceName = 'PERSONAL_REGISTER_EXPAND';

        // 流水号
        $sn = $this->getBindSn('RT');

        $reqData['platformUserNo'] = $member_id;
        $reqData['requestNo'] = $sn;
        $reqData['idCardType'] = 'PRC_ID';
        $reqData['userRole'] = $userRole;
        $reqData['userLimitType'] = 'ID_CARD_NO_UNIQUE';
        $reqData['checkType'] = 'LIMIT';
        $reqData['redirectUrl'] =  $hkyh->RETURN_PC_URL;

        // 到银行页面注册
        $hkyh->createPostParam($serviceName,$reqData);
    }

    //注册协议
    public function actionXy()
    {
    	return $this->render('xy');
    }

}
