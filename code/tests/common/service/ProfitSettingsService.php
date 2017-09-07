<?php 
namespace common\service;
use common\models\QfbProfitSettings;
class ProfitSettingsService extends BaseService{
	protected $_className="common\models\QfbProfitSettings";
	public function findModelByProduct($product_id){
		$model = $this->newModel();
		return $this->model=$model::find()->where(['=','product_id',$product_id])->one();
	}
}