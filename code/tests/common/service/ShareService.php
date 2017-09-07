<?php
namespace common\service;
use common\models\MemberInfo;
use common\models\DwalletActivityLog;
use common\models\Member;
use common\models\DwalletFlow;
use common\enum\WalletFlowEnum;
use common\models\DwalletMember;
use common\models\MemberMoney;
use api\components\Flow;
use yii\base\Exception;
use yii;

/**
 * 分享注册送流量业务逻辑
 * @author xiaoma <xiaomalover@gmail.com>
 *
 */
class ShareService extends BaseService
{

	/**
     * @param Int $member_id 用户id
	 * 获取用户的基本信息
     */
    public static function getUserInfo($member_id)
    {
    	$nickname = $mobile = "";
    	yii::$app->set('db',yii::$app->components['dmall']);
    	$mi = MemberInfo::find()->where(['member_id' => $member_id])->one();
    	if($mi){
    		$nickname = $mi->realname ? $mi->realname : $mi->nickname;
    	}
		$member = Member::findOne($member_id);
		if($member){
			if(!$nickname){
				$nickname = $member->mobile;
			}
			$mobile = $member->mobile;
		}
		yii::$app->set('db',yii::$app->components['dwallet']);
    	return ['nickname'=>$nickname, 'mobile'=>$mobile];
    }

    /**
     * 获取用户获赠流量信息
     * @param Int $member_id 用户id
     */
    public static function getflowInfo($member_id)
    {
    	$fl = DwalletFlow::find()->where(['member_id'=>$member_id])->orderBy("id desc")->one();
        $flow = $fl ? $fl->flow : 0;
        return $flow;
    }

    /**
     * 获取最新送流量记录
     * @param Int $limit 条数
     */
    public static function getFlowList($limit)
    {
    	$flowList = DwalletFlow::find()->where(['status' => WalletFlowEnum::STATUS_SUCCESS])->orderBy('complete_time desc')->limit($limit)->all();
    	return $flowList;
    }
    
    /**
     * 获取最新财富赠送记录
     * @param Int $limit 条数
     */
    public static function getCflist($limit)
    {
    	$cfList = DwalletActivityLog::find()->where(['type' => 1, 'money_type' => 1 ,'from' => 19])->orderBy("create_time desc")->limit($limit)->all();
    	return $cfList;
    }

    /**
     * @param String $mobile 注册人手机号
     * @param String $r_mobile 分享人（推荐人）手机号
     * @param String $password  密码
     * 注册大明和钱富宝用户
     */
    public static function register($mobile, $r_mobile, $password)
    {

    	//已注册钱富宝用户
        $dwm = self::isRegInQfb($mobile);
        if($dwm){
            return ['code'=>500,'msg'=>'用户已注册'];
        }

        $dmm = self::isRegInDm($mobile);
        //未在大明注册用户,则注册用户
    	if(!$dmm){
            $res = self::regDmUser($mobile,$r_mobile,$password);
            if($res['code'] != 200){
                //return ['code'=>500,'msg'=>'注册大明商城用户时失败'];
                return $res;
            }else{
                $dmm = $res['data'];
            }
    	}

        $tran = Yii::$app->db->beginTransaction();

        try {
        	//在钱富宝注册用户
    		$dwm = new DwalletMember();
    		$dwm->mobile = $mobile;
    		$dwm->username = $mobile;
    		$dwm->member_id = $dmm->id;
    		if($dwm->save()){
    			//创建送流量订单
    			$fm = new DwalletFlow();
    			$fm->mobile = $mobile;
    			$fm->member_id = $dwm->member_id;
    			$fm->create_time = time();
    			$fm->complete_time = time();
                //判断客户手机类型已获取相应的流量赠送值（因为各运营商送的流量不一样）
                $mt =  Flow::getPhoneDetail($mobile);
                $fm->belongto = isset($mt['belongto']) ? $mt['belongto'] : "未知";
                if(isset($mt['mobile_type'])){
                    $fm->mobile_type = $mt['mobile_type'];
                    if($mt['mobile_type'] == WalletFlowEnum::MOBILE_TYPE_MOBILE){ //移动
                        $fm->flow = 30;
                    }else if($mt['mobile_type'] == WalletFlowEnum::MOBILE_TYPE_UNICOM){ //联通
                        $fm->flow = 20;
                    }else if($mt['mobile_type'] == WalletFlowEnum::MOBILE_TYPE_TElECOM){ //电信
                        $fm->flow = 10;
                    }
                }
    			if($fm->save()){
    				//调用送流量接口
    				$res = Flow::chargeFlow($mobile,$fm->id);
                    $obj = json_decode($res);
                    if(isset($obj->code) && $obj->code == "000"){
                        $fm->sn = $obj->data->orderNO;
                        $fm->save();
                    }
                    $tran->commit();
                    return ['code'=>200,'msg'=>'成功','data'=>['member_id'=>$dwm->member_id]];
    			}else{
    				throw new Exception('flow');
    			}
    		}else{
    			throw new Exception('qfbmember');
    		}
        }catch(Exception $e){
            $tran->rollback();
            switch($e->getMessage()){
                case 'qfbmember':
                    return ['code'=>500,'msg'=>'保存钱富宝用户出错'];
                    break;
                case 'flow':
                    return ['code'=>500,'msg'=>'保存流量充值记录信息失败'];
                    break;
                case 'charge':
                    return ['code'=>500,'msg'=>'接口充值流量失败'];
                    break;
                default:
                    return ['code'=>500, 'msg'=>$e->getMessage()];
                    break;
            }
        }
    }
    
    
    /**
     * @param String $mobile 注册人手机号
     * @param String $r_mobile 分享人（推荐人）手机号
     * @param String $password  密码
     * 注册大明和钱富宝用户
     */
    public static function cfRegister($mobile, $r_mobile, $password)
    {
    
    	//已注册钱富宝用户
    	$dwm = self::isRegInQfb($mobile);
    	if($dwm){
    		return ['code'=>500,'msg'=>'用户已注册'];
    	}
    
    	$dmm = self::isRegInDm($mobile);
    	//未在大明注册用户,则注册用户
    	if(!$dmm){
    		$res = self::regDmUser($mobile,$r_mobile,$password);
    		if($res['code'] != 200){
    			//return ['code'=>500,'msg'=>'注册大明商城用户时失败'];
    			return $res;
    		}else{
    			$dmm = $res['data'];
    		}
    	}

    	//在钱富宝注册用户
    	$dwm = new DwalletMember();
    	$dwm->mobile = $mobile;
    	$dwm->username = $mobile;
    	$dwm->member_id = $dmm->id;
    	if($dwm->save()){
    		return ['code'=>200,'msg'=>'成功','data'=>['member_id'=>$dwm->member_id]];
    	}else{
    		return ['code'=>500,'msg'=>'保存钱富宝用户出错'];
    	}
    }
    

    /**
     *注册商城用户
     * @param String $moblie 手机号
     * @param String $r_mobile 推荐人手机号
     * @param String $password 密码
     */
    public static function regDmUser($mobile,$r_mobile,$password)
    {
        $model = new Member();
        $model->setScenario('member');

        $infoModel = new MemberInfo();
        $moneyModel = new MemberMoney();

        $tran = Yii::$app->db->beginTransaction();
        try {
                $model->password = $password;
                $model->mobile = $mobile;
                $model->r_mobile = $r_mobile;
                //判断来源
                $referer = Yii::$app->request->headers['Referer'];
                if(stristr($referer,'cf-register')){ //财富计划
                }else if(stristr($referer,'register')){ //流量
                }else{ //官网
                    $model->source = 2;
                }
                if(!$model->save())
                    throw new Exception('member');
                $infoModel->member_id=$model->id;
                if(!$infoModel->save())
                    throw new Exception('info');
                $moneyModel->member_id=$model->id;
                if(!$moneyModel->save())
                    throw new Exception('money');
                $tran->commit();
                return ['code'=>200,'msg'=>'注册商城用户成功','data'=>$model];

        } catch (Exception $e) {
            $tran->rollback();
            switch($e->getMessage()){
                case 'member':
                    return ['code'=>500,'msg'=>'保存商城用户失败'];
                    break;
                case 'info':
                    return ['code'=>500,'msg'=>'保存商城用户信息失败'];
                    break;
                case 'money':
                    return ['code'=>500,'msg'=>'保存用户账户信息失败'];
                    break;
                default:
                    return ['code'=>500, 'msg'=>$e->getMessage()];
                    break;
            }
        }
    }

    /**
     * 查看用户有没有在钱富宝注册
     * 未注册返回false，注册返回用户记录
     * @param String $mobile 手机号
     */
    public static function isRegInQfb($mobile)
    {
    	$member = DwalletMember::find()->where(['mobile' => $mobile])->one();
    	return $member;
    }

    /**
     * 查看用户是否在大明商城注册
     */
    public static function isRegInDm($mobile)
    {
    	yii::$app->set('db',yii::$app->components['dmall']);
    	$member = Member::find()->where(['mobile' => $mobile])->one();
    	return $member;
    }


    /**
     * 处理流量充值回调
     */
    public static function ensureFlow($params)
    {
        if(isset($params['extNo'])){
            $fl = DwalletFlow::findOne($params['extNo']);
            if($fl){
                if(isset($params['status']) && $params['status'] == "成功"){
                    $fl->status = WalletFlowEnum::STATUS_SUCCESS;
                    $fl->complete_time = time();
                    $fl->save();
                }else{
                    $fl->status = WalletFlowEnum::STATUS_FAIL;
                    $fl->complete_time = time();
                    $fl->save();
                }
            }
            echo json_encode(['code'=>200,'msg'=>'回调逻辑处理完成']);
        }else{
            echo json_encode(['code'=>500,'msg'=>'extNo不能为空']);
        }
    }
}

?>