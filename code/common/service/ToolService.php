<?php
namespace common\service;
use Yii;
class ToolService {
	

    public static function SetSn($prefix,$length=6){
        return sprintf("%s%s%s",strtoupper($prefix),date("YmdHis"),static::RandStr($length));
    }
    public static function RandStr($length=4){
        $seed="ABCDEFGHJKLMNPQRSTUVWXYZ0123456789";
        $strlen = strlen($seed)-1;
        $tmp=[];
        for($i=0;$i<$length;$i++){
            $index = mt_rand(1,$strlen);
            array_push($tmp,$seed[$index-1]);
        }
        return implode('',$tmp);
    }
	/**
	*	银行卡掩藏中间部分数字，只保留头部4位和尾部3位
	*/
	public static function setEncrypt($string,$start=4,$end=3,$sign='*',$length=8){
		$start=mb_substr($string,0,$start,'utf-8');
		$end=mb_substr($string,-$end);
		$signStr='';
		$i=0;
		for($i;$i<$length;$i++){
			$signStr.=$sign;
		}

		return $start.$signStr.$end;
	}

    public static function setEncryptSubString($string,$start,$sign=""){
        return $sign.mb_substr($string,$start);
    }

    /**
     * 设置access_token
     * return token string
     */
    public static function setAccessToken($member_id)
    {
    	$key=Yii::$app->params['auth_key'];
    	$str=$member_id.$key.time();
        return $token=md5($str);
    }

    public static function setMin($value,$min=1){
        return $value>$min?$value:$min;
    }

    /**
    *找最近人
    */
    public static function findPerson($relations,$count = 3){
    	if($relations==null) return [];
    	$tmp=array_reverse(explode(',',$relations));
    	if(count($tmp)==0) return null;
    	$arr=[];
    	foreach ($tmp as $key => $value) {
    		if($key==0 || $value==null) continue;
    		if($key>$count) break;
    		$arr[]=$value;
    	}
		return $arr;
    }

}