<?php

namespace api\common;

use yii\base\Behavior;
use Yii;
use yii\base\Controller;

/**
 * 为控制器准备参数的行为
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class PrepareParamsBehavior extends Behavior
{
    /**
     * 绑定beforeAction事件
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction'
        ];
    }

    /**
     * beforeAction事件处理函数
     * @author xiaomalover <xiaomalover@gmail.com>
     */
    public function beforeAction($action)
    {
        $request = Yii::$app->request;
        $params = $request->isPost ? $request->post() : $request->get();
        $this->owner->queryParams = $_GET = $_POST = $params;
    }
}
