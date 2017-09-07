<?php 
namespace api\models;
use yii;
use yii\helpers\Url;
class ProductDetail{
	public $type;
	public $title;
	public $time;

	public $record;
	public $money="0";
	//public $profit;
	public $status;
	public $rate;
	public $date;
	public $min_money;
	public $max_money;
	public $limit;
	public $total_money;
	public $url;//
	public $url_data;
	public $buy;
	public $buy_tips='';

	public $profit="0";

	private $_model;
	private $_orderService;
	public function __construct($model,$orderService){
		$this->_model = $model;
		$this->_orderService=$orderService;
	}
	public function run(){
		$this->defalut();
		$this->checkBuy();
		$this->getRecord();
		$this->getAgreement();
		return $this;
	}

	public function getAgreement(){
		$tmp = [];
		if($this->_model->product_agreement){
			foreach ($this->_model->product_agreement as  $_model) {
				$t = [];
				$t['content_url']=Url::to(['/agreement', 'id'=>$_model['id']], true);
				$t['title']=$_model['title'];
				$t['pic_url']=Yii::$app->params['img_domain'].'/'.$_model['pic_url'];
				array_push($tmp,$t);
			}
		}
		$this->url_data = $tmp;
	}
	function getMemberID(){
        // return Yii::$app->user->identity->id?Yii::$app->user->identity->id:0;
    }
	private function getRecord(){
		if($this->getMemberID()){
			$model = $this->_orderService->findPriceAndProfit($this->_model->id);
			if($model['pay_money']){
				$this->record=true;
				$this->money=$model->pay_money."";
				$this->profit=$model->profit_money."";
				return true;
			}
		}
		$this->record=false;
		$this->money="0";
	}
	private function checkBuy(){
		if($this->getMemberID()&&$this->isNewer()){
			if($this->_orderService->isBuy()){
				$this->notCan('新手才能购买哦');
				$this->getStatus();
				return false;
			}
		}
		if($this->getStatus()){
			$this->can();
		}else{
			$this->notCan('已到期');
			return false;
		}
	}

	private function isNewer(){
		return $this->_model->is_newer;
	}

	private function notCan($message){
		$this->buy=false;
		$this->buy_tips=$this->buy_tips?$this->buy_tips:$message;
	}

	private function can(){
		$this->buy=true;
	}

	private function getStatus(){
		if($this->_model->end_time< time()){
			$this->time=0;
			$this->status='已满额';
			$this->type=2;
			return false;
		}else if($this->_model->has_money >= $this->_model->stock_money){
			$this->buy_tips='已满额';
			$this->type=2;
			$this->time=0;
			$this->status="已满额";
			return false;
		}else if($this->_model->status ==2){
			$this->type=2;
			$this->time=0;
			$this->status="已满额";
			return false;
		}else{
			$this->type=1;
			$this->status="抢购中";
			$this->time=($this->_model->end_time-time())*1000;
			return true;
		}
	}

	private function defalut(){
		//购买状态(百分比)
		$buy_status = $this->_model->has_money * 100 / $this->_model->stock_money;

		$this->title = $this->_model->product_name;
		$this->url = $this->_model->product_detail
			? Url::to(['/product', 'id'=>$this->_model['id']], true) : '';
		$this->rate=$this->_model->year_rate;
		$this->date=$this->_model->invest_day.'天';
		$this->min_money=$this->_model->min_money;
		$this->max_money=$this->_model->max_money;
		$this->limit=$this->_model->stock_money-$this->_model->has_money."";
		$this->total_money=$this->_model->stock_money;
		$this->total_money=$this->_model->stock_money;
		$this->buy_status=($buy_status > 99 && $buy_status < 100) ? floor($buy_status): ceil($buy_status);
		$this->is_newer=$this->_model->is_newer;
		//$this->url_data = $this->_model->product_agreement;
	}
}