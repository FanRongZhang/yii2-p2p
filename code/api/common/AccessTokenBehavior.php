<?php

namespace api\common;

use yii\filters\auth\QueryParamAuth;
use Yii;

/**
 * Token验签行为
 * Class AccessTokenBehavior
 * @package api\common
 */
class AccessTokenBehavior extends QueryParamAuth
{
    public $tokenParam = 'access_token';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $controllId = $this->owner->id;
        $actionId = $action->id;

        //无需执行登录鉴权行为的控制器
        $params = Yii::$app->params['rbac'];
        if (in_array($controllId, $params['not_validate_controller'])) {
            return true;
        }

        //无需执行登录鉴权行为的动作
        if (in_array(
            $controllId . "/" . $actionId,
            $params['not_validate_action'])
        ) {
            return true;
        }

        //返回父类执行用户鉴权的结果
        return parent::beforeAction($action);
    }

    /**
     * 授权
     */
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->get($this->tokenParam)
             ? : $request->post($this->tokenParam);
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                if ($this->owner->hasProperty('member_id')) {
                    $this->owner->member_id = $identity->id;
                }
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }
        return null;
    }
}
