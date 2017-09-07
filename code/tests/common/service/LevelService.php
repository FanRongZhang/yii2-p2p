<?php 
namespace common\service;
use yii;
use common\models\QfbLevel;

class LevelService extends BaseService{
	
	/**
	 * @return array
	 *
	 * @author jin
	 */
	public static function getMemberLevels( ){
	    $query = QfbLevel::find();
	    $query->select(['id','name']);
	
	    $result = $query->orderBy('sort')->asArray()->all();
	    return $result;
	}
	
}