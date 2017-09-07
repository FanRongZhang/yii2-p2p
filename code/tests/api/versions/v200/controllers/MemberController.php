<?php
namespace api\versions\v200\controllers;

use api\common\BaseController;
use common\models\UMember;
use common\service\MemberInfoService;
use common\service\MemberService;
use common\service\UcenterService;
use common\service\VouchersService;
use common\service\LoginRecordService;
use common\service\BankService;
use api\common\helpers\ReseponseCode as Code;
use common\service\ImeiService;
use common\service\IdCardValidateService;
use yii\web\UploadedFile;
use common\service\CommonService;
use common\service\MemberMoneyService;
use common\models\QfbMoneyLog;
use common\models\QfbLiveLog;
use common\models\QfbOrder;
use common\models\QfbProfitSettings;
use common\service\ProductService;
use common\models\QfbProduct;
use common\service\ApiService;
use yii\base\Object;
use common\models\QfbMember;
use common\models\QfbMemberMoney;
use yii\helpers\Url;
/**
* 
*/
class MemberController extends BaseController
{
    /**
     * 32零钱代金券积分查询v200
     * @author jin
     */
    public function actionMoney()
    {
        $member_id = $this->member_id;
        
        $member_money = MemberMoneyService::getByMemberMoney($member_id);
        $result['money'] = $member_money['money'];
        $result['coupon'] = VouchersService::getVouchersMoneysByMemberId($member_id);
        $result['score'] = '0';
         
        return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK],$result);
    }

    /**
     * 上传头像
     * 参数：
     * file           上传图片
     *
     * @author jin
     *
     */
    public function actionAvatar()
    {
        $uid = $this->member_id;

        $params = $this->getParams();
        $channelId = isset( $params['channelId'] ) ? $params['channelId'] : null;            //android传1，ios传2
        $channelName = 'unidentified_';
        if ($channelId == 1){
            $channelName = 'android_';
        }elseif ($channelId == 2){
            $channelName = 'ios_';
        }

        $file = UploadedFile::getInstanceByName('avatar');

        if ($file->extension!='jpg' && $file->extension!='png'){
            return ApiService::error(Code::COMMON_ERROR_CODE,'上传的头像图片格式不对');
        }

        if ($file->size > 512*1024){
            return ApiService::error(Code::COMMON_ERROR_CODE,'上传的头像图片不能大于512K');
        }elseif($file->size <= 0){
            return ApiService::error(Code::COMMON_ERROR_CODE,'上传的头像图片不能为空');
        }

        if ($file) {
            $dir = './../../storage/uploads/avatar/';
            $img_dir = 'avatar/';
            if (!is_dir($dir)){
                mkdir($dir,0777,true);
            }

            $fileName = $channelName.$uid.'_userimg'.rand(1000,9999);
            $result = $file->saveAs($dir . $fileName .time(). '.' . $file->extension);
            if ($result){

                $domine = \Yii::$app->fileStorage->baseUrl;
                $img['avatar'] = $img_dir . $fileName .time(). '.' . $file->extension;
                $data['url'] = $domine .'/'. $img_dir . $fileName .time(). '.' . $file->extension;

                $memberInfo = new MemberInfoService();
                if ($memberInfo->saveAvatar($uid,$img['avatar'])){
                    return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK],$data);
                }else{
                    return ApiService::error(Code::COMMON_ERROR_CODE,'头像上传成功，但存储url失败',$data);
                }

            }else{
                return ApiService::error(Code::COMMON_ERROR_CODE,'头像上传失败');
            }
        }
    }

    /**
     * 4实名认证
     *
     * @author jin
     */
    public function actionAuthname()
    {
        $member_id = $this->member_id;
        $params = $this->getParams();
        $realname = isset( $params['realname'] ) ? $params['realname'] : null;            //真实姓名
        $idcard = isset( $params['idcard'] ) ? $params['idcard'] : null;                // 用户身份证号码
        $imei = isset( $params['imei'] ) ? $params['imei'] : null;                    //设备唯一标示；（用于建立设备黑名单，若一个设备2次认证失败则进入黑名单，需要联系客服解锁）
        $auth_key = isset( $params['auth_key'] ) ? $params['auth_key'] : null;            //验签
        $auth_random = isset( $params['auth_random'] ) ? $params['auth_random'] : null;      //随机数

        if($realname == null || $idcard == null|| $imei == null|| $auth_key == null|| $auth_random == null)
        {
            return ApiService::error(Code::COMMON_ERROR_CODE,'参数错误');
        }
    
        //把身份证最后一个字符改成大写
        $idcard = strtoupper($idcard);
    
        //验签
        $imei1 = substr($imei, 0, floor(strlen($imei)/2) );
        $imei2 = substr($imei, floor(strlen($imei)/2), strlen($imei));
    
        $type = $auth_random % 3;
        $key = '';
        $des3_key = \Yii::$app->params['encrypt_key'];
        if ($type==0){
            $key = md5(($imei1 . $auth_random . $imei2) . ($auth_random<<($auth_random % 4)) . $des3_key .$idcard);
        }elseif ($type==1){
            $key = md5(($auth_random>>($auth_random % 5)) . $idcard . (strrev($imei . $des3_key)) . $auth_random );
        }elseif ($type==2){
            $key = md5( (strrev( substr($des3_key, 0, floor(strlen($des3_key)/2) ) .
                 substr($auth_random, 0, floor(strlen($auth_random)/2) ) . substr($imei, 0, floor(strlen($imei)/2) ) )) .
                 $idcard . (strrev($auth_random) >> ($auth_random % 6)) );
        }
    
        if ($auth_key == $key){
            //查询此身份证是否已经被别人认证了
            $idcards = MemberInfoService::getByIdcard($member_id,$idcard);
            
            if (empty($idcards)){
                $imei_count = ImeiService::getImeiCount($imei);
                if ($imei_count >= 2){
                    return ApiService::error(Code::COMMON_ERROR_CODE,'该设备已达到最大认证失败次数，请联系客服400-607-1818解锁','');
                }
                $member_count = ImeiService::getMemberCount($member_id);
                if ($member_count >= 3){
                    return ApiService::error(Code::COMMON_ERROR_CODE,'该用户已达到最大认证失败次数，请联系客服400-607-1818解锁','');
                }
    
                //$idCardValidate = new IdCardValidateService();
                //$result = $idCardValidate->doValidate($realname,$idcard);
                
                $result['code'] = 0;
                if (!isset($params['test'])){
                    $result['code'] = 1;
                }
                
                //实名认证
                if (intval( $result['code'] ) == 1){
                    $memberInfoParams['QfbMemberInfo']=array(
                        'card_no' => $idcard,
                        'realname' => $realname,
                        'is_verify' => 1,
                    );
                    //对应用户保存认证通过的身份证
                    $saveMemberInfo = MemberInfoService::saveMemberInfo($member_id,$memberInfoParams);
                    if ( $saveMemberInfo ){
                        BankService::updateByMemberId($member_id);
                        return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK]);
                    }else {
                        return ApiService::error(Code::COMMON_ERROR_CODE,'保存用户身份证信息失败');
                    }
                }elseif(intval( $result['code'] ) == 2){
                    //认证失败，入设备黑名单数据表
                    $result = ImeiService::setImei($member_id,strval($imei));
                    if ($result){
                        return ApiService::error(Code::COMMON_ERROR_CODE,'姓名和身份证号码不一致',$result);
                    }else {
                        return ApiService::error(Code::COMMON_ERROR_CODE,'入设备黑名单数据表失败',$result);
                    }
                }elseif(intval( $result['code'] ) == 3){
                    //认证失败，入设备黑名单数据表
                    $result = ImeiService::setImei($member_id,strval($imei));
                    if ($result){
                        return ApiService::error(Code::COMMON_ERROR_CODE,'无此身份证号，请到户籍所在地进行核实',$result);
                    }else {
                        return ApiService::error(Code::COMMON_ERROR_CODE,'入设备黑名单数据表失败',$result);
                    }
                }else{
                    return ApiService::error(Code::COMMON_ERROR_CODE,'认证失败'.$result['code']);
                }
            }else{
                return ApiService::error(Code::COMMON_ERROR_CODE,'此身份证已被实名认证');
            }
        }
        else
        {
            return ApiService::error(Code::COMMON_ERROR_CODE,'验签失败');
        }
    }
    
    
    /**
     * 26我的代金券列表v200
     * int type;  //type=0 表示可用，type=1 表示查看历史( 表示已使用 表示已过期)
     * int limit;//一页限制个数，默认10
     * int page;//页数，默认为1
     * @author jin
     */
	public function actionCoupon()
	{
	    $member_id = $this->member_id;
	    $params = $this->getParams();
	    $type = isset($params['type']) ? $params['type'] : null;
	    $limit = isset($params['limit']) ? $params['limit'] : 10;
	    $page = isset($params['page']) ? $params['page'] : 1;
	    
	    if ( !is_numeric($type) || !is_numeric($limit) || !is_numeric($page) || !in_array($type, [0,1]) )
	    {
	        return ApiService::error(Code::COMMON_ERROR_CODE,'参数错误');
	    }
	    
	    $vouchers = VouchersService::getVouchersByTypeAndMemberId($member_id,$type,$limit,$page);
	    
	    $result = array();
	    foreach ($vouchers as $val)
	    {
	        $result[] = 
	        [
	        	'status' => intval($val['status']),
	        	'money' => intval($val['money']),
	        	'invalid_time' => date('Y-m-d',$val['invalid_time']),
	        	'remark' => $val['remark'],
	        	'limit' => '投资额度达到'.$val['use_money'].'元及以上可用',
	        ];
	    }
	    if (empty($result)){
	        $data['total'] = 0;
	    }else{
	        $data['total'] = intval(VouchersService::getVouchersMoneys($member_id,$type));
	    }
	    $data['list'] = $result;
	    return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK],$data);
	}
	
	/**
	 * 28我的代金券详情v200
	 * int id;//代金券id
	 * @author jin
	 */
	public function actionCouponDetail()
	{
	    $member_id = $this->member_id;
	    $params = $this->getParams();
	    $vochers_id = isset($params['id']) ? $params['id'] : null;
	     
	    if ( !is_numeric($vochers_id) )
	    {
	        return ApiService::error(Code::COMMON_ERROR_CODE,'参数错误');
	    }
	     
	    $data = VouchersService::getVouchersByVouchersIdAndMemberId($member_id, $vochers_id);
	    
	    $result[] =
	        [
	            'receive_time' => date('Y-m-d',$data['receive_time']),
	            'invalid_time' => date('Y-m-d',$data['invalid_time']),
	            'remark' => $data['remark'],
	            'status' => intval($data['status']),
	            'limit' => '投资额度达到'.$data['vouchers']['use_money'].'元以上可用',
	            'use_time' => $data['use_time']==0? "" : date('Y-m-d',$data['use_time']),
	            'use_product_name' => $data['use_product_name'],
	        ];
	     
	    return ApiService::success(Code::HTTP_OK,Code::$statusTexts[Code::HTTP_OK],$result);
	}

    /**
     * 用户修改昵称
     * @return mixed
     */
    public function actionUpdateNick()
    {
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
        $member_id = $this->member_id;
        $memberInfo = new MemberInfoService();
        $result = $memberInfo->updateNickName($member_id,$params['nickname']);
        if ($result)
        {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '修改成功！',
            ];
        }
        else
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '修改失败！'
            ];
        }
    }

    /**
     * 用户修改密码
     * @return array
     */
    public function actionUpdatePwd()
    {
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
        // 1 修改登录密码 2 修改支付密码
        if ($params['type'] == 1)
        {
            $member_id = $this->member_id;
            if (!CommonService::checkVerify($params['code'], $params['mobile'],CommonService::VERIFY_TYPE_PWD))
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
        elseif($params['type'] == 2)
        {
            $member_id = $this->member_id;
            if (!CommonService::checkVerify($params['code'], $params['mobile'],CommonService::VERIFY_TYPE_PAYPWD))
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '验证码错误！'
                ];
            }
            $member = new MemberService();
            $result = $member->updateZfPassword($member_id,$params);
            if ($result)
            {
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '修改成功！',
                ];
            }
            else
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '修改失败！'
                ];
            }
        }
    }

    /**
     * 修改推荐人关系
     * @return array
     */
    public function actionUpdateReferrer()
    {
        $params = $this->getParams();
        if (!$params['r_mobile'])
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失！'
            ];
        }
        $memberService = new MemberService();
        $member_id = $this->member_id;
        $result = $memberService->updateRefer($member_id,$params);
        if ($result['code'] == 200)
        {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '修改成功！',
            ];
        }
        else
        {
            return [
                'code' => Code::HTTP_OK,
                'msg' => $result['message'],
            ];
        }
    }

    /**
     * 判断用户手机号码是否存在
     */
    public function actionIsPhone()
    {
        $params = $this->getParams();
        if (!$params['mobile'])
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失！'
            ];
        }
        $memberService = new MemberService();
        $result = $memberService->findMemberByMobile($params);
        if ($result)
        {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '存在',
            ];
        }
        else
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '手机号码不存在！'
            ];
        }
    }



    /**
     * 53活期理财详情
     * @return array
     * @author wsf
     */
    public function actionProfit() {
        //昨天、今天凌晨的时间戳
        $yes_start_time = strtotime(date('Y-m-d',strtotime('-1 day')));
        $to_start_time = strtotime(date('Y-m-d'));
        
        $params = $this->getParams();
        $product_id = $params['product_id']; 
        //判断是否登录，登录就获取用户id
        if (!empty($params['access_token'])) {
            $member = QfbMember::find()->where(['=','access_token',$params['access_token']])->one();
            $uid = $member->id;
        } else {
            $uid = '';
        }    
        $data = array();
        //投资金额
        $all_price = 0;
        $money = QfbMemberMoney::find()->select(['live_money','pre_live_money'])->where(['=','member_id',$uid])->one();
        if ($money) {
            $all_price = $money->live_money + $money->pre_live_money;
        }
        $data['wealth_money'] = (string) $all_price;
        //万份收益
        $ten_thousand = QfbProduct::find()->select('year_rate')->where(['id'=>$product_id,'is_hidden'=>0])->one();
        $rate_money = 100 * $ten_thousand->year_rate / 365;
        $p= stripos($rate_money , '.');
        $data['ten_thousand_profit'] = (string) substr($rate_money ,0 , $p+5);

        //查询累积收益
        $all_profit = 0;
        $profit = QfbMoneyLog::find()->select('money')->where(['member_id'=>$uid,'type'=>1,'money_type'=>2,'action'=>8])->all();
        if ($profit) {
            foreach ($profit as $pro) {
                $all_profit += $pro->money;
            }
        }
        $data['all_profit'] = (string) $all_profit;
        //昨日收益
        $yes_profit = QfbMoneyLog::find()->select('money')->limit(1)
                    ->where(['member_id'=>$uid,'type'=>1,'money_type'=>2,'action'=>8])
                    ->andWhere(['between','create_time',$yes_start_time,$to_start_time])
                    ->one();
        $data['yesterday_profit'] = !empty($yes_profit->money) ? (string) $yes_profit->money : "0";
        //查询年化收益率七条记录
        $seven = QfbLiveLog::find()->select(['year_rate','create_time'])->limit(7)->orderBy('create_time desc')->all();
        foreach ($seven as $k => $sev) {
            $data['list'][$k]['operant_time'] = (string) $sev->create_time;
            $data['list'][$k]['yield_rate'] = (string) $sev->year_rate;
        }
        //查询协议
        $service = new ProductService();
        $model = $service->getAgreement($product_id);
        if (!empty($model[0]['product_agreement'])) {
            foreach ($model[0]['product_agreement'] as $k => $mod) {
                $data['url_data'][$k]['title'] = (string) $mod['title'];
                $data['url_data'][$k]['content_url'] = (string) Url::to(['/agreement', 'id'=>$mod['id']], true);
                $data['url_data'][$k]['pic_url'] = (string) (\Yii::$app->fileStorage->baseUrl.'/'.$mod['pic_url']);  
            }

        } else {
            $data['url_data'] = [];
        }
        return [
                    'code' => Code::HTTP_OK,
                    'msg' => Code::$statusTexts[Code::HTTP_OK],
                    'data' => $data
               ];
    }

    /**
     * 用户设置支付密码
     * @return array
     */
    public function actionSetZfpwd()
    {
        $params = $this->getParams();
        $member_id = $this->member_id;
        if (!$params['zf_pwd'])
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失！'
            ];
        }
        $memberService = new MemberService();
        $result = $memberService->setZfPwd($member_id,$params);
        if ($result)
        {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '设置成功！',
            ];
        }
        else
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '设置失败！'
            ];
        }
    }

    /*
     * 验证密码是否正确
     * */
    public function actionVerifyPwd(){
        $params = $this->getParams();
        if(!$params['pwd'] || !$params['type']){
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失！'
            ];
        }

        $member_id  = $this->member_id;
        if($params['type']==2){//2表示验证支付密码
            $result = MemberService::checkZfPwd($member_id,$params['pwd']);
            $msg    = $result ? '支付密码校验正确' : '支付密码校验失败';
            $code   = $result ? Code::HTTP_OK : Code::COMMON_ERROR_CODE;
        }elseif($params['type']==1){//1表示验证登录密码
            $result = UMember::find()->where(['id'=>$member_id,'password'=>$params['pwd']])->one();
            $msg    = $result ? '登录密码校验正确' : '登录密码校验失败';
            $code   = $result ? Code::HTTP_OK : Code::COMMON_ERROR_CODE;
        }else{
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数类型错误！'
            ];
        }

        return [
            'code' => $code,
            'msg' => $msg
        ];
    }

    /**
     * 用户激活中视用户
     * @return array
     */
    public function actionActive(){
        $member_id = $this->member_id;
        if (!$member_id) {
            return [
                'code' => Code::NOT_LOGIN,
                'msg' => '用户未登录!'
            ];
        }
        $status = 1;
        $service = new UcenterService();
        $status = $service->updateMemberActive($member_id,$status);
        if ($status) {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '激活成功！',
            ];
        } else {
            return [
                'code' => Code::HTTP_OK,
                'msg' => '激活失败！',
            ];
        }
    }

    /**
     * @查询是否实名绑卡
     * @return array
     */
    public function actionCheck(){
        $member_id = $this->member_id;
        $member = new MemberInfoService();
        $memberInfo = $member->getMemberInfo($member_id);
        if ($memberInfo) {
            if ($memberInfo->is_verify == 1) {
                $data = [
                    'realname' => $memberInfo->realname,
                    'idcard' => $memberInfo->card_no,
                    'is_verify' => true,
                ];
            } else {
                $data = [
                    'realname' => '',
                    'idcard' => '',
                    'is_verify' => false,
                ];
            }
            return [ 'code' => Code::HTTP_OK, 'msg' => '成功', 'data' => $data ];
        } else {
            return [ 'code' => Code::HTTP_OK, 'msg' => '未查到该用户信息'];
        }
    }
}