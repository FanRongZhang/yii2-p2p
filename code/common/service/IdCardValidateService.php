<?php
namespace common\service;
use Yii;


/**
 * 实名认证
 * @author jin
 *
 */
class IdCardValidateService extends BaseService
{
    public function PY_SendQuery($queryCondition)
    {
        //configuration
        $USER_ID = \Yii::$app->params['idcard_userid'];
	    $PASSWORD = \Yii::$app->params['idcard_password'];
        $ENDPOINT = \Yii::$app->params['idcard_endpoint'];
    
        $url_wsdl = $ENDPOINT . '/services/WebServiceSingleQueryOfUnzip?wsdl';
        $client = new \SoapClient($url_wsdl, array(
            'encoding'=>'GBK',
            //'trace' => 1
        ));
    
        $result = $client->queryReport($USER_ID, $PASSWORD, $queryCondition->asXML());
    
        $xml_wrapper = <<<XML
<?xml version="1.0" encoding="GBK"?> $result
XML;
    
        $result = simplexml_load_string($xml_wrapper, null, LIBXML_NOCDATA);
        return $result;
    }
    
    public function PY_PrepareQuery($queryType, $subreportIDs, $refID, $map)
    {
        $req_xml_template = <<<XML
<?xml version="1.0" encoding="GBK"?> <conditions> </conditions>
XML;
    
        $dom = new \DOMDocument;
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
    
    /***
     * return obj
     * <name>姓名</name>
     * <documentNo>证件号码</documentNo>
     * <result>认证结果ID,1:姓名和公民身份号码一致。2：公民身份号码一致，姓名不一致。3：库中无此号，请到户籍所在地进行核实。</result>
     * <photo>此节点的值暂时返回空。被查询人办理身份证时的照片,jpg格式并经过Base64编码。</photo>
     * 
     * @author jin
     */
    public function doValidate($name='',$idcard='')
    {
        $requestBody = self::PY_PrepareQuery("25160", "10602", "1", array(
            'name'=>$name,
            'documentNo'=>$idcard));
        $result = self::PY_SendQuery($requestBody);
        if($result->status == 1)
        {
            $queryResult = simplexml_load_string(
                preg_replace('/GBK/', 'utf-8', $result->returnValue, 1));
            
            $data['code'] = $queryResult->cisReport->policeCheckInfo->item->result;
            $data['message'] = '操作成功';
        }else{
            $data['code'] = $result->errorCode;
            $data['message'] = $result->errorMessage;
        }
        return $data;
    }
}