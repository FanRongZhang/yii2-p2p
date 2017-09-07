<?php
namespace common\service;
use yii;
use api\common\helpers\ReseponseCode as Code;
class ApiService
{
	public static function send($code,$message=null,$data=null){
		if($data===null){
			return new ResponeMessage($code,$message);
		}else{
			return new ResponeData($code,$data,$message);
		}
	}

	public static function success($code=200,$message=null,$data=null){

		$message=self::setMessage($message);
		$array=['code'=>intval($code),'msg'=>$message];
		if($data!==null)$array['data']=$data;
		return $array;
	}
	public static function error($code,$message=null,$data=null){

		$message=self::setMessage($message);
		$array=['code'=>intval($code),'msg'=>$message];
		if($data!==null)$array['data']=$data;
		return $array;
	}
	protected static function setMessage($message=null){
		if($message==null) return 'no message';
		if(is_string($message)){
			return $message;
		}
		if(is_array($message) || is_object($message) ){
			foreach ($message as $key => $value) {
				return is_array($value)?$value['0']:$value;
			}
		}
		return 'no message';
	}
}
/**
 * 
 */
class ResponeMessage{
	public $code;
	public $msg;
	public function __construct($code,$message=null){
		$this->code = $code;
		$msg=Code::$statusTexts[$this->code];
		if(empty($msg))
			throw new \Exception("code 不存在");
		$this->msg=$message?$message:$msg;
	}
}

class ResponeData extends ResponeMessage{
	public function __construct($code,$data,$message=null){
		parent::__construct($code,$message=null);
		$this->data = $data;
	}
	public $data;
}
