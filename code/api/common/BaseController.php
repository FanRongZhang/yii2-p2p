<?php

namespace api\common;

use Yii;
use yii\rest\Controller;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use api\common\SafeBehavior;
use api\common\AccessTokenBehavior;
use api\common\PrepareParamsBehavior;

/**
 * 普通api控制器基类
 * @author xiongli
 * @since 2.0
 */
class BaseController extends Controller
{
    /**
     * 关闭POST表单验证
     */
    public $enableCsrfValidation = false;

    /**
     * 当前用户id
     */
    public $member_id;

    /**
     * 请求参数
     */
    public $queryParams;

    public function getParams($name = null)
    {
        return is_null($name) ? $this->queryParams : $this->queryParams[$name];
    }

    /**
     * 封装行为
     * @return Array 所有行为的数组
     */
    public function behaviors()
    {
        //继承父类所有行为
        $behaviors = parent::behaviors();

        //安全验签行为
        $safe = [
            'class' => SafeBehavior::className(),
        ];

        //用户鉴权行为(access-token的验证)
        $behaviors['authenticator'] = [
            'class' => AccessTokenBehavior::className(),
        ];

        //内容格式协商行为
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                // 'application/xml' => Response::FORMAT_XML,
            ],
        ];

        //安全验签要放在最前面--生产环境是打开
        // array_unshift($behaviors, $safe);

        //准备参数的行为
        $behaviors['prepare'] = [
            'class' => PrepareParamsBehavior::className(),
        ];

        return $behaviors;
    }

    /**
     * 设置流水号
     */
    public function getBindSn($type='')
    {
        //生成随机字母+数字
        $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";
        $len = strlen($str);
        for ($i = 0; $i < 6; $i++) {
            $code .= $str{rand(0, $len - 1)};
        }
        return $type . date('YmdHis') . $code;
    }

    
}
