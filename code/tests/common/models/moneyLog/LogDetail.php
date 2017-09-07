<?php 
namespace common\models\moneyLog;
class LogDetail{
	public $create_time;
	public $money;
	public $type;
	public $remark;
	public function load($model){
		$this->create_time = $model->create_time;
		$this->money = $model->money;
		$this->type = $model->type;
		$this->remark = $model->remark;
		return $this;
	}
}