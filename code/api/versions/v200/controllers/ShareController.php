<?php
namespace api\versions\v200\controllers;

use common\models\Vmember;

use common\service\LogService;
use yii\web\Controller;
use yii\web\Response;
use yii;
use yii\helpers\Json;
use yii\captcha\Captcha;
use common\service\CommonService;
use common\extension\WxJsSdk;
use common\enum\AgreementEnum;
use common\service\ApiService;
use common\service\ShareServiceYn;
use common\service\MemberInfoService;
use common\service\MemberService;
use common\service\UcenterService;

use api\common\helpers\ReseponseCode as Code;

/*
use common\dlbService\DlbMemberService;
use common\service\MemberService;*/
use common\service\ShareService;
/**
 * Share controller
 * @author xiaoma <xiaomalover@gmail.com>
 * QFB 分享注册
 */
class ShareController extends Controller
{
	public $layout = "main";

    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
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
     * 用户注册
     * @orderby lwj
     */
    public function actionRegister(){
        
        // 判断是不是post请求
        if(Yii::$app->request->isPost){

            $params = Yii::$app->request->post();
            $mobile = trim($params['mobile']);
            $verify = trim($params['verify']);
            $password = trim($params['password']);
            $r_mobile = trim($params['r_mobile']);
            $type = trim($params['type']);
            
            if(empty($mobile) || empty($verify) || empty($password))
                return Json::encode(['code'=>Code::COMMON_ERROR_CODE, 'msg'=>'您的信息输入不完整']);

            // 推荐人不能是自己
            if($mobile == $r_mobile) 
                return Json::encode(['code'=>Code::COMMON_ERROR_CODE, 'msg'=>'推荐人不能是自己']);

            // 短信验证
            $res = CommonService::checkVerify($verify, $mobile, CommonService::VERIFY_TYPE_REG);

            if($res){

                $params['account'] = $mobile;
                
                $reg = ShareServiceYn::register($params);

                if($reg['code'] == 200){
                    Yii::$app->session['user_id'] = $reg['data']['member_id'];
                }

                return Json::encode($reg);
            }else{
                return Json::encode(['code'=>Code::NO_EXIST, 'msg'=>'短信验证码错误']);
            }
        }

        $this->getView()->title = "活期收益比余额宝高4倍，我们都在用！注册就送流量！";

        $flowList = $nickname = $wxParam = $member_id = $mobile = '';

        if(isset($_GET['member_id'])){
            $member_id = $_GET['member_id'];
            
            //获取推荐人的用户信息
            $rinfo = MemberInfoService::getMemberInfo($member_id);
            $member_data = MemberService::findModelById($member_id);
            
            $nickname = $rinfo['nickname'];
            $mobile = $member_data['mobile'];

            //微信分享js参数准备
            $wjs = new WxJsSdk("wx93580722cf7e4646", "373ef065e9e98a02da844ca777298e4e");
            $wxParam = $wjs->getSignPackage();
        }

        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render('register',compact('flowList', 'nickname', 'wxParam', 'member_id','mobile'));
    }

    /**
     * 注册成功页面
     */
    public function actionSuccess()
    {

    	$this->getView()->title = "活期收益比余额宝高4倍，我们都在用！注册就送流量！";
        $member_id = isset($_GET['member_id']) ? $_GET['member_id'] : 0;
        if(Yii::$app->session['user_id'] && Yii::$app->session['user_id'] == $member_id){
            
            // $flowList = ShareService::getFlowList(10);
            // $flow = ShareService::getFlowInfo($member_id);
            //微信分享js参数准备
            // $wjs = new WxJsSdk("wx93580722cf7e4646", "373ef065e9e98a02da844ca777298e4e");
            // $wxParam = $wjs->getSignPackage();
            
            $member_data = MemberService::findModelById($member_id, '', 'mobile');
            $mobile = $member_data['mobile'];
            
            Yii::$app->response->format = Response::FORMAT_HTML;
            //return $this->render('success',compact('flowList','flow','wxParam'));
            return $this->render('success', compact('mobile'));
        }else{

            // 页面跳转
            $hostInfo = Yii::$app->request->hostInfo;
            header("location:".$hostInfo."/share/register?member_id=".$member_id);

            // return $this->redirect(["register?member_id=".$member_id]);
        }
    }

    /**
     * ajax 验证手机号是否已经注册
     */
    public function actionIsRegisted(){

        if(Yii::$app->request->isPost){
            $params = Yii::$app->request->post();
            $mobile = trim($params['mobile']);
            $type = trim($params['type']);
            if ($type == CommonService::VERIFY_TYPE_REG){
                $member = new MemberService();
                $userModel = $member->findMemberByMobile($mobile);
              
                if ($userModel){
                    return Json::encode(['code' => Code::COMMON_ERROR_CODE, 'msg' => '手机号已注册！']);
                }
            }else{
                return Json::encode(['code' => Code::HTTP_FORBIDDEN, 'msg' => '非法操作！']);
            }
        }

    }

    /**
     * ajax获取验证码
     */
    public function actionGetVerify()
    {
        if(Yii::$app->request->isPost){
            $params = Yii::$app->request->post();
            $mobile = trim($params['mobile']);
            $catv = trim($params['catv']);
            $type = trim($params['type']);
           
            if(empty($mobile) || empty($catv) || empty($type))
               return Json::encode([ Code::COMMON_ERROR_CODE, 'msg'=>'您的信息输入不完整']);
            if (!CommonService::getType($type))
                return Json::encode(['code' => Code::COMMON_ERROR_CODE, 'msg' => '短信类型参数有误！']);

            // 验证图片验证码
            if(!$this->createAction('captcha')->validate($catv, false)) 
                return Json::encode(['code' => Code::HTTP_UNAUTHORIZED, 'msg' => '输入验证码有误！']);

            if ($type == CommonService::VERIFY_TYPE_REG){
                $member = new MemberService();
                $userModel = $member->findMemberByMobile($mobile);
              
                if ($userModel){
                    $user = new UcenterService();
                    $memberModel = $user->getModel();
                    $member_s = $memberModel->find()->where(['=','id', $userModel->id])->one();
                   
                    if (!empty($member_s))
                        return Json::encode(['code' => Code::COMMON_ERROR_CODE, 'msg' => '帐号已在一能积金存在，请登录进行平移！']);
                    else
                        return Json::encode(['code' => Code::COMMON_ERROR_CODE, 'msg' => '手机号已注册！']);
                }
            }

            $service = new CommonService();
            // 开始测试 $debug=true  
            $result = $service->sendMobileVerifyCode($mobile, $type, $debug=false);
            if ($result){
                $data = ['session_id'=>session_id(), 'result'=>$result];
                return Json::encode(['code' => Code::HTTP_OK, 'msg' => '发送成功', 'data' => $data]);
            }else{
                return Json::encode(['code' => Code::COMMON_ERROR_CODE, 'msg' => '请求失败！']);
            }
        }
        
        // 无效操作
        return Json::encode(['code' => Code::COMMON_ERROR_CODE, 'msg' => '请求失败！']);
    }

    /**
     * 访问协议地址
     * @return string
     */
    public function actionFile(){

        Yii::$app->response->format = Response::FORMAT_HTML;
        $type = Yii::$app->request->post('type') ? Yii::$app->request->post('type') : Yii::$app->request->get('type');
      
        if(!$type){
            echo "type 参数不能为空";
            exit;
        }

        switch($type)
        {
            case 'registerdeal':
                return $this->render('registerdeal');
            case 'regular':
                return $this->render('regular');
            case 'current':
                return $this->render('current');
            case 'protocol':
                $this->getView()->title = "一能积金服务协议";
                return $this->render('protocol');
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    /**
     * 下载页面
     */
    public function actionDownload()
    {
        $this->getView()->title = "活期收益比余额宝高4倍，我们都在用！注册就送流量！";
    	Yii::$app->response->format = Response::FORMAT_HTML;
    	return $this->render("download");
    }

    /**
     * 充值回调
     */
    public function actionEnsure()
    {
        $params = Yii::$app->request->post();
        ShareService::ensureFlow($params);
    }

    /**
     * ios9设置页面
     */
    public function actionSetting()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render("setting");
    }

    /**
     * 财富计划注册页面
     */
    public function actionCfRegister()
    {
    	$this->getView()->title = "[有人@你]你创业，我出钱！点击领取900元！";

    	$cfList = ShareService::getCfList(10);
        $wjs = new WxJsSdk("wx93580722cf7e4646", "373ef065e9e98a02da844ca777298e4e");
        $wxParam = $wjs->getSignPackage();

        $mobile = $nickname = '';
        if(isset($_GET['member_id'])){
        	$member_id = $_GET['member_id'];
        	//获取推荐人的用户信息
        	$rinfo = ShareService::getUserInfo($member_id);
        	$mobile = $rinfo['mobile'];
            $nickname = $rinfo['nickname'];
        }

        $params = Yii::$app->request->post();
        if(count($params)>1){
        	$mobile = $params['mobile'];
        	$verify = $params['verify'];
        	$password = $params['password'];
        	$r_mobile = $params['r_mobile'];
        	$res = CommonService::checkVerify($verify, $mobile, CommonService::VERIFY_TYPE_REG);
        	if($res){
        		$reg = ShareService::cfRegister($mobile, $r_mobile, $password);
        		if($reg['code'] == 200){
        			yii::$app->session['user_id'] = $reg['data']['member_id'];
        		}
        		return Json::encode($reg);
        	}else{
        		return Json::encode(['code'=>205,'msg'=>'验证错误']);
        	}
        }
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render('cf-register',compact('cfList','wxParam','mobile','nickname'));
    }

    /**
     * 财富计划注册成功页面
     */
    public function actionCfSuccess()
    {
        $this->getView()->title = "[有人@你]你创业，我出钱！点击领取900元！";
        Yii::$app->response->format = Response::FORMAT_HTML;
        $wjs = new WxJsSdk("wx93580722cf7e4646", "373ef065e9e98a02da844ca777298e4e");
        $wxParam = $wjs->getSignPackage();
        return $this->render('cf-success',compact('wxParam'));
    }

    /**
     * 发送短信的接口
     */
    public function actionSendSms()
    {
        $params = Yii::$app->request->post();
        if(isset($params['mobile']) && isset($params['content']) && isset($params['key'])){
            $sign = md5("qfb!@#$");
            $mobile = $params['mobile'];
            $content = $params['content'];
            $key = $params['key'];
            $verify_key = md5($mobile.$content.$sign);
            if($key == $verify_key){
                $bl = CommonService::pubSendNewSms($mobile,$content);
                if($bl){
                    $res = ['code'=>200,'msg'=>'验证码发送成功'];
                }else{
                    $res = ['code'=>500,'msg'=>'验证码发送失败'];
                }
            }else{
                $res = ['code'=>500,'msg'=>'安全校验失败'];
            }
        }else{
            $res = ['code'=>500,'msg'=>'参数缺失'];
        }
        return Json::encode($res);
    }


    public function actionFlow()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render("flow");
    }


    public function actionFortune()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render("fortune");
    }

    /**
    *定期理财
    */
    public function actionRegular(){
        Yii::$app->response->format = Response::FORMAT_HTML;
        $day=null;
        switch ($_REQUEST['type']) {
            case 30:
                $day=30;
                break;
            case 90:
                $day=90;
                break;
            case 180:
                $day=180;
                break;
            case 365:
                $day=365;
                break;
            default:
				$day=30;
                # code...
                break;
        }
        return $this->render('regular'.$day);
    }

    public function actionPlanned(){
        Yii::$app->response->format = Response::FORMAT_HTML;
        $day=null;
        switch ($_REQUEST['type']) {
            case 90:
                $day=90;
                break;
            case 180:
                $day=180;
                break;
            case 365:
                $day=365;
                break;
            default:
                $day=90;
                # code...
                break;
        }
        return $this->render('plans'.$day);
    }

    public function actionAgreement(){
        Yii::$app->response->format = Response::FORMAT_HTML;
        switch ($_REQUEST['type']) {
            case "regular":
                $day = "regular";
                break;
            case "current":
                $day = "current";
                break;
            case "protocol":
                $day = "protocol";
                break;
            default:
                $day = "regular";
                break;
        }
        return $this->render($day);

    }

    /**
     * 获取协议地址
     */
    public function actionGetagreement()
    {
        $params = Yii::$app->request->post('type')?Yii::$app->request->post('type'):Yii::$app->request->get('type');
        $type = isset($params['type'])?$params['type']:ApiService::success(202,'参数错误','');

        switch ($type)
        {
            case $type:
                $data=[
                    'url'=>Yii::$app->params['agreement_url'].'share/file?type='.AgreementEnum::getType($type),
                    'title'=>AgreementEnum::getName($type),
                ];
                return ApiService::success(200,'请求成功',$data);
        }

    }
    
	/**
     * 说明
     * @return string
     */
    public function actionExplain(){
        Yii::$app->response->format = Response::FORMAT_HTML;
        $type = Yii::$app->request->post('type')?Yii::$app->request->post('type'):Yii::$app->request->get('type');
        if(!$type){
            return ApiService::error(200,'参数不能为空');
        }
		
		return $this->render('cpic');
    }

    /**
     * 钱富宝新年礼包
     * @return string
     */
    public function actionGifts(){
        Yii::$app->response->format = Response::FORMAT_HTML;
		Yii::$app->response->format = Response::FORMAT_HTML;
		return $this->render('gifts');
    }
	
	 /*新年礼包*/
     public function actionPackage(){
     	Yii::$app->response->format = Response::FORMAT_HTML;
     	return $this->render('package');
     }

	public function actionNational(){
		Yii::$app->response->format = Response::FORMAT_HTML;
		$day=null;
		switch ($_REQUEST['type']) {
			case 90:
				$day=90;
				break;
			case 180:
				$day=180;
				break;
			case 365:
				$day=365;
				break;
			default:
				$day=90;
				# code...
				break;
		}
		return $this->render('nation'.$day);
	}

	/**
	 * 乐享计划下线公告
	 * @return string
	 */
	public function actionOffline(){		
		Yii::$app->response->format = Response::FORMAT_HTML;
		return $this->render('offline');
	}

	/**
	* 大礼疯狂送定投2000惊喜乐翻天
	* @return string
	*/
	public function actionSurprise(){
		Yii::$app->response->format = Response::FORMAT_HTML;
		 return $this->render('surprise');
	}

	/**
	* 38相约女神节
	* @return string
	*/
	public function actionFestival(){
		Yii::$app->response->format = Response::FORMAT_HTML;
		 return $this->render('festival');
	}

}
