<?php
/**
 * 证联支付类
 * @author xiaomalover <xiaomalover@gmail.com>
 */
namespace common\extension\zlpay;

use League\Flysystem\Exception;
use yii\base\Component;
use common\extension\zlpay\lib\encryptSign;

/**
 * Class ZlPay
 * @package common\extension\zlpay
 */
class ZlPay extends Component
{
    public  $errors = [];
    /**
     * 充值
     */
    const TRADE_TYPE_RECHARGE = '01';

    /**
     * 绑卡
     */
    const TRADE_TYPE_BIND = '02';

    /**
     * 公钥
     */
    public $publicKey;

    /**
     * 私钥
     */
    public $privateKey;

    /**
     * 机构代码，由证联分配的9位定长的唯一代号
     */
    public $instuId;
    public $deviceimei;
    public $pip;
    public $pport;
    public $url;
    public $merid;
    //银行卡类型 01 储蓄卡
    public $cardType;
    //证件类型 1 身份证
    public $certifTp;
    //验证码类型 02实名认证 03签约支付
    public $smsType;
    //回调地址
    public $backUrl;

    public function __construct(){
        $this->certifTp = "1";
        $this->cardType= "1";
    }
    /**
     * 证联实名认证
     * @author  lwj
     */
    public function certify($params = null){
      $params['certifTp'] = $this->certifTp;
        $params['cardType'] = $this->cardType;
        $data = array (
            'version' => '1.0',
            'encoding' => '1',
            'txnType' => '04',
            'txnSubType' => '01',
            'bizType' => '000205',
            'channelType' => '00',
            'encryptData' =>$params,
           // 'smsCode' => rand(111111,999999),
            'bindId' => '',
            'respCode' => '',
            'respMsg' => ''
        );
        $post = new encryptSign($this->privateKey,$this->publicKey, null);
        //数据加签
        $post -> sign($data,'02');
        //获取发送的数据
        $value = $post ->getPostData();

        //发送数据
        $getvalue = $this->sendmessage(stripslashes($value));
        $res = $this->responseHandle($post,$getvalue);

        $answer = "";
        if($post -> CheckRsa(stripslashes(json_encode($res['addit'])).$res['data'] ,$res['sign']['signature'])){
            $answer = json_decode($res['data']);
            return  $answer;
        }else{
            throw new Exception("收到应答报文解密,验签结果：失败");
        }
    }
    /**
     * 添加白名单
     * accNo 银行账户
     * accName 银行账户名
     * @author  lwj
     */
    public function addWriteListing($params = null){
        $data = array (
            'version' => '1.0',
            'encoding' => '1',
            'txnType' => '27',
            'txnSubType' => '01',
            'bizType' => '000205',
            'channelType' => '00',
            'accNo' => $params['cardNo'],
            'accName' => $params['customerNm'],
            'respCode' => '',
            'respMsg' => ''
        );
        $post = new encryptSign($this->privateKey,$this->publicKey, null);
        //数据加签
        $post -> sign($data,'02');
        //获取发送的数据
        $value = $post ->getPostData();

        //发送数据
        $getvalue = $this->sendmessage(stripslashes($value));
        $res = $this->responseHandle($post,$getvalue);

        $answer = "";
        if($post -> CheckRsa(stripslashes(json_encode($res['addit'])).$res['data'] ,$res['sign']['signature'])){
            $answer = json_decode($res['data']);
            return  $answer;
        }else{
            throw new Exception("收到应答报文解密,验签结果：失败");
        }
    }

    /**
     * 证联订单生成
     */
    function createOrdertn($params){
        $data = array (
            'version' => '1.0',
            'encoding' => '1',
            'txnType' => '17',
            'txnSubType' => '01',
            'bizType' => '000205',
            'channelType' => '00',
            'tn'=>$params['sn'],
            'txnAmt'=>$params['pay_money']*100,
            'orderDesc'=>'充值',
            'backUrl' => $this->backUrl,
            'bindId' => $params['bindId'],
            'orderId'=>$params['id'],
            'orderType'=>'0001',
            'currencyCode'=>'156',
            'txnTime'=>date("YmdHis"),
            'payTimeout'=>date("YmdHis",time()+7*3600*24),
            'respCode' => '',
            'respMsg' => ''
        );
        $post = new encryptSign($this->privateKey,$this->publicKey, null);
        //数据加签
        $post -> sign($data,'02');
        //获取发送的数据
        $value = $post ->getPostData();

        //发送数据
        $getvalue = $this->sendmessage(stripslashes($value));

        $res = $this->responseHandle($post,$getvalue);
        $answer = "";
        if($post -> CheckRsa(stripslashes(json_encode($res['addit'])).$res['data'] ,$res['sign']['signature'])){

            $answer = json_decode($res['data']);
            return  $answer;
        }else{
            throw new Exception("收到应答报文解密,验签结果：失败");
        }
    }
    /**
     * 证联短信验证
     * $params['tn'] 受理订单号
     * $parmas['bindId'] 证联绑卡ID
     */
    function smsVerify($params){

        $data = array (
            'version' => '1.0',
            'encoding' => '1',
            'txnType' => '21',
            'txnSubType' => '01',
            'bizType' => '000205',
            'channelType' => '00',
            'smsType'=>"03",
            'reserved'=>json_encode($params),
            'respCode' => '',
            'respMsg' => ''
        );
        $post = new encryptSign($this->privateKey,$this->publicKey, null);
        //数据加签
        $post -> sign($data,'02');
        //获取发送的数据
        $value = $post ->getPostData();

        //发送数据
        $getvalue = $this->sendmessage(stripslashes($value));
        $res = $this->responseHandle($post,$getvalue);

        $answer = "";
        if($post -> CheckRsa(stripslashes(json_encode($res['addit'])).$res['data'] ,$res['sign']['signature'])){

            $answer = json_decode($res['data']);
            return  $answer;
        }else{
            throw new Exception("收到应答报文解密,验签结果：失败");
        }
    }
    /**
     * 证联订单支付
     */
    function payOrder($params){
        $data = array (
            'version' => '1.0',
            'encoding' => '1',
            'txnType' => '14',
            'txnSubType' => '01',
            'bizType' => '000205',
            'channelType' => '00',
            'tn'=>$params['bank_sn'],
            'txnAmt'=>$params['pay_money'],
            'bindId' => $params['bindId'],
            'currencyCode'=>'156',
            'smsCode'=>$params['code'],
            'respCode' => '',
            'respMsg' => ''
        );
        $post = new encryptSign($this->privateKey,$this->publicKey, null);
        //数据加签
        $post -> sign($data,'02');
        //获取发送的数据
        $value = $post ->getPostData();
        \Yii::getLogger()->log("str",3);
        //发送数据
        $getvalue = $this->sendmessage(stripslashes($value));

        $res = $this->responseHandle($post,$getvalue);

        $answer = "";
        if($post -> CheckRsa(stripslashes(json_encode($res['addit'])).$res['data'] ,$res['sign']['signature'])){

            $answer = json_decode($res['data']);
            return  $answer;
        }else{
            throw new Exception("收到应答报文解密,验签结果：失败");
        }
    }

    /**
     * @param $answer
     * 解密回调数据
     */
    function decryKey($answer){
        $encryObj = new encryptSign($this->privateKey,$this->publicKey, null);
        /**
         *用私钥获取key
         */
        $keyData = $encryObj-> PriDecrypt($answer['addit']['encryKey']);
        /**
         * 用key解出data
         */
        $encryObj->beforeAESKey = $keyData;
        $data = $encryObj->decpytData($answer['data']);
        if($data) return $data; else return "";
    }
    /**
     * 发送数据到证联
     */
    function sendmessage($data){

        $request = 'POST /'.$this->url." HTTP/1.1\nHost:".$this->pip."\nContent-Type:application/x-www-form-urlencoded\nContent-Length:".(strlen($data))."\nConnection:Close\n\n".$data;
        $fp = fsockopen($this->pip,$this->pport,$errno,$errstr,3) or exit ("\n\n".$errstr.'---->'.$errno);
        if(is_resource($fp)){
            fputs($fp,$request) or exit ("\n\n".'通讯异常');
            $contents = strstr(stream_get_contents($fp), "%7B%22");
            $cont = preg_replace("/\s+[a-z|0-9|a-z0-9].*\s+/" , '', $contents);
            fclose($fp);
        }else
            throw new Exception("调取银行支付失败");
        return($cont);
    }
    /**
     * 应答数据处理
     */
    function responseHandle($post,$getvalue){
        if($getvalue) {
            //应答数据
            $getvalue = (array)json_decode(urldecode($getvalue));
            $getvalue['addit'] = (array)($getvalue['addit']);
            $getvalue['sign'] = (array)($getvalue['sign']);

            $getvalue['data'] = $post->decpytData($getvalue['data']);
            $getvalue['addit']['riskInfo'] = (array)json_decode($post->decpytData($getvalue['addit']['riskInfo']));
            return $getvalue;
        }else
            return false;
    }

}
