<?php

namespace api\models;

use common\models\Product as BaseProduct;

class Product extends BaseProduct{

	public function fields()
	{
		return [
			'id',
			// 'create_time'=>function(){
			// 	return date('Y-m-d H:i:s',$this->create_time);
			// },
			'rate'=>function (){
				return $this->year_rate;
			},
			'date'=>function(){
				return $this->invest_day."天";
			},
			'type' => function(){
				return $this->has_money<$this->stock_money?1:2;
			},'min_money','max_money',
			'limit'=>function(){
				return bcsub($this->stock_money,$this->has_money,2);
			},
			'total_money'=>function(){
				return $this->stock_money;
			},
			'url_data'=>function(){
				return $this->product_agreement;
				//$this;
			}
		];
	}
}