<?php

class pycreditClass
{
    const REPORT_TYPE_Id = 25160;
    const REPORT_TYPE_BankcardNo = 25173;

    static function PY_SendQuery($queryCondition)
    {
//        $debug = true;
        $debug = false;

        //configuration
        $USER_ID = "";
        $PASSWORD = "";
//        $ENDPOINT = "http://www.pycredit.com:9001"; //测试服务器non-ssl
        $ENDPOINT = "https://www.pycredit.com:9443"; //测试服务器ssl
        //$ENDPOINT = "https://www.pycredit.com:8443"; //生产服务器ssl

        //SoapClient获取WSDL不使用ssl设置,所以需要先下载wsdl文件
        $url_wsdl = $ENDPOINT . '/services/WebServiceSingleQuery?wsdl';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_wsdl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 不检查证书中域名
//        curl_setopt($ch, CURLOPT_VERBOSE, '1'); //开发模式，会把通信时的信息显示出来
        curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__) . '/hnzr.cer');  //pem
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, '123456');
        curl_setopt($ch, CURLOPT_SSLKEY, dirname(__FILE__) . '/hnzr.key');  //pem
        curl_setopt($ch, CURLOPT_SSLKEYPASSWORD, '123456');
        curl_setopt($ch, CURLOPT_POST, false); //不能用POST
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($ch);
        curl_close($ch);
        $fp = fopen('pycredit.wsdl', 'w');//具体看你放的地址的配置
        fwrite($fp, $resp);
        fclose($fp);

        //Convert p12 to PEM with merged private key:
        //openssl pkcs12 -in supplied_cert.p12 -out php_soap_cert.pem -clcerts
        $certificate    = dirname(__FILE__) . '/full.pem';
        $password       = '123456';
        $client = new SoapClient(dirname(__FILE__) ."/pycredit.wsdl",array(
            'local_cert'    => $certificate,
            'passphrase'    => $password,
            'encoding'=>'GBK',
            'cache_wsdl' => WSDL_CACHE_NONE,
            //'trace' => 1
        ));

        $str = $queryCondition->asXML();
        $result = $client->queryReport($USER_ID, $PASSWORD, $str);
        if($debug)
        {
            echo $client->__getLastRequestHeaders();
            echo $client->__getLastRequest();
            echo $client->__getLastResponse();
        }

        $xml_wrapper = <<<XML
<?xml version="1.0" encoding="GBK"?> $result
XML;

        $result = simplexml_load_string($xml_wrapper, null, LIBXML_NOCDATA);
        if($debug) {
            print_r($result);
        }
        return $result;
    }

    static function PY_PrepareQuery($queryType, $subreportIDs, $refID, $map)
    {
        $req_xml_template = <<<XML
<?xml version="1.0" encoding="GBK"?> <conditions> </conditions>
XML;

        $dom = new DOMDocument;
        $dom->loadXML($req_xml_template);

        $sxe = simplexml_import_dom($dom);
        $sxe->addChild("condition")->addAttribute("queryType", $queryType);

        foreach($map as $key=>$value)
        {
            $item = $sxe->condition->addChild("item");
            $item->addChild("name", $key);
            $item->addChild("value", $value);
        }

        $item = $sxe->condition->addChild("item");
        $item->addChild("name", "subreportIDs");
        $item->addChild("value", $subreportIDs);
        $item = $sxe->condition->addChild("item");
        $item->addChild("name", "refID");
        $item->addChild("value", $refID);

        return $sxe;
    }

    static function PY_ParsePayload($encryped)
    {
        $zipped = base64_decode($encryped);
        $temp_zip_file_name = tempnam("/data/www/tmp", "py_");
        $handle = fopen($temp_zip_file_name, "w");
        fwrite($handle, $zipped);
        fclose($handle);

        $archive = new PclZip($temp_zip_file_name);
        $list = $archive->extract(PCLZIP_OPT_BY_NAME, "reports.xml", PCLZIP_OPT_EXTRACT_AS_STRING);
        $str = $list[0]['content'];

        $result = simplexml_load_string($str, null, LIBXML_NOCDATA);

        return $result;
    }

    static public function checkNationId($name_gbk, $id)
    {
        $requestBody = pycreditClass::PY_PrepareQuery("25160", "10602", "1", array(
            'name'=>iconv('gbk', 'UTF-8', $name_gbk),
//            'name'=>$name_gbk,
            'documentNo'=>$id));
        $result = pycreditClass::PY_SendQuery($requestBody);

        $checkResult = false;
        if($result->status == 1)
        {
            $queryResult = pycreditClass::PY_ParsePayload($result->returnValue);
            //print_r($queryResult->cisReport[0]->policeCheckInfo->item[0]->result);
            $checkResult = ($queryResult->cisReport[0]->policeCheckInfo->item[0]->result == 1);
        }
        else
        {
            $checkResult = false;
        }

        return $checkResult;
    }

}

?>

