<?php

namespace api\common;

use yii\base\Behavior;
use Yii;
use yii\base\Controller;
use api\common\helpers\ReseponseCode as Code;

/**
 * 用户安全验签行为
 * 用于登录，还有设置密码
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class UserSafeBehavior extends Behavior
{

    /**
     * 要验签动作数组
     */
    public $actions = [];

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
     * 此函数处理验签逻辑
     * @author xiaomalover <xiaomalover@gmail.com>
     */
    public function beforeAction($event)
    {
        //只验签部分方法
        $action = $event->action->id;
        if (!in_array($action, $this->actions)) {
            return true;
        }

        //获取到请求对象，在后面获取参数使用
        $request = Yii::$app->request;
        $queryParams = $request->isGet ? $request->get() : $request->post();

        if ((isset($queryParams['repwd_sign']) || isset($queryParams['login_sign'])
             || isset($queryParams['register_sign']))
            && (isset($queryParams['login_random']) || isset($queryParams['repwd_random'])
             || isset($queryParams['register_random']))
            ) {
            if (isset($queryParams['repwd_sign'])) {
                $sign = $queryParams['repwd_sign'];
                $random = $queryParams['repwd_random'];
            } else if (isset($queryParams['login_sign'])) {
                $sign = $queryParams['login_sign'];
                $random = $queryParams['login_random'];
            } else {
                $sign = $queryParams['register_sign'];
                $random = $queryParams['register_random'];
            }
            $mobile = $queryParams['mobile'];
            $password = $queryParams['password'];
            //随机数模3获得验签类型
            $verify_type = $random % 3;
            switch ($verify_type) {
                case 0:
                    $verify_val = md5(substr($mobile, 0, floor(strlen($mobile) / 2)) . $password . substr($mobile, floor(strlen($mobile) / 2)) . strrev($mobile . $random));
                    if ($sign != $verify_val)
                        exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                , 'msg' => '信息验签不通过'], JSON_UNESCAPED_UNICODE));
                    break;

                case 1:
                    $verify_val = md5(substr($mobile, 0, floor(strlen($mobile) / 2)) - substr($random, 0, floor(strlen($random) / 2)) . strtoupper($password));
                    if ($sign != $verify_val)
                        exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                , 'msg' => '信息验签不通过'], JSON_UNESCAPED_UNICODE));
                    break;
                case 2:
                    $verify_val = md5(strtoupper(substr(strrev($password), 0, floor(strlen($password) / 2))) . substr($mobile, 0, floor(strlen($mobile) / 2)) . ($random << ($random % 2)));
                    if ($sign != $verify_val)
                        exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                , 'msg' => '信息验签不通过'], JSON_UNESCAPED_UNICODE));
                    break;
                default:
                    exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                , 'msg' => '信息验签不通过'], JSON_UNESCAPED_UNICODE));
            }
        } else {
            exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                , 'msg' => '信息验签不通过'], JSON_UNESCAPED_UNICODE));
        }
    }
}
