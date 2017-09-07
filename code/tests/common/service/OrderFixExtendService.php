<?php 
namespace common\service;
use common\models\QfbOrderFixExtend;
class OrderFixExtendService extends BaseService{
	protected $_className = "common\models\QfbOrderFixExtend";
	public function create($params){
		$this->model = $this->newModel();
		$this->model->load(['QfbOrderFixExtend'=>$params]);
		if($this->model->validate()&&$this->model->save()){
			return true;
		}else{
			$this->messages = $this->model->getErrors();
			return false;
		}
	}
}