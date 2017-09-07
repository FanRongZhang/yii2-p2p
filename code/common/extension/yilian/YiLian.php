<?php
namespace common\extension\YiLian;

use common\extension\yilian\lib\Crypt3Des;
use common\extension\yilian\lib\reqBean;
use common\extension\yilian\lib\RSA;
use common\service\LogService as Log;
use yii\base\Component;
use api\common\helpers\ReseponseCode as Code;

/**
 * 易联支付组件
 */
class YiLian extends Component
{
    /**
     * 易联接口下单地址
     * @var String
     */
    public $URL;

    /**
     * 公钥
     * @var String
     */
    public $pub_key;

    /**
     * 私钥路径
     */
    public $pfx_key;

    /**
     * 私钥密码
     */
    public $pfx_pass;

    /**
     * 3des 加密类
     */
    public $des;

    /**
     * 商户类别
     */
    public $rsa;

    /**
     * log 日志类
     */
    public $log;

    /**
     * 易联系统后台用户名
     * @var String
     */
    public $USER_NAME;

    public $VERSION; //版本

    public $RETURN_URL; //异步通知地址
    
    public $MERCHANT_KEY; //密钥

    public $MERCHANT_ID; //商户号

    public function __construct(){
        $this->pfx_key  = dirname(__FILE__)."/yilian.pfx";
        $this->pfx_pass = '11111111';
        $this->pub_key  = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCqWSfUW3fSyoOYzOG8joy3xldpBanLVg8gEDcvm9KxVjqvA/qJI7y0Rmkc1I7l9vAfWtNzphMC+wlulpaAsa/4PbfVj+WhoNQyhG+m4sP27BA8xuevNT9/W7/2ZVk4324NSowwWkaqo1yuZe1wQMcVhROz2h+g7j/uZD0fiCokWwIDAQAB';

        $this->rsa = new RSA($this->pfx_key, $this->pfx_pass);
        $this->des = new Crypt3Des();
        $this->des->setKey(base64_decode($this->generateKey(9999, 24)));

        //组装公钥匙
        $pem = chunk_split($this->pub_key, 64, "\n"); //转换为pem格式的公钥
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $this->pub_key = openssl_get_publickey($pem); //获取公钥内容
    }

    /**
     * 短信验证码功能 - 发送短信验证码到客户绑定手机号
     * @param $data
     */
    public function send_message($data){
        $bean = new reqBean();
        $bean->VERSION = $this->VERSION;
        $bean->USER_NAME = $this->USER_NAME;
        $bean->BATCH_NO = $data['MER_ORDER_NO'];//批次号-SN
        $bean->MSG_TYPE = $this->getMsgType("send_message");

        //body
        $bean->addDetail($data);
        //私钥 对签名加密rsa 然后放到xml中
        $bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);
        $queryXml = $bean->classToXml();

/*        //代收记录信息
        $log_data = array();
        $log_data['acc_no'] = $data['ACC_NO'];
        $log_data['mobile_no'] = $data['MOBILE_NO'];
        $log_data['trans_desc'] = $data['TRANS_DESC'];*/

        //des 加密
        $req_body_enc = $this->des->encrypt($queryXml);
        //公钥 rsa  加密
        $req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key);
        $sendTxt = $req_body_enc . "|" . $req_key_enc;
        $data = $this->postXmlUrl($this->URL, $sendTxt, true);
        $result = explode("|", $data);
        $key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
        $this->des->setKey(base64_decode($key_3des));
        $receiveXml = $this->des->decrypt($result[0]);
        $bean->xmlToClass($receiveXml);

        if ($this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
            $res_state = array();
            $res_state['TRANS_STATE'] = $bean->TRANS_STATE;
            $res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
            $res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];
            $res_state['REMARK'] = $res_state['TRANS_DETAILS'][0]['REMARK'];
            return $res_state;
        }
        return "";
    }

    /**
     * 认证
     * @param $data
     * @return array|string
     * @throws
     */
    public function verify($data){
        //req_bean
        $bean = new reqBean();
        $bean->VERSION = $this->VERSION;
        $bean->USER_NAME = $this->USER_NAME;
        $bean->BATCH_NO = $data['MER_ORDER_NO'];//批次号-SN

        $bean->MSG_TYPE = $this->getMsgType("verify");

        //body
        $data['SN'] = 'SN'.date('YmdHis');
        $bean->addDetail($data);

        //私钥 对签名加密rsa 然后放到xml中
        $bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);

        $queryXml = $bean->classToXml();
        //print_r($queryXml);exit;
        //绑卡记录信息
        /*$log_data = array();
        $log_data['orderid'] = $data['MER_ORDER_NO'];
        $log_data['bank_icon'] = $data['BANK_CODE'];
        $log_data['bank_code'] = $data['ACC_NO'];
        $log_data['idcard'] = $data['ID_NO'];
        $log_data['bank_name'] = $data['ACC_NAME'];
        $log_data['mobile'] = $data['MOBILE_NO'];
        $log_data['createtime'] = date('Y-m-d H:i:s',time());
        $log_data['sn'] = $data['SN'];//默认*/

        $req_body_enc = $this->des->encrypt($queryXml); //des 加密
        $req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key); //公钥 rsa  加密
        $sendTxt = $req_body_enc . "|" . $req_key_enc;

        $data = $this->postXmlUrl($this->URL, $sendTxt, true);

        $result = explode("|", $data);
        $key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
        $this->des->setKey(base64_decode($key_3des));
        $receiveXml = $this->des->decrypt($result[0]);
        $bean->xmlToClass($receiveXml);

        if ($res = $this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
            $res_state = array();
            $res_state['TRANS_STATE'] = $bean->TRANS_STATE;
            $res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
            $res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];
            $res_state['REMARK'] = $res_state['TRANS_DETAILS'][0]['REMARK'];

            return $res_state;
        }

        return "";
    }

    /**
     * 代收功能 - 客户付款给我商户
     * @param $data
     */
    public function gather($data){
        $bean = new reqBean();
        $bean->VERSION = $this->VERSION;
        $bean->USER_NAME = $this->USER_NAME;
        $bean->BATCH_NO = $data['MER_ORDER_NO'];
        $bean->MSG_TYPE = $this->getMsgType("gather");

        //body
        $data['SN'] = 'SN'.date('YmdHis');
        $bean->addDetail($data);
        //私钥 对签名加密rsa 然后放到xml中
        $bean->MSG_SIGN = $this->rsa->sign($bean->toSign(), $this->rsa->priKey);
        $queryXml = $bean->classToXml();

/*        //代收记录信息
        $log_data = array();
        $log_data['orderid'] = $data['MER_ORDER_NO'];
        $log_data['acc_no'] = $data['ACC_NO'];
        $log_data['acc_name'] = $data['ACC_NAME'];
        $log_data['idcard'] = $data['ID_NO'];
        $log_data['amount'] = $data['AMOUNT'];
        $log_data['createtime'] = date('Y-m-d H:i:s',time());
        $log_data['sn'] = $data['SN'];//默认*/

        //des 加密
        $req_body_enc = $this->des->encrypt($queryXml);
        //公钥 rsa  加密
        $req_key_enc = $this->rsa->encrypt(base64_encode($this->des->key), $this->pub_key);
        $sendTxt = $req_body_enc . "|" . $req_key_enc;
        $data = $this->postXmlUrl($this->URL, $sendTxt, true);
        $result = explode("|", $data);
        $key_3des = $this->rsa->decrypt($result[1], $this->rsa->priKey); //私钥匙 rsa  解密
        $this->des->setKey(base64_decode($key_3des));
        $receiveXml = $this->des->decrypt($result[0]);
        $bean->xmlToClass($receiveXml);

        if ($this->rsa->verify($bean->toSign(), $bean->MSG_SIGN, $this->pub_key)){
            $res_state = array();
            $res_state['TRANS_STATE'] = $bean->TRANS_STATE;
            $res_state['TRANS_DETAILS'] = $bean->TRANS_DETAILS;
            $res_state['PAY_STATE'] = $res_state['TRANS_DETAILS'][0]['PAY_STATE'];
            $res_state['REMARK'] = $res_state['TRANS_DETAILS'][0]['REMARK'];
            return $res_state;
        }
        return "";
    }

    //设置是代付、代收、认证
    public function getMsgType($type){
        if ($type == "pay"){//批量代付
            return "100001";
        }elseif ($type == "pay_query"){//批量代付查询
            return "100002";
        }elseif ($type == "send_message"){//发送短信验证码
            return "500001";
        }elseif ($type == "gather"){//批量代收
            return "200001";
        }elseif ($type == "gather_query"){//批量代收查询
            return "200002";
        }elseif($type == 'verify'){//认证
            return "300001";
        }elseif($type == 'verify_query'){//认证查询
            return "300002";
        }elseif($type == 'verify_remover'){//解绑
            return "300004";
        }
        return '';
    }

    /* 发送数据返回接收数据 */
    public function postXmlUrl($url, $xmlStr, $ssl = false, $type = "Content-type: text/xml")
    {
        $ch = curl_init();
        $params = array();
        if ($type)
            $params[] = $type; //定义content-type为xml
        curl_setopt($ch, CURLOPT_URL, $url); //定义表单提交地址
        if ($ssl)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_POST, 1);   //定义提交类型 1：POST ；0：GET
        curl_setopt($ch, CURLOPT_HEADER, 0); //定义是否显示状态头 1：显示 ； 0：不显示
        if ($params)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $params); //定义请求类型
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //定义是否直接输出返回流
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlStr); //定义提交的数据，这里是XML文件
        //封禁"Expect"头域
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $xml_data = curl_exec($ch);
        if (curl_errno($ch))
        {
            throw new Exception(curl_error($ch));
        }
        else
        {
            curl_close($ch);
        }

        return $xml_data;
    }

    //生成24位随机码
    public function generateKey($round, $length)
    {
        $key = "";
        for ($i = 0; $i < $length; $i++)
        {
            $random = rand(0, $round) % 16;
            switch ($random)
            {
                case 0: $key .= "0";
                    break;
                case 1: $key .= "1";
                    break;
                case 2: $key .= "2";
                    break;
                case 3: $key .= "3";
                    break;
                case 4: $key .= "4";
                    break;
                case 5: $key .= "5";
                    break;
                case 6: $key .= "6";
                    break;
                case 7: $key .= "7";
                    break;
                case 8: $key .= "8";
                    break;
                case 9: $key .= "9";
                    break;
                case 10: $key .= "A";
                    break;
                case 11: $key .= "B";
                    break;
                case 12: $key .= "C";
                    break;
                case 13: $key .= "D";
                    break;
                case 14: $key .= "E";
                    break;
                case 15: $key .= "F";
                    break;
                default: $i--;
            }
        }

        return base64_encode($key);
    }

}
