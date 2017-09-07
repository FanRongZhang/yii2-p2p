<?php 
namespace common\enum;
class VouchersEnum{
    
    //0=>'固定规则发放',1=>'活动发放'
	const RULE_FIX = 0;
	const RULE_LIVE = 1;
	public static function getRule($index=null){
	    $arr= [
	        self::RULE_FIX => '固定规则发放',
	        self::RULE_LIVE => '活动发放',
	    ];
	    return ($index === null)?$arr:$arr[$index];
	}
	
	//0=>'否',1=>'是'
	const NO = 0;
	const YES = 1;
	public static function getStatus($index=null){
	    $arr= [
	        self::NO => '否',
	        self::YES => '是',
	    ];
	    return ($index === null)?$arr:$arr[$index];
	}
	
	
}