<?php 
namespace common\service;
use yii;
use common\models\QfbMemberInfo;
use common\models\QfbImei;

class ImeiService extends BaseService
{
    /**
     *
     *通过imei获取一条数据
     *
     */
    public static function getImeiCount($imei){
        return QfbImei::find()->where(['imei'=>$imei])->sum('imei_count');
    }
    
    /**
     *
     *获取对应用户的所有认证失败次数
     *
     */
    public static function getMemberCount($member_id){
        return QfbImei::find()->where(['member_id'=>$member_id])->sum('member_count');
    }
    /**
     *
     *设置设备记录，无就新增，有则更新
     *
     */
    public static function setImei($member_id,$imei){
        $imeiModel = QfbImei::find()->where(['member_id'=>$member_id,'imei'=>$imei])->one();
        if (empty($imeiModel)){
            $model = new QfbImei();
            $model->member_id = $member_id;
            $model->imei = $imei;
            $model->imei_count = 1;
            $model->member_count = 1;
            $model->edit_time = time();
            if ($model->save()){
                return true;
            }else {
                return false;
                //return $model->errors;
            }
        } else{
            $imeiModel->imei_count = $imeiModel->imei_count + 1;
            $imeiModel->member_count = $imeiModel->member_count + 1;
            $imeiModel->edit_time = time();
            if ($imeiModel->save()){
                return true;
            }else {
                return false;
                //return $imeiModel->errors;
            }
        }
        return false;
    }
    
	
}