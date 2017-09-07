<?php 
namespace common\models\moneyLog;
class MoneyLog{
	private $list=[];
	private $listIndex=[];
	public function add($model){
		$logDetail = new LogDetail();
		$logDetail=$logDetail->load($model);

		$month=strtotime(date("Y-m-01",$logDetail->create_time));
		if(isset($this->listIndex[$month])){
			$this->list[$this->listIndex[$month]]->add($logDetail);
		}else{
			$this->list[]=new LogList($month,$logDetail);
			$this->listIndex[$month] = count($this->list)-1;
		}
		return $this;
	}
	public function show(){
		return $this->list;
	}
}