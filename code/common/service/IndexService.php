<?php 
namespace common\service;

use common\models\QfbIndex;
class IndexService extends BaseService{
    
    /**
     *
     */
    public static function getAll(){

        $query  = QfbIndex::find()->where('id not in (5,9,4)');
        return $query->asArray()->all();
    }
    
}