<?php 
namespace common\service;

use common\models\QfbIndex;
class IndexService extends BaseService{
    
    /**
     *
     */
    public static function getAll(){
        
        $query  = QfbIndex::find();
        return $query->asArray()->all();
    }
    
}