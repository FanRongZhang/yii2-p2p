<?php
namespace api\versions\v200\controllers;

use common\service\VersionService;
use yii\data\ActiveDataProvider;
use api\common\BaseController;
use yii;
use common\models\QfbMember;
use api\common\helpers\ReseponseCode as Code;
use common\models\QfbAbout;
use common\models\QfbDayOff;
/**
 * @author wang
 * @since 2.0
 */
class VersionController extends BaseController
{
    /**
    *   版本更新[不登录]
    */
    public function actionNew(){
        
        $post=$this->getParams();       
        $service=new VersionService();
        $type=$post['channelId'];
        $model=$service->getLast($type);
        $model['is_force']= (bool)$model['is_force'];
        $ver=isset($post['ver']) && $post['ver'] ?$post['ver']:$post['ver_code'];
        $ver= str_replace('.', '', $ver);
        $bool=$model['ver_code'] <= intval($ver);
        $data = [
            'id' => (int) $model->id,
            'ver_code' => (int) $model['ver_code'],
            'ver_name' => (string) $model['ver_name'],
            'create_time' => (string) date("Y-m-d",$model['create_time']),
            'content' => (string) $model['content'],
            'type' => (int) $model['type'],
            'url' => $type == 1 ? (string) (\Yii::$app->fileStorage->baseUrl.'/'.$model['url']) : (string) $model['url'],
            'force' => (bool) $model['is_force']
        ];
        if($bool) {
            return [
                    'code' => Code::HTTP_OK,
                    'msg' => 'last version'
               ];
        }else{
            return [
                    'code' => Code::HTTP_OK,
                    'msg' => 'new version',
                    'data' => $data
               ];
        }
    }

    /**
    *   关于钱富宝
    *   @author wsf
    */
    public function actionAbout(){  

        $data = array();
        $query = QfbAbout::find();
        $result = $query->select(['mobile','weixin','qq','remark','wx_pic'])->one();

        if (!empty($result)) {

            $data['mobile'] = $result->mobile;
            $data['qq'] = $result->qq;
            $data['time'] = $result->remark;
            $data['wechat'] = $result->weixin;
            $data['wechat_pic'] = $result->wx_pic;
            $data['status'] = (bool) true; 
            $data['tips'] = "只在工作日才能联系客服哦";
        }

        $day_off = array();
        //查询节假日表
        $time = QfbDayOff::find()->select('time')->asArray()->all();
        foreach ($time as $k=>$t) {
            $day_off[$k] = $t['time'];
        }
        //今天
        $today_day = strtotime(date("Y-m-d"));
        //判断是否在假期
        if (in_array('$today_day', $day_off)) {
            $data['status'] = (bool) false; 
        }
        return [
                    'code' => Code::HTTP_OK,
                    'msg' => Code::$statusTexts[Code::HTTP_OK],
                    'data' => $data
               ];
             
    }
}
