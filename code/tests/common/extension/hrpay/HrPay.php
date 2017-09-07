<?php
namespace common\extension\hrpay;

use common\extension\hrpay\lib\Array2XML;
use common\extension\hrpay\lib\Curl;
use common\service\CommonService;
use common\service\LogService as Log;
use yii\base\Component;
use api\common\helpers\ReseponseCode as Code;

/**
 * 华融支付组件
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class HrPay extends Component
{
    /**
     * 商户号
     * @var String
     */
    public $mercNo;

    /**
     * md5验签key
     * @var String
     */
    public $md5Key;

    /**
     * 短息验证码获取url
     */
    public $smsUrl;

    /**
     * 支付url
     */
    public $payUrl;

    /**
     * 绑卡（鉴权）url
     */
    public $authUrl;

    /**
     * 商户类别
     * 0:一般商户;1:平台商户
     * @var Integer
     */
    public $merNoType = 1;

    /**
     * 商户名
     * @var String
     */
    public $merchantName = '中视钱包';

    /**
     * 异步通知地址
     * @var String
     */
    public $notifyUrl;

    /**
     * 获取验证码
     * @param String $phoneNumber 手机号
     * @param String $orderNumber 订单号
     * @param Number $transamt 订单金额
     * @param String $productName 商品名称
     * @param String $code 接口名
     * @param String $cardNo 银行卡号
     * @param String $cardName 付款人姓名
     * @param String $cardID 付款人身份证号
     * 若需要发华融下发短信，值为 SendSMS
     * 若不需要下发短信，值为 SendCode
     * @return Boolean true发送成功，false发送失败
     */
    public function getVerifyCode(
        $phoneNumber,
        $orderNumber,
        $transamt,
        $productName,
        $cardNo,
        $cardName,
        $cardID,
        $code = 'SendSMS'
    ) {
        $preSign = $this->mercNo . '&' . $phoneNumber
        . '&' . $orderNumber . '&' . $transamt
        . '&' . $this->md5Key;
        $sign = strtoupper(md5($preSign));
        $data = [
            'Code' => $code,
            'mercNo' => $this->mercNo,
            'merchantName' => $this->merchantName,
            'merOrderName' => $productName,
            'merNoType'    => 0,
            'merOrderId'   => $orderNumber,
            'signType'     => 'MD5',
            'signInfo'     => $sign,
            'merOrderAmt'  => $transamt,
            'cardType'     => 0,
            'cardNo'       => $cardNo,//银行卡号
            'cardName'     => $cardName, //付款人姓名
            'cardID'       => $cardID, //付款人身份证号
            'mobile'       => $phoneNumber
        ];
        $res = Curl::curlPost($this->smsUrl, $data);
        $arr = (array) json_decode($res);

        if (isset($arr['Errorcode']) && $arr['Errorcode'] == '0000') {
            //由于SendCode模式华融不会发验证码，只会返回验证码
            //所以要自己的短信平台发送----暂时不支持自己下发短信
           /* if ($code == 'SendCode') {
                //接受短信验证码，自己下发给用户
                $verifyCode = $arr['Verifycode'];
                //调试模式下直接输出验证码
                if ($this->debug) {
                    echo $verifyCode;
                } else {
                    //发送短信给用户
                    $content = "你的账户正在使用华融快捷支付，即将扣款"
                        . $transamt . "元,支付验证码" . $verifyCode;
                    $res = CommonService::pubSendNewSms($phoneNumber, $content);
                    return $res;
                }
            } else {
                return true;
            }*/
            return true;
        } else {
            //失败时记录日志
            Log::log('hr_sms_error.log', $res);
            return false;
        }
    }

    /**
     * 快捷支付
     * @param  String  $orderNumber 订单号
     * @param  Number  $verifyCode  验证码
     * @param  Decimal  $transamt   订单金额
     * @param  String  $productName 产品名
     * @param  String  $orderTime 订单时间，格式为YmdHis
     * @param  String  $cardNo     卡号
     * @param  String  $cardName 持卡人姓名
     * @param  String  $cardId     持卡人身份证号
     * @param  String  $mobile     预留手机号
     * @param  Integer $cardType    卡类型,0为借记卡，1为借贷卡
     * @param  string  $code        接口代码
     * @param  string  $signType    签名类型
     * @param  string  $cvn2        信用卡验证代码(三位如：048)
     * @param  string  $validityM   信用卡有效期月(两位如：01)
     * @param  string  $validityY   信用卡有效期年(四位如：2020)
     * @return Array 支付结果
     */
    public function pay(
        $orderNumber,
        $verifyCode,
        $transamt,
        $productName,
        $orderTime,
        $cardNo,
        $cardName,
        $cardId,
        $mobile,
        $cardType = 0,
        $cvn2 = '',
        $validityM = '',
        $validityY = '',
        $code = 'MerQuickPay',
        $signType = 'MD5'
    ) {
        $preSign = $cardNo . '&' . $cardId . '&'
        . $mobile . '&' . $this->md5Key;
        $sign = strtoupper(md5($preSign));
        $data = [
            'code' => $code,
            'mercNo' => $this->mercNo,
            'merNoType' => $this->merNoType,
            'merOrderId' => $orderNumber,
            'signType' => $signType,
            'signInfo' => $sign,
            'Verifycode' => $verifyCode,
            'merOrderAmt' => $transamt,
            'merOrderName' => $productName,
            'notifyUrl' => $this->notifyUrl,
            'orderTime' => $orderTime,
            'cardNo' => $cardNo,
            'cardType' => $cardType,
            'cardName' => $cardName,
            'cardID' => $cardId,
            'mobile' => $mobile,
        ];
        //信用卡加上有效期，校验码参数
        if ($cardType == 1) {
            $vdata = compact('cvn2', 'validityM', 'validityY');
            $data = array_merge($data, $vdata);
        }
        $res = Curl::curlPost($this->payUrl, $data);
        $arr = (array) json_decode($res);
        if (isset($arr['ordsts']) && $arr['ordsts'] == '1') {
            return ['code' => Code::HTTP_OK, 'msg' => $arr['message']
                , 'data' => $arr];
        } else {
            //失败时记录日志
            Log::log('hr_pay_error.log', $res);
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $arr['message']
                , 'data' => $arr];
        }
    }

    /**
     * 绑卡
     * @param  String  $orderNumber 订单号
     * @param  String  $mobile     预留手机号
     * @param  String  $bankName    银行名称
     * @param  String  $cardNo     卡号
     * @param  String  $cardName 持卡人姓名
     * @param  String  $idNo     证件号码
     * @param  String  $idType  证件类型
     * @param  String  $cartType 卡类型（00：对私；01对公）
     * @param  String  $signType 验签方式（此处固定为M代表md5)
     * @return Array 绑卡结果
     */
    public function auth(
        $orderNumber,
        $mobile,
        $bankName,
        $cardNo,
        $cardName,
        $idNo,
        $idType = '00',
        $cartType = "00",
        $signType = 'M'
    ) {
        $preSign = $this->mercNo . '&' . $orderNumber . '&' . $this->md5Key;
        $sign = strtoupper(md5($preSign));
        $data = [
            "Mer_No" => $this->mercNo,
            "signType" => $signType,
            "prdordno" => $orderNumber,
            "Mobile" => $mobile,
            "BankName" => $bankName,
            "AccountNo" => $cardNo,
            "AccountName" => $cardName,
            "AccountType" => $cartType,
            "IdType" => $idType,
            "IdNo" => $idNo,
            "inMsg" => $sign,
        ];
        $res = Curl::curlPost($this->authUrl, $data);
        $arr = (array) json_decode($res);
        if (isset($arr['RSPCOD']) && $arr['RSPCOD'] == '00000') {
            return ['code' => Code::HTTP_OK, 'msg' => $arr['RSPMSG']
                , 'data' => $arr];
        } else {
            //失败时记录日志
            Log::log('hr_auth_error.log', $res);
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $arr['RSPMSG']
                , 'data' => $arr];
        }
    }

    /**
     * 准备异步回调数据
     * @param String $data 处理前数据
     * @return String [xml格式] 处理后数据
     */
    public function getNotifyData($data)
    {
        Array2XML::init($version = '1.0', $encoding = 'UTF-8');
        $xml = Array2XML::createXML('root', $data);
        return $xml->saveXML();
    }
}
