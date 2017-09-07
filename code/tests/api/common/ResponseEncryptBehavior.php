<?php

namespace api\common;

use common\service\AesService;
use YII;
use yii\base\Behavior;
use yii\web\Application;

/**
 * 响应数据加密行为
 * xiaomalover <xiaomalover@gmail.com>
 */
class ResponseEncryptBehavior extends Behavior
{
    public $actions = [];

    /**
     * @return array
     */
    public function events()
    {
        return [
            Application::EVENT_AFTER_REQUEST => 'afterRequest',
        ];
    }

    /**
     * 响应前返回加密数据
     */
    public function afterRequest($event)
    {
        //获取当前请求url
        $requestRoute = $event->sender->requestedRoute;
        //去除版本信息
        $url = str_replace("v200/", "", $requestRoute);
        //如果在加密名单，才执行加密
        if (in_array($url, $this->actions)) {
            $json = json_encode($event->sender->response->data
                , JSON_UNESCAPED_UNICODE);
            $desServ = new AesService(Yii::$app->params['default_aes_key'],
                Yii::$app->params['default_aes_iv']);
            $res = $desServ->encrypt($json);
            echo $res;exit;
        } else {
            return true;
        }
    }
}
