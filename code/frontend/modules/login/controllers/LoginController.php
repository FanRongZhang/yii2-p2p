<?php

namespace frontend\modules\login\controllers;

use common\models\QfbMember;
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
use common\models\QfbSmsCode;
use frontend\models\SignupForm;
use common\service\MongoService\UserBehaviorService as UserMongoService;
use common\service\LogService;


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
				'testLimit'=>1,
                'backColor' => 0xE1E1E1,
                'foreColor' => 0x1d70d8,
                'maxLength' => '4', // 最多生成几个字符
                'minLength' => '4', // 最少生成几个字符
                'width' => '85',
                'height' => '40',
                'padding' => 0,
                // 'offset' =>2,
                'disturbCharCount' => 0,
                'transparent' => true,
            ],
        ];
    }

	public function registerBefore($post)
	{

		foreach($post as $key=>$val){
			if($key == "mobile" || $key == "password" || $key == "code")
				if (empty($val)) $this->error('您的信息输入不完整');
			if(($key == "account" && $val == "") || !isset($post['account'])) $post['account'] = $post['mobile'];
		}
		if(!isset($post['agreement'])){
			$this->error('请选阅读用户协议！');
		}

		//验证短信验证码
        $result=CommonService::checkVerify(intval($post['code']),$post['mobile'],6);

        if (!$result) {
        	return $this->error('短信验证码错误');
        }


		return true;
	}

	/**
	 * 注册
	 * @return string|\yii\web\Response
	 * @author panheng
     */
	public function actionRegister()
	{
		if(Yii::$app->request->getIsPost()){
			$data = Yii::$app->request->post('UMember');
			$this->registerBefore($data);
			$form['SignupForm'] = $data;
			$model = new SignupForm();
			if ($model->load($form)) {
				if ($user = $model->signup()) {
					if (Yii::$app->getUser()->login($user)) {
						$this->loginAfter();
						return $this->redirect('/login/login/regok');
					}
				}
			}
			return $this->render('register', [
				'model' => $model,
			]);
		}

		return $this->render('register');
	}

	/**
	 * 登录前处理
	 * @param $data
	 * @return \common\service\Ambigous
     */
	public function loginBefore($data)
	{
		if(!$this->createAction('captcha')->validate($data['verifyCode'], false))
			$this->error('验证码错误');

		$service = new UcenterService();
		$member = $service->findUserByMobile($data['mobile']);
		if(!$member) $this->error('账号不存在');
		$login = new LoginRecordService($member->id);
		$login_record = $login->dayFailLogin($member->id);
		$count_record = $login->countFailLogin($member->id);
		if($login_record > 2 && $count_record < 9){
			$this->error('密码错误次数过多，请明日再试');
		}elseif($count_record > 8){
			$service->updateMemberActive($member->id); //修改用户状态
			$this->error('您的账号存在风险已被冻结，请联系客服解冻!');
		}
		$memberModel = $service->findBymobile($data['mobile'], $data['password']);
		if (!$memberModel) {
			$ip = Yii::$app->request->userIP;
			$params=array(
				'flag'=>0,
				'type'=>1,
			);
			$login->saveRecord($member->id,$params,$ip);
			if ($login_record > 2 && $count_record < 9) {
				$this->error('密码错误次数过多，请明日再试!');
			} elseif($count_record > 8) {
				$service->updateMemberActive($member->id); //修改用户状态
				$this->error('您的账号存在风险已被冻结，请联系客服解冻!');
			}
			$this->error('密码错误!');
		}
		/*冻结屏蔽登录(0冻结 1启用)*/
		if (!$memberModel->status) {
			$this->error('帐号被冻结，请联系客服!');
		}

		return true;

	}


	/**
     * ---------------------------------------
     * 登录后处理方法
     * @author lijunwei
     * ---------------------------------------
     */
    public function loginAfter()
    {
		$member_id = \Yii::$app->user->identity->id;
		$login = new LoginRecordService($member_id);

		$login->deleteSuccessRecord($member_id);//密码输入正确 删除用户面膜输入错误的记录
		/** 切换数据库 **/
		$address = $this->getIpAddress();
		$ip = Yii::$app->request->userIP;
		$member = QfbMember::find()->where(['id'=>$member_id])->asArray()->one();
		$client = [
			'ip' => $member['last_ip'],
			'address' => $member['address'],
			'last_access_time' => $member['last_access_time'],
		];
		if(empty($member['last_ip']) || $member['last_ip'] == $ip){
			$client['ip_status'] = 1;  //常用地址
		}else{
			$client['ip_status'] = 0;  //非常用地址
		}
		$memberModel = QfbMember::findOne($member_id);
		$memberModel->last_ip = $ip;
		$memberModel->address = $address;
		$memberModel->last_access_time = time();
		$memberModel->channel_id = 3;
		$memberModel->save();

		/**将user_token存入mongodb来判断用户是否多点登录**/
		$userMongoService = new UserMongoService($member_id);
		$userToken = EncryptService::twiceMd5($member_id.time());
		$condition = ['member_id'=>$member_id];
		$params = ['user_token'=>$userToken];

		if(!$userMongoService->update($condition, $params)){
			$params['member_id'] = $member_id;
			$userMongoService->insert($params);
		}

		$client['user_token'] = $userToken;

		\Yii::$app->session->set('PHONE', $member['mobile']);
		\Yii::$app->session->set('LOGIN', array_merge($member,$client));

		return true;

    }

	/**
	 * Logs in a user.
	 *
	 * @return mixed
	 */
	public function actionLogin()
	{
		if (!\Yii::$app->user->isGuest && Yii::$app->session->get('LOGIN') != '') {
			return $this->goHome();
		}

		$data = Yii::$app->request->post();
		$model = new LoginForm();

		$member = isset($data['LoginForm']) ? $this->loginBefore($data['LoginForm']) : '';

		if($member){
			if ($model->load($data) && $model->login()) {
				$this->loginAfter();
				return $this->redirect('/index/index/index');
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

	        //验证短信验证码
	        $result=CommonService::checkVerify(intval($params['code']),$params['mobile'],8);

	        if (!$result) {
	        	$this->error('短信验证码错误');
	        }

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

		Yii::$app->user->logout();
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
    	$member_id=$this->mid ? $this->mid : 0;

    	if (empty($member_id)) {
    		$this->error('参数错误');
    	}
    	
        //更改用户状态
        $member = QfbMember::findOne($member_id);
        $member->is_dredge=9;
        $member->option_time=time();
        $member->save();

        //用户角色
        if ($member->member_type == 1) {
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

		// 记录日志
		$fileName = "RT_".$serviceName."_GATEWAY".".log";
		$content = "绑卡注册操作   执行时间：".date("Y-m-d H:i:s", time())."   用户编号：".$member_id."   请求数据：".json_encode($reqData)."\r\n";
		LogService::hkyh_write_log($fileName, $content);

        // 到银行页面注册
        $hkyh->createPostParam($serviceName,$reqData);
    }

    //注册协议
    public function actionXy()
    {
    	$id = Yii::$app->request->get('id');
    	$data = QfbAgreement::findOne($id);
    	return $this->render('xy',['data'=>$data]);
    }

	/**
	 *验证码
     */
	public function actionSendcode()
    {
        $mobile = Yii::$app->request->post('mobile');
		$type = Yii::$app->request->post('type');
		$captcha=Yii::$app->request->post('captcha');

		// 验证图形验证码
		if(empty($captcha)){
			echo json_encode(['status'=>'error','msg'=>'请输入图形验证码', 'error_type'=>'captcha']);exit;
		}else if(!$this->createAction('captcha')->validate($captcha,false)) {
			echo json_encode(['status'=>'error','msg'=>'验证码错误', 'error_type'=>'captcha']);exit;
		}

		if(empty($mobile)){
			echo json_encode(['status'=>'error','msg'=>'请输入手机号', 'error_type'=>'mobile']);
			exit;
		}

		/**验证用户是否注册*/
		$UCmemberServ = new UcenterService();
		$checkMobile = $UCmemberServ->findUserByMobile($mobile);

		if ($checkMobile && $type == 6 ) {
			//已注册
			echo json_encode(['status'=>'error', 'msg'=>'该号码已注册，请直接登录', 'error_type'=>'mobile']);exit;
		}else if (!$checkMobile && $type == 8 ) {
			//已注册
			echo json_encode(['status'=>'error', 'msg'=>'该号码不存在', 'error_type'=>'mobile']);exit;
		}

		// 关闭真实-发送短信验证码 true
        $result = CommonService::sendMobileVerifyCode($mobile, $type, true);

        if ($result) {
        	echo json_encode(['status'=>'success','msg'=>'发送成功','code'=>$result]);exit;
        } else {
        	echo json_encode(['status'=>'error','msg'=>'发送失败，请重试！','code'=>$result, 'error_type'=>'sendcode']);exit;
        }
    }

	/**
	 * 判断是否是常用地址
	 * @param $member
	 * @return array
     */
	public function isUsualAddress($member)
	{
		$address = $this->clientIp();

		if($member->last_ip == $address['ip']){
			$address['ip_status'] = 1;  //常用地址状态
		}else{
			$address['ip_status'] = 0;  //非常用地址状态
		}

		return $address;
	}


}
