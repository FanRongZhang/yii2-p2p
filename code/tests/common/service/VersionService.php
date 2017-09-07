<?php
namespace common\service;
use yii;
use common\models\QfbVersion;


/**
 * 版本业务逻辑
 * @author wang
 *
 */
class VersionService extends BaseService
{
    /**
    *   获取版本信息
    */
    public function getLast($type){
        
        $query = QfbVersion::find();
        $query->select(['id','ver_code','ver_name','create_time','content','type','url','is_force']);
        $query->where(['=','type',$type]);
        $query->orderBy('ver_code desc');
        $model = $query->one();

        return $model;
    }

}
