<?php

namespace api\common;

use Yii;
use yii\base\Behavior;
use yii\web\Application;
use api\common\helpers\ReseponseCode;
use common\models\QfbSystemMaintenance as Maintenance;

/**
 * 关闭网站行为
 * xiaomalover <xiaomalover@gmail.com>
 */
class CloseBehavior extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            Application::EVENT_BEFORE_REQUEST => 'beforeRequest'
        ];
    }

    /**
     * 响应前返回关闭
     */
    public function beforeRequest()
    {
        $maintenance = Maintenance::find()->one();
        if ($maintenance && $maintenance->is_maintenance) {
            header("Content-type: application/json; charset=UTF-8");
            die(json_encode(
                [
                    'code'=>ReseponseCode::SYSTEM_MAINTENANCE,
                    'msg'=> $maintenance->msg,
                ], JSON_UNESCAPED_UNICODE
            ));
        }
    }
}
