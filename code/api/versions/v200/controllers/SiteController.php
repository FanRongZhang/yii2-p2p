<?php

namespace api\versions\v200\controllers;
//use common\service\JsonService;
use api\common\BaseController;
use common\service\UcenterService;
use yii;
use yii\rest\Controller;
//use common\service\ApiService;
use common\service\CommonService;
//use common\enum\AgreementEnum;
use common\service\MemberService;
use api\common\helpers\ReseponseCode as Code;
use common\enum\AgreementEnum;
use yii\helpers\Url;
/**
 * 操作
 * @author jin
 *
 */
class SiteController extends BaseController
{

    public function actionVerifycode()
    {
        $queryParams = $this->getParams();
//        $queryParams=$request->isGet?$request->get():$request->post();

        if ($queryParams['code'] == '' ||$queryParams['mobile'] == '' || $queryParams['type'] == '')
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失！'
            ];
        }


        if (!CommonService::checkVerify($queryParams['code'], $queryParams['mobile'],$queryParams['type']))
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '验证码错误！'
            ];
        }
        else
        {
            if ($queryParams['type'] == 15)
            {
                $member = new MemberService();
                $member_id = $member->findMemberByMobile($queryParams['mobile']);
                $member->isChangeDevice($member_id->id,$queryParams);
            }
        }
        return [
            'code' => Code::HTTP_OK,
            'msg' => '成功',
        ];
    }


    /**
     * 获取验证码
     * 参数：
     * mobile           手机号
     * type             1;设置密码的验证码  2; 设置支付密码的验证    3;申请提现的验证码  4;申请积分的验证码
     *
     * @author jin
     */
    public function actionCode()
    {
        $queryParams =  $this->getParams();

        $mobile = isset($queryParams['mobile']) ? $queryParams['mobile'] : false;
        $type = isset($queryParams['type']) ? $queryParams['type'] : false;
        if (!$type || !$mobile)
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失！'
            ];
        }
        else
        {
            //验证签名START
            $imei   = $queryParams['imei'];
            if($queryParams['checkrandom']%2==0){//MD5（（手机号+随机数）插入 反转 ( IMEI ) 的中间）
                $nImei  = strrev($imei);
                $imei_front = substr($nImei, 0, floor(strlen($nImei)/2));
                $imei_back  = substr($nImei, floor(strlen($nImei)/2));
                $string     = $mobile.$queryParams['checkrandom'];
                $sign       = MD5($imei_front.$string.$imei_back);
                if ($sign != $queryParams['checksign'])
                    return [
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '安全验签不通过！'
                    ];
            }elseif($queryParams['checkrandom']%2==1){//MD5（反转（随机数+（将 手机号 插入 IMEI 的中间）））
                $imei_front = substr($imei, 0, floor(strlen($imei)/2));
                $imei_back  = substr($imei, floor(strlen($imei)/2));
                $sign       = MD5(strrev($queryParams['checkrandom'].$imei_front.$mobile.$imei_back));
                if ($sign != $queryParams['checksign'])
                    return [
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '安全验签不通过！'
                    ];
            }else{
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '安全验签不通过！'
                ];
            }
            //验证前面END

            if (!CommonService::getType($type))
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '短信类型参数有误！'
                ];
            }

            if ($type == CommonService::VERIFY_TYPE_REG)
            {
                $member = new MemberService();
                $userModel = $member->findMemberByMobile($mobile);
                if ($userModel)
                {
                    $user = new UcenterService();
                    $memberModel = $user->getModel();
                    $member_s = $memberModel->find()->where(['=','id',$userModel->id])->one();
                    if ($member_s->status == 2) {
                        return [
                            'code' => Code::COMMON_ERROR_CODE,
                            'msg' => '帐号已在钱富宝存在，请登录进行平移！'
                        ];
                    } else {
                        return [
                            'code' => Code::COMMON_ERROR_CODE,
                            'msg' => '手机号已注册！'
                        ];
                    }
                }
            }
            
            // 找回密码，验证手机号是否存在
            if($type == CommonService::VERIFY_TYPE_FORGET){
                $member = new MemberService();
                $userModel = $member->findMemberByMobile($mobile);
                if(empty($userModel)){
                    return [
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '账号未注册！'
                    ];
                }
            }

            $service = new CommonService();
            $result = $service->sendMobileVerifyCode($mobile, $type);
            if ($result)
            {
                $data = [
                    'session_id'=>session_id()
                ];
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => $data
                ];
            }
            else
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '请求失败！'
                ];
            }
        }
    }

    /**
     * 获取协议地址
     */
    public function actionGetAgreement()
    {
        $params =  Yii::$app->request->get();
        $type = isset($params['type'])?$params['type']:['code' => Code::COMMON_ERROR_CODE, 'msg' => '参数缺失！'];
        switch ($type)
        {
            case 1:
                $data=[
                    'url'=>Url::to(['/agreement', 'id'=>1], true),
                    'title'=>AgreementEnum::getName($type),
                ];
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => $data
                ];
                break;
            case 2:
                $data=[
                    'url'=>Url::to(['/agreement', 'id'=>2], true),
                    'title'=>AgreementEnum::getName($type),
                ];
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => $data
                ];
                break;
            case 3:
                $data=[
                    'url'=>Url::to(['/agreement', 'id'=>3], true),
                    'title'=>AgreementEnum::getName($type),
                ];
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => $data
                ];
                break;
            case 4:
                $data=[
                    'url'=>Url::to(['/agreement', 'id'=>4], true),
                    'title'=>AgreementEnum::getName($type),
                ];
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => $data
                ];
                break;
        }

    }




}
