<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/3
 * Time: 15:10
 */
namespace common\service;
use Yii;
use common\models\QfbLoginRecord;

class LoginRecordService
{


    protected $tableName;
    protected $model;
    public function __construct($member_id){
        $this->model = new QfbLoginRecord($member_id);
    }

    /**
     * 保存用户登录数据
     * @param $member_id
     * @param $params
     * @param $ip
     * @return bool
     */
    public function saveRecord($member_id,$params,$ip){
        $model = $this->model;
        $model->member_id = $member_id;
        $model->create_time = time();
        $model->flag = $params['flag'];
        $model->type = $params['type'];
        $model->ip = $ip;
        if($model->save()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 用户一天登录失败的次数
     * @param $member_id
     * @return int|string
     */
    public function dayFailLogin($member_id){
        $start_time = strtotime(date("Y-m-d",time()));
        $end_time = bcadd($start_time,86400);
        $model = $this->model;
        return $model->find()->select('member_id')->where(['=','member_id',$member_id])->andWhere(['=','flag',0])->andWhere(['>','create_time',$start_time])->andWhere(['<','create_time',$end_time])->count();
    }

    /**
     * 用户总共登录失败次数
     * @param $member_id
     * @return int|string
     */
    public function countFailLogin($member_id){
        $model = $this->model;
        return $model->find()->select('member_id')->where(['=','member_id',$member_id])->andWhere(['=','flag',0])->count();
    }
    /**
     * 删除用户密码输入错误的记录
     * @param $member_id
     * @return bool
     */
    public function deleteSuccessRecord($member_id){
        $model = $this->model;
        $result = $model::updateAll(['flag'=>1],"member_id=:member_id",[':member_id'=>$member_id]);
        if($result){
            return true;
        }else{
            return false;
        }
    }

}