<?php
namespace common\extension\zlpay\lib;
use yii;
class encryptSign{
    
    private $prikpath;
    //公钥路径
    private $pubkpath;
    //业务数据
    private $businessData;
    //加密信息域
    private $addit;
    //加签信息域
    private $sign;
    //商户生成key
    public $beforeAESKey;
    public $afterAESKey;
    //tokenAutho
    private $tokenAutho;
    private $postData = array(
        'data' => '',
        'addit' => '',
        'sign' => array(
          //签名
          'signature' => '',
          //加密方法 01:RSA 02:MD5
          'signMethod' => '')
    );
 
    function __construct($pripath, $pubkpath, $tokenAutho) {
        
        $this->prikpath = $pripath;
        $this->pubkpath = $pubkpath;
        $this->tokenAutho = $tokenAutho;
        
      //$this->merchantKey = strtoupper(hash('ripemd128', $uid . md5($data)));
        $this->postData['addit'] = array(
            'accessType' => 1,
          //合作机构号
          'coopInstiId' =>Yii::$app->ZlPay->instuId,
            //合作机构号
            'merId' => Yii::$app->ZlPay->merid,
          //加密KEY
          'encryKey' => '',
          //加密方法 01:AES
          'encryMethod' => '01',
          //风控信息
          'riskInfo' => array(
              'random' => time(),
              'timestamp' => date('Ymdhms',time()),
              'os' => 'linux',
              'deviceIMEI' =>Yii::$app->ZlPay->deviceimei
          )
      );
      
    }

    function PriRes(){

        $priKey = file_get_contents(dirname(dirname(__FILE__))."/key/".$this->prikpath);
        $res = openssl_get_privatekey($priKey);
        return $res;
    }

    function PubRes(){

        $pubKey = file_get_contents(dirname(dirname(__FILE__))."/key/".$this->pubkpath);
        $res = openssl_get_publickey($pubKey);
        return $res;
    }
    //生成key
    function createKey(){
        $uid = uniqid("", true);
        $data = "";
        $data .= mt_rand() . mt_rand() . time() . mt_rand() . mt_rand();

        $this->beforeAESKey = hash('ripemd128', $uid . md5($data));
        file_put_contents('keyfile', $this->beforeAESKey);
        //echo '生成的key：'.$this->beforeAESKey;
    }
    //公钥加密
    function pubEncrypt($pubEncryptData){

        $encrypted = '';
        $res = $this->PubRes();
        $encyptlen = rand(64,117);
        //echo '<br>待加密数据'.$pubEncryptData;
        $strlen = ceil( strlen($pubEncryptData) / $encyptlen );
        //echo '<br>循环次数'.$strlen;
        $flag = "";
        for($i = 0; $i < $strlen;){
            $flag .= openssl_public_encrypt(substr($pubEncryptData, $i, $i + $encyptlen), $encrypted, $res);
            $i += $encyptlen;
        }
        if( 1 != $flag ){
            exit('加密错误');
        }
        unset($flag);
        openssl_free_key($res);
        return BASE64_ENCODE($encrypted);

    }
    //补数据
    private static function pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    //加密数据
    public function encpytData($input) {


        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = self::pkcs5_pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, hex2bin($this->beforeAESKey), $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);

        return $data;
    }
    //加密数据
    public function decpytData($encryptData) {

        //echo '<br>key:'.$this->beforeAESKey;
        //echo '<br>待解密字段：'.$encryptData;
        $decrypted= mcrypt_decrypt(MCRYPT_RIJNDAEL_128,hex2bin($this->beforeAESKey), base64_decode($encryptData), MCRYPT_MODE_ECB);
        //echo '<br>解密：';
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s-1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }
    //签名
    function sign($data, $method){

        //获取业务数据
        $this->postData['data'] = json_encode($data,JSON_UNESCAPED_UNICODE);
        //获取加签方式
        $this->postData['sign']['signMethod'] = $method;
        //创建key
        $this->createKey();
        //对key用公钥加密
        $this->afterAESKey = $this->pubEncrypt($this->beforeAESKey);
        //encryKey中填充公钥加密后的串
        $this->postData['addit']['encryKey'] = $this->afterAESKey;
        //如果token为空，使用用户生成的key
        //拼接好待加签的串
        $signData = is_null($this->tokenAutho) ? $this->beforeAESKey : $this->tokenAutho;
        $signData .= stripslashes(json_encode($this->postData['addit'])).$this->postData['data'];
        if( '02' == $method ){
            $this->postData['sign']['signature'] = strtoupper(md5($signData));
        }elseif ( '01' == $method ) {

            $sign="";
            $res = $this->PriRes();
            //echo '<br>签名原串：'.stripslashes($signData);
            openssl_sign(stripslashes($signData), $sign, $res);
            $this->postData['sign']['signature'] = base64_encode($sign);

            openssl_free_key($res);
        }

        //echo '<h5>加签后的数据</h5>';
        //echo '<br>';

        //对业务数据用key加密
        $this->postData['data'] = $this->encpytData($this->postData['data']);
        //处理riskinfo域
        $this->postData['addit']['riskInfo'] = json_encode($this->postData['addit']['riskInfo']);
        //对riskInfo域用key加密
        $this->postData['addit']['riskInfo'] = $this->encpytData($this->postData['addit']['riskInfo']);
    }
    //验签 
    function CheckRsa($data,$sign) {

        $res = $this->PubRes();
        $result = openssl_verify($data, BASE64_DECODE($sign), $res);
        openssl_free_key($res);

        return $result;
    }
    //获取发送的数据
    function getPostData() {

        $sendData = 'data='.  urlencode($this->postData['data']);
        $sendData .= '&addit='.urlencode(json_encode($this->postData['addit']));
        $sendData .= '&sign='.urlencode(json_encode($this->postData['sign']));
        //print_r($sendData);
        return ($sendData);
    }
    public function PriDecrypt($data){
        $flag = '';
        $encode = '';
        $this->data = base64_decode($data);
        $encyptlen = 128;
        $res = $this->PriRes();
        $strlen = ceil( strlen($this->data) / $encyptlen );
        for($i = 0; $i < $strlen; $i++ ){
            $decrypted = '';
            $flag .= openssl_private_decrypt(substr($this->data, $i, $i + $encyptlen), $decrypted, $res);
            $i += $encyptlen;
            $encode .= $decrypted;
        }
        if( 1 != $flag ){
          return '解密错误';
        }
        unset($flag);
        openssl_free_key($res);

        return $encode;
    }

    function __destruct() {

    }
}