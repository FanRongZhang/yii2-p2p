<?php
/**
 * 快钱支付类
 * @author xiaomalover <xiaomalover@gmail.com>
 * 包括绑卡验证码获取，绑卡，支付验证码获取，支付
 */
namespace common\extension\kuaiqian;

use yii\base\Component;


class KuaiQian extends Component
{

    /**
     * 版本号
     */
    public $version = "1.0";

    /**
     * 证书文件名
     */
    public $pemFile;

    /**
     * 商户号
     */
    public $merchantId;

    /**
     * 证书密码
     */
    public $certPassword;

    /**
     * 商户绑卡终端号
     */
    public $terminalIdBind;

    /**
     * 商户充值终端号
     */
    public $terminalIdCharge;

    /**
     * 异步回调地址
     */
    public $tr3Url;

    /**
     * 调试模式
     */
    public $debug;

    /**
     * 基础URL
     */
    public $baseUrl;

    /**
     * 快钱绑卡前获取手机验证码
     * @param  Int $customerId  用户id
     * @param  String $externalRefNumber 外部跟踪订单号
     * @param  String $pan 银行卡卡号
     * @param  String $cardHolderName 持卡人姓名
     * @param  String $idType 持卡人证件类型
     * @param  String $cardHolderId 持卡人证件号
     * @param  String $phoneNO 持卡人手机号
     * @return Array 结果
     */
    public function getVerifyCodeBeforBind($customerId, $externalRefNumber, $pan, $cardHolderName, $idType, $cardHolderId, $phoneNO)
    {
        //调试模式下，直接返回成功的结果信息
        if ($this->debug) {
            return $this->getContent("beforebind.txt");
        }

        $url = $this->baseUrl."/cnp/ind_auth";
        $xmlstr = "";
        $xmlstr.= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";
        $xmlstr.= "<MasMessage xmlns=\"http://www.99bill.com/mas_cnp_merchant_interface\">";
        $xmlstr.= "<version>".$this->version."</version>";
        $xmlstr.= "<indAuthContent>";
        $xmlstr.= "<merchantId>".$this->merchantId."</merchantId>";
        $xmlstr.= "<terminalId>".$this->terminalIdBind."</terminalId>";
        $xmlstr.= "<customerId>".$customerId."</customerId>";
        $xmlstr.= "<externalRefNumber>".$externalRefNumber."</externalRefNumber>";
        $xmlstr.= "<pan>".$pan."</pan>";
        $xmlstr.= "<cardHolderName>".$cardHolderName."</cardHolderName>";
        $xmlstr.= "<idType>".$idType."</idType>";
        $xmlstr.= "<cardHolderId>".$cardHolderId."</cardHolderId>";
        $xmlstr.= "<phoneNO>".$phoneNO."</phoneNO>";
        $xmlstr.= "</indAuthContent>";
        $xmlstr.= "</MasMessage>";
        $res = $this->sendTr1($url, $xmlstr);
        return $res;
    }


    /**
     * 绑卡方法
     * @param  Int $customerId 用户id
     * @param  String $externalRefNumber 外部跟踪订单号
     * @param  String $pan 银行卡卡号
     * @param  String $phoneNO 持卡人手机号
     * @param  String $validCode  短信验证码
     * @param  String $token 验证令牌
     * @return Array 结果
     */
    public function bind($customerId, $externalRefNumber, $pan, $phoneNO, $validCode, $token)
    {
        //调试模式下，直接返回成功的结果信息
        if ($this->debug) {
            return $this->getContent("bind.txt");
        }

        $url = $this->baseUrl."/cnp/ind_auth_verify";
        $xmlstr = "";
        $xmlstr.= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";
        $xmlstr.= "<MasMessage xmlns=\"http://www.99bill.com/mas_cnp_merchant_interface\">";
        $xmlstr.= "<version>".$this->version."</version>";
        $xmlstr.= "<indAuthDynVerifyContent>";
        $xmlstr.= "<merchantId>".$this->merchantId."</merchantId>";
        $xmlstr.= "<customerId>".$customerId."</customerId>";
        $xmlstr.= "<externalRefNumber>".$externalRefNumber."</externalRefNumber>";
        $xmlstr.= "<pan>".$pan."</pan>";
        $xmlstr.= "<phoneNO>".$phoneNO."</phoneNO>";
        $xmlstr.= "<validCode>".$validCode."</validCode>";
        $xmlstr.= "<token>".$token."</token>";
        $xmlstr.= "</indAuthDynVerifyContent>";
        $xmlstr.= "</MasMessage>";

        $res = $this->sendTr1($url, $xmlstr);
        return $res;
    }


    /**
     * 查询绑卡信息
     * @param  Int $customerId 用户id
     * @param  string $cardType  卡类型
     * @return Array 结果
     */
    public function query($customerId, $cardType="0000")
    {
        $url = $this->baseUrl."/cnp/pci_query";
        $xmlstr = "";
        $xmlstr.= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";
        $xmlstr.= "<MasMessage xmlns=\"http://www.99bill.com/mas_cnp_merchant_interface\">";
        $xmlstr.= "<version>".$this->version."</version>";
        $xmlstr.= "<PciQueryContent>";
        $xmlstr.= "<merchantId>".$this->merchantId."</merchantId>";
        $xmlstr.= "<customerId>".$customerId."</customerId>";
        $xmlstr.= "<cardType>".$cardType."</cardType>";
        $xmlstr.= "</PciQueryContent>";
        $xmlstr.= "</MasMessage>";
        $res = $this->sendTr1($url, $xmlstr);
        return $res;
    }


    /**
     * 支付前验证码
     * @param  Int $customerId 用户id
     * @param  String $externalRefNumber 外部跟踪订单号
     * @param  String $storablePan  缩略卡号
     * @param  String $bankId  银行的英文缩写
     * @param  Number $amount  金额
     * @return Array 结果数组
     */
    public function getVerifyCodeBeforPay($customerId, $externalRefNumber, $storablePan, $bankId, $amount)
    {
        //调试模式下，直接返回成功的结果信息
        if ($this->debug) {
            return $this->getContent("beforepay.txt");
        }

        $url = $this->baseUrl."/cnp/getDynNum";
        $xmlstr = "";
        $xmlstr.= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $xmlstr.= "<MasMessage xmlns=\"http://www.99bill.com/mas_cnp_merchant_interface\">";
        $xmlstr.= "<version>".$this->version."</version>";
        $xmlstr.= "<GetDynNumContent>";
        $xmlstr.= "<merchantId>".$this->merchantId."</merchantId>";
        $xmlstr.= "<customerId>".$customerId."</customerId>";
        $xmlstr.= "<externalRefNumber>".$externalRefNumber."</externalRefNumber>";
        $xmlstr.= "<storablePan>".$storablePan."</storablePan>";
        $xmlstr.= "<bankId>".$bankId."</bankId>";
        $xmlstr.= "<amount>".$amount."</amount>";
        $xmlstr.= "</GetDynNumContent>";
        $xmlstr.= "</MasMessage>";
        $res = $this->sendTr1($url, $xmlstr);
        return $res;
    }


    /**
     * 快钱支付（这里为2次支付，因为先会绑卡）
     * @param  String $interactiveStatus 消息状态
     * @param  String $txnType 交易类型
     * @param  String $entryTime 商户端交易时间
     * @param  String $storableCardNo 缩略卡号
     * @param  Number $amount 金额
     * @param  String $externalRefNumber 外部跟踪订单号
     * @param  Int $customerId 用户id
     * @param  String $spFlag  特殊交易标志
     * @param  String $phone 电话
     * @param  String $validCode 验证码
     * @param  Int $savePciFlag 是否保存鉴权信息
     * @param  String $token 验证令牌
     * @param  String $payBatch  快捷支付批次 (1：首次支付 2：再次支付)
     * @return Array 结果数组
     */
    public function pay($interactiveStatus, $txnType, $entryTime, $storableCardNo, $amount, $externalRefNumber, $customerId, $spFlag, $phone, $validCode, $savePciFlag, $token, $payBatch)
    {
        //调试模式下，直接返回成功的结果信息
        if ($this->debug) {
            return $this->getContent("pay.txt");
        }

        $url = $this->baseUrl."/cnp/purchase";
        $xmlstr = "";
        $xmlstr.= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $xmlstr.= "<MasMessage xmlns=\"http://www.99bill.com/mas_cnp_merchant_interface\">";
        $xmlstr.= "<version>".$this->version."</version>";
        $xmlstr.= "<TxnMsgContent>";
        $xmlstr.= "<interactiveStatus>".$interactiveStatus."</interactiveStatus>";
        $xmlstr.= "<txnType>".$txnType."</txnType>";
        $xmlstr.= "<merchantId>".$this->merchantId."</merchantId>";
        $xmlstr.= "<terminalId>".$this->terminalIdCharge."</terminalId>";
        $xmlstr.= "<tr3Url>".$this->tr3Url."</tr3Url>";
        $xmlstr.= "<entryTime>".$entryTime."</entryTime>";
        $xmlstr.= "<storableCardNo>".$storableCardNo."</storableCardNo>";
        $xmlstr.= "<amount>".$amount."</amount>";
        $xmlstr.= "<externalRefNumber>".$externalRefNumber."</externalRefNumber>";
        $xmlstr.= "<customerId>".$customerId."</customerId>";
        $xmlstr.= "<spFlag>".$spFlag."</spFlag>";
        $xmlstr.= "<extMap>";
        $xmlstr.= "<extDate><key>phone</key><value>".$phone."</value></extDate>";
        $xmlstr.= "<extDate><key>validCode</key><value>".$validCode."</value></extDate>";
        $xmlstr.= "<extDate><key>savePciFlag</key><value>".$savePciFlag."</value></extDate>";
        $xmlstr.= "<extDate><key>token</key><value>".$token."</value></extDate>";
        $xmlstr.= "<extDate><key>payBatch</key><value>".$payBatch."</value></extDate>";
        $xmlstr.= "</extMap>";
        $xmlstr.= "</TxnMsgContent>";
        $xmlstr.= "</MasMessage>";
        $res = $this->sendTr1($url, $xmlstr);
        return $res;
    }


    /**
     * curl提交数据方法
     * @param  String $url 请求地址
     * @param  XML $reqXml 请求数据
     * @return Array 结果数组
     */
    private function sendTr1($url, $reqXml)
    {

        $merchantId = $this->merchantId;
        $certFileName = dirname(__FILE__).DIRECTORY_SEPARATOR.$this->pemFile;
        $certPassword = $this->certPassword;
        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

        $loginInfo = array( "Authorization: Basic " . base64_encode($this->merchantId.":".$this->certPassword));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
        curl_setopt($ch, CURLOPT_USERAGENT,$user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_CAINFO, $certFileName);
        curl_setopt($ch, CURLOPT_SSLCERT, $certFileName);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $certPassword);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reqXml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $loginInfo);

        $tr2Xml=curl_exec($ch);

        if (curl_error($ch)){
           $res = curl_errno($ch).curl_error($ch);
        }else{
           $res = $this->xmlToArray($tr2Xml);
        }

        curl_close ($ch);

        return $res;
    }


    /**
     * 处理回调逻辑
     * @param  String $content xml回调结果
     * @return String|Boolean 成功返回订单号 失败返回false
     */
    public function handleNotify($content)
    {
        $res = $this->xmlToArray($content);
        //调试模式下，模拟数据
        if ($this->debug) {
            $res = $this->getContent("notify.txt");
        }
        if(isset($res['TxnMsgContent']) &&
           isset($res['TxnMsgContent']['responseCode']) &&
           $res['TxnMsgContent']['responseCode'] == '00'){
            $data = [];
            $txnType = $res['TxnMsgContent']['txnType'];
            $data['sn'] = $res['TxnMsgContent']['externalRefNumber'];
            $data['ref'] = $res['TxnMsgContent']['refNumber'];
            $data['returnData'] = $this->setReturnData($txnType, $data['ref']);
            return $data;
        }else{
            return false;
        }
    }


    /**
     * 准备异步回调返回的数据
     */
    private function setReturnData($txnType,$refNumber)
    {
        $export='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <MasMessage xmlns="http://www.99bill.com/mas_cnp_merchant_interface">
                <version>'.$this->version.'</version>
                <TxnMsgContent>
                    <txnType>'.$txnType.'</txnType>
                    <interactiveStatus>TR4</interactiveStatus>
                    <merchantId>'.$this->merchantId.'</merchantId>
                    <terminalId>'.$this->terminalIdCharge.'</terminalId>
                    <refNumber>'.$refNumber.'</refNumber>
                    </TxnMsgContent>
                </MasMessage>';
        return $export;
    }

    /**
     * 将xml转成数组
     * @param  String $xmlstring xml字符串
     * @return Array 数组
     */
    private function xmlToArray($xmlstring) {
        return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
    }


    /**
     * 调试模式读取成功结果
     * @param  String $fileName 文件名
     * @return Array 结果数组
     */
    private function getContent($fileName)
    {
        $content = '';
        $fullFileName = dirname(__file__)."/test/".$fileName;
        if (file_exists($fullFileName)) {
            $content = unserialize(file_get_contents($fullFileName));
        }
        return $content;
    }
}