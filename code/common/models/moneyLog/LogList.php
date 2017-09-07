<?php 
namespace common\models\moneyLog;
class LogList{
	public $month;
	public $data=[];
	public function __construct($month,LogDetail $model){
		$this->month = $month;
		$this->add($model);
	}
	public function add(LogDetail $model){
		$this->data[]=$model;
	}
}