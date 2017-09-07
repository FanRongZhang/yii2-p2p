<?php
namespace common\service;
use common\models\QfbSystemSettings;
use yii;


class SystemService extends BaseService
{
    protected $_className="common\models\QfbSystemSettings";

    /**
     * 获取最新一条记录
     * @return $result
     */
    public static function getLastRate()
    {
        $model = QfbSystemSettings::find();
        $model->orderBy('id desc');
        $result = $model->asArray()->one();
        return $result;
    }
    /**
     * 判断是否可以进行金额交易
     * [isCanMoney description]
     * @return boolean [description]
     */
    public static function isCanMoney(){
        $model=self::getLastRate();
        if($model==null) return false;
        return $model['status']==0?true:false;
    }
}
