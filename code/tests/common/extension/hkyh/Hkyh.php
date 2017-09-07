<?php

/* * ****************************
 * $File: bank.class.php
 * $Description: 银行存管核心处理类
 * $Author: kai
 * $Time:2017-04-29
 * $Update:None 
 * $UpdateDate:None 
 * **************************** */


namespace common\extension\Hkyh;

// use common\extension\yilian\lib\Crypt3Des;
// use common\extension\yilian\lib\reqBean;
// use common\extension\yilian\lib\RSA;
// use common\service\LogService as Log;
use yii\base\Component;
// use api\common\helpers\ReseponseCode as Code;

/**
 * 海口银行接口组件
 */
class Hkyh extends Component
{

    const ERROR = '操作有误，请不要乱操作';
    const PRIVATEKEY_ERROR = '私钥错误，请核实';
    const SERVICE_NOT_FOUND_ERROR = '接口不存在，请核实';
    const PRIVATEKEY_SIGN_ERROR = '私钥加密失败';
    const VERIFY_PARAMETER_ERROR = '验签必要参数为空';
    const REQUESTNO_CAN_NOT_EMPTY_ERROR = '流水号不得为空';

    public $platformNo; // 商户编号

    public $keySerial;
    public $gatewayUrl; //网关请求地址
    public $serviceUrl; //直连请求地址

    public $lmPublicKey;

    public $publicKey;

    public $privateKey;

    public $RETURN_URL; //异步通知地址

    public $RETURN_BASE_URL; //同步域名地址

    public $RETURN_PC_URL; // pc 回调地址

    public function __construct(){
        $this->lmPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAochwsj6pfEIBTfcfaLpmQFLY6t7/hWe5DMrJRAfG4+1ZKXfs4Zj1gSlAJaJAjxIOWlKJdwyDDiIiFROnPEvBd8lsDiCvKu1f/mq35U16VVHxVX5F4yCLiPj8nK2wqlRfElpsgnkKFOxodkQGdy9ZZchewlQaeP4OOYFpsaNTm3LEm5K1MbRIb58XGuA2od40oOR+UmF4hPMDjJKM92kx/4MFbnO6rx89K9n2CsAMLBUZ7IGZ9gXbPnpzuuUH+qqIcdp78305TykTgX1tdn59eom7LxeIRaEmfQPvZBa4vytovHHJtqRyc2hvV8HbP6PMuP888KpMblH1WWB2BSDVKwIDAQAB";

        $this->privateKey = "MIIEpQIBAAKCAQEAtFW123mvxb63k+Ocwtce758yu0CrXDQfew7iORslAw1sCeSD
                            sKpIX/Ovm38gpUTmBjvmuCJ4wQxHL6UjgRFGGtTzxr9jwCVUBOmEboTRm2rOL533
                            mAteyEYWh9ePUmtcdNOYiFFStkFrgJSeomcQpahtVGI04DBOuWsb+XgZOPDmecBu
                            7CCp1n5SlqQxmbxnK12F4rPMUT+2tgoosHcwmKoid9Ayo861+bUwmDS6jWCHOHsX
                            RMZWfW8YxPbyK60D3e4Awig6YVBfmhMo7IvepAcojc798ZfYTwXAkUrtA9s66a+v
                            658geMDtLPuiOPvfmg2h3QJymIZJPmnv2Y7pdQIDAQABAoIBAB5qrofcWId0CabK
                            J3C9tBwasAXhfIXKzNyGwCV9rJp9MxHgF04/CoIUqjQCF1GI83ttsxavycpV9dPV
                            eM2WSkrQTGXjdkG8ihViBdJWWLxsIWbqsA5XLJ9/IuG6vBPcw/V4kyu6+8Z99lHz
                            v2YJGEe4xugKwHxn6X9gz5EebrKpts7tSomAI6/tj7bGQ4XZrAheiGphRbt7hxMi
                            oHZfVhP6+tdsN3U/nX8vwLKuIiW7t4EytRjjZBKnBd9vhrVekv2k/u+6Buzq63ac
                            PMq2b5G2Ah/GHc5siBNEsIZsirKgFa57d1naRKY7uPgfJFK1Tg5JOTbUdgw79AOJ
                            jqw10AECgYEA3q+KTj8DB4QViZrtcUT76zquwqSyAQ5OD17wvfar1dugEh/Q325s
                            aHRMvUbWpNJq6gnrri5dmKSjXtd1TAjKWR0zVfl0wu5/YyFza98GO1UGI/cCa61/
                            quWOomBqaR906xoY62YPP6JodZVekeVNcvIWGe+QMO0hnZcA77YdrgECgYEAz1A1
                            EyINoHe2eALwpFowk5uVUNybeYn3ehJ2eq6uxAkF1HH1EX7drUnQZBG1DRJpPdaN
                            CpunqmRrX12EeJW5e11I2aQiPoxLfRt8OfNexUGgufnGd5MDOuUpxYMo1or81qEy
                            rZwsj4mWgWpP11vvQIZDmMk7rGqj+0lZG8u0Y3UCgYEAz7mUHHVvD/3IUXpx7+1X
                            yhANoYgcfBup+XCoxatqk62x3fZL04CgI7gJNa703v9huDvU28BoktFRjFEUG/8/
                            Mm2oy5RNtODhd8lmb8O1fndLqRTr7yIDK5nDufiSislLOVw4zq1/n+6R3s2dUB9E
                            ZespbrwoF1u8tdJ8jx+lBAECgYEAlmVhm5fAuwEd5sJ7QUAMrYjZMehZAjsMxgpO
                            2YGQV7cT+6MQXrjiqKw7+dy+s9T/dFUJzQBPClX/bxew2qoD/XMXPozMKwQsX35p
                            CMF9pyyNJV4GlQiZ90f4nckg/0OlWS9oTHpX+WmfPhOaCGoxj2XX9CdImzRh8hKs
                            CnYlT2ECgYEAttx/9kbQ7F2Nl5nMFOfwUZ08lOHb2sql3RIBpQESD7ya+YS9DZgB
                            X+HnUHLrlGQusgbFZfDWFTseNZ6r5alQzGdxcAzas4ESLtjDsDjtjNj03mBZPdyG
                            I8FzB1Ixs2sHW2MSDVCJC5FpzjnMFQ6JbdxFJPK/5SogkHwkxqGuOXg=";
    }

    /**
     * 创建Http请求内容
     */
    function createPostParam($serviceName,$reqData) {

        // global $_G;

        //获取配置参数
        $sign = "";

        //所有的 reqData 的 JSON 内都须包含 timestamp 时间参数
        $reqData['timestamp'] = self::timestamp();
        //记录流水号

        //将传入的所需参数json格式化
        $reqDataJson = json_encode($reqData);//self::GbkToJson($reqData);
        // 私钥
        $privatePem = self::keyToPem('private',$this->privateKey);



        $privateKey = openssl_pkey_get_private($privatePem);
        //判断私钥是否可用
        if($privateKey){

            //验签数据-- OPENSSL_ALGO_MD5
            if (openssl_sign($reqDataJson, $sign, $privateKey, OPENSSL_ALGO_SHA1)) {
                $httpResponse['serviceName'] = $serviceName;
                $httpResponse['platformNo'] = $this->platformNo;
                $httpResponse['keySerial'] = $this->keySerial;
                $httpResponse['reqData'] = $reqDataJson;
                $httpResponse['sign'] = base64_encode($sign);
                //查询接口类型
                $serviceInfo = self::serviceNameInfo($serviceName);
                if($serviceInfo){

                    //判断使用直连或者网关接口
                    if($serviceInfo['type']=='gateway'){
                        //网关接口-- 模拟表单提交
                        return self::createGateWayPOST($this->gatewayUrl, $httpResponse);
                    }else{
                        return self::createHttpResponsePOST($this->serviceUrl, $httpResponse);  //直连接口
                    }
                }
                return ['status'=>'error', 'message'=>self::SERVICE_NOT_FOUND_ERROR, 'data'=>''];
            } else {
                return ['status'=>'error', 'message'=>self::PRIVATEKEY_SIGN_ERROR, 'data'=>''];
            }

        }else{
            return ['status'=>'error', 'message'=>self::PRIVATEKEY_ERROR, 'data'=>''];
        }
    }

    /**
     * 返回时间戳
     */
    function timestamp(){
        return date('YmdHis');
    }

    /**
     * 将JAVA端生成的KEY转成PHP可识别使用的KEY
     */
    function keyToPem($type,$key){
        if(strlen($key)<=10) return false;
        //拼接相应的首部和尾部
        if($type=='public'){
            $pem = "-----BEGIN PUBLIC KEY-----\n".$key."\n-----END PUBLIC KEY-----";
        }elseif($type=='private'){
            $pem = "-----BEGIN RSA PRIVATE KEY-----\n".$key."\n-----END RSA PRIVATE KEY-----";
        }else{
            return false;
        }
        return $pem;
    }

    /**
     * 根据接口名称返回相应的接口类型，接口不齐全，请按平台自身的需求填入相应需要的接口名
     */
    function serviceNameInfo($name){

        //网关接口
        $gateway = ['PERSONAL_REGISTER_EXPAND','UNBIND_BANKCARD','PERSONAL_BIND_BANKCARD_EXPAND','RESET_PASSWORD','MODIFY_MOBILE_EXPAND','ACTIVATE_STOCKED_USER','RECHARGE','WITHDRAW','USER_AUTHORIZATION','USER_PRE_TRANSACTION'];

        //直连接口
        $service = ['DIRECT_RECHARGE','CONFIRM_WITHDRAW','CANCEL_WITHDRAW','ESTABLISH_PROJECT','MODIFY_PROJECT','CANCEL_PRE_TRANSACTION','SYNC_TRANSACTION','ASYNC_TRANSACTION','DEBENTURE_SALE','CANCEL_DEBENTURE_SALE','USER_AUTO_PRE_TRANSACTION','DOWNLOAD_CHECKFILE','CONFIRM_CHECKFILE','UNFREEZE_TRADE_PASSWORD','QUERY_USER_INFORMATION','QUERY_TRANSACTION','QUERY_PROJECT_INFORMATION'];

        //是否异步
        $async = ['PERSONAL_REGISTER_EXPAND','UNBIND_BANKCARD','PERSONAL_BIND_BANKCARD_EXPAND','RESET_PASSWORD','MODIFY_MOBILE_EXPAND','ACTIVATE_STOCKED_USER','RECHARGE','WITHDRAW','USER_PRE_TRANSACTION','ASYNC_TRANSACTION','USER_AUTHORIZATION'];

        $result['async'] = in_array($name,$async);

        if(in_array($name,$gateway)){
            $result['type'] = 'gateway';
        }elseif(in_array($name,$service)){
            $result['type'] = 'service';
        }else{
            //如果两种接口都找不到，则返回错误
            return false;
        }
        return $result;
    }

    /**
     * 模拟网关请求，使用POST方法
     */
    function createGateWayPOST($url,$postData){
        header("Content-type: text/html; charset=utf-8");
        $method = 'POST';

        $html = "<html><head><meta charset='utf-8'></head><body>";
        $html .= "<form id='xitouGateway' name='testGateway' action='" .$url. "' method='" . $method . "'>";
        foreach ($postData as $key =>$value){
            $html .= "<input type='hidden' name='".$key."' value='" . $value . "'/>";
        }
        $html .= "<input type='submit' style='display:none;' value='submit'></form>";
        $html .= "<script>document.forms['testGateway'].submit();</script>";
        $html .= "</body></html>";
        echo $html;
        exit;
    }

    /**
     * 模拟HTTP请求，使用POST方法
     */
    function createHttpResponsePOST($url,$postData){

        $errors_array = array();

        header("Content-type: text/html; charset=utf-8");

        $curl = curl_init($url);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        //curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$postData);// post传输数据

        $response = curl_exec($curl);

        if(curl_error($curl)) $errors_array[] = 'CULR Errors:'.curl_errno($curl).'-->'.curl_error($curl);//捕抓异常

        //没有错误
        if(empty($errors_array)){
            if(stristr($response, '404 Not Found') || empty($response)){
                $errors_array[] = 'API服务器出错';
            }
        }

        curl_close($curl);//关闭curl链接

        if(!empty($errors_array)) return ['status'=>'error', 'message'=>'API服务器出错', 'data'=>''];

        return ['status'=>'success', 'message'=>'请求成功', 'data'=>$response];
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 返回平台用户编号
     */
    function getPlatformNo(){
        return $this->platformNo;
    }

    /**
     * 返回数据验签
     */
    function verify($respData,$sign){
        if(empty($respData)||empty($sign)){
            return self::VERIFY_PARAMETER_ERROR;
        }
        //去除转字符串中的反斜杠
        $respData =  stripslashes($respData);
        //base64解码
        $deSign = base64_decode($sign);
        $lmPublicPem = self::keyToPem('public',$this->lmPublicKey);
        $result = openssl_verify($respData, $deSign, $lmPublicPem, OPENSSL_ALGO_SHA1)===1;
        return $result;
    }
    /**
     * 把数组转成JSON格式，GBK转UTF8，如果是UTF8则把转换的函数删除即可
     */
    // function GbkToJson($data){
    //     $return = json_encode(array_iconv("GB2312","UTF-8//ignore",$data),JSON_UNESCAPED_SLASHES);
    //     return $return;
    // }

    /**
     * 对返回的数据状态判断检查
     */
    function callbackCheck($respData){
        /* 业务规则：
         * R1:查找流水号是否存在
         * R2:查找网关返回的用户ID与跳转前记录的ID是否一致
         * R3:判断该请求的状态
         * R4:判断网关记录的状态
         */
        //code...
    }

}



?>