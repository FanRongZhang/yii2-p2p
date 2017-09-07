<?php

namespace api\common;

use yii\base\Behavior;
use Yii;
use yii\base\Controller;
use api\common\helpers\ReseponseCode as Code;

/**
 * 安全验签行为
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class SafeBehavior extends Behavior
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
     * 此函数处理验签逻辑
     * @author xiaomalover <xiaomalover@gmail.com>
     */
    public function beforeAction()
    {
        //截取控制器和方法名称
        $controllerId = $this->owner->id;
        $actionId = $this->owner->action->id;
        $urlId = $controllerId . "/" .$actionId;
        //在白名单中的方法直接跳过验签
        if (in_array($urlId, Yii::$app->params['not_decrypt_action'])) {
            return true;
        }

        $request = Yii::$app->request;
        $params = $request->isGet ? $request->get() : $request->post();
        if (count($params)) {
            if (isset($params['qfb_data'])) {
                //接收明文json串，转换成参数存入Request对象(测试用)
                // Yii::$app->request->setBodyParams(json_decode($params['qfb_data'], true));
                //解密密文, 转换成参数存入Request对象中(正式用)
                $this->decryptForBodyParams($urlId);
            } else {
                //上传头像是不加密
                if ($urlId != 'member/avatar') {
                    exit(json_encode([
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '错误的请求'],
                        JSON_UNESCAPED_UNICODE)
                    );
                }
            }
        }

        //获取到请求对象，在后面获取参数使用
        $request = Yii::$app->request;

        //如果验签开启，则执行验签逻辑
        if (Yii::$app->params['is_sign']) {
            //根据请求方法，获取参数
            $queryParams = $request->isGet ? $request->get() : $request->post();

            //只有同时传了timeStamp和sgin才进入验签，否则直接不通过
            if (isset($queryParams['timeStamp']) && isset($queryParams['sign'])) {

                $timeStamp = $queryParams['timeStamp']; //前端时间戳，登录与非登录都会传
                $sign = $queryParams['sign']; //验签参数，登录与非登录都会传

                if (isset($queryParams['access_token'])
                    && !empty($queryParams['access_token']) ) { //登录用户验签
                    $access_token = $queryParams['access_token']; //用户token
                    $des3_key = Yii::$app->params['encrypt_key']; //密钥
                    //根据前端所传时间戳，从末位往前数8位进行截取，
                    //所得结果转为int数据%2（2:代表当前支持的加密种类，后续新增，此值递增）
                    //取余及为加密类型
                    $verify_type = substr($timeStamp, strlen($timeStamp) - 8) % 2;

                    //根据加密类型，验签
                    if ($verify_type == 0) {
                        $verify_val = md5(strrev($access_token) . strrev($des3_key) . $timeStamp);
                        if ($sign != $verify_val)
                            exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
                    } else if ($verify_type == 1) {
                        $token_front = substr($access_token, 0, floor(strlen($access_token)/2));
                        $token_back = substr($access_token, floor(strlen($access_token)/2));
                        $verify_val = md5($token_front . $des3_key
                            . $token_back . $timeStamp);
                        if ($sign != $verify_val)
                            exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
                    }
                } else {  //非登录用户验签
                    if (isset($queryParams['imei'])) {
                        //前端传过来时间戳的后8位%2得到非登录用户的加密类型
                        $verify_type = substr($timeStamp, strlen($timeStamp) - 8) % 2;
                        $imei = $queryParams['imei'];
                        if ($verify_type == 0) {
                            $verify_val = md5(strrev($imei) . $timeStamp);
                            if ($verify_val != $sign)
                                exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                    , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
                        } else if ($verify_type == 1) {
                            $time_front = substr($timeStamp, 0, floor(strlen($timeStamp)/2));
                            $time_back = substr($timeStamp, floor(strlen($timeStamp)/2));
                            $verify_val = md5($time_front . $imei . $time_back);
                            if ($verify_val != $sign)
                                exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                                    , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
                        }
                    } else {
                        exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                            , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
                    }
                }

            } else {
                exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                    , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * 解密post密文数据
     * 同时赋值给request对象
     * @param $urlId 当前控制器和动作id (e.g member/login)
     * @author xiaomalover <xiaomalover@gmail.com>
     */
    public function decryptForBodyParams($urlId)
    {
        $request = Yii::$app->request;
        $queryParams = $request->isGet ? $request->get() : $request->post();
        if ($request->isPost) {
            if (isset($queryParams['qfb_data'])) {
                //非登录动作数组
                $no_login_actions = ['user/login', 'user/register'
                    , 'version/new', 'user/reset-password', 'message/content'
                    , 'bank/support', 'message/tips'];
                $is_default_aes = in_array($urlId, $no_login_actions);
                if ($is_default_aes) { //非登录动作用默认密钥和加密向量
                    $default_aes_key = Yii::$app->params['default_aes_key'];
                    $default_aes_iv = Yii::$app->params['default_aes_iv'];
                } else { //登录接口，用非默认密钥和加密向量
                    $default_aes_key = Yii::$app->params['encrypt_key'];
                    $default_aes_iv = Yii::$app->params['encrypt_iv'];
                }
                //解密
                $mcryStr = $this->aes_mcrypt_decrypt($default_aes_key , $queryParams['qfb_data'], $default_aes_iv);
                /* $aesServ= new  \common\service\AesService($default_aes_key,$default_aes_iv);
                 $mcryStr = $aesServ->decrypt($queryParams['qfb_data']);*/
                //截取json字符串
                $mcryStr = substr($mcryStr, 0, strrpos($mcryStr, "}") + 1);
                //decode json为数组赋给请求类参数
                $arr = json_decode($mcryStr, true);
                if (is_array($arr)) {
                    $_GET = array_merge($_GET, $arr);
                    $_POST = array_merge($_POST, $arr);
                    Yii::$app->request->setBodyParams($arr);
                } else {
                    exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                        , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
                }
            } else {
                exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                    , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
            }
        } else {
            if (isset($queryParams['qfb_data'])) {
                //GET方式使用默认密钥和加密向量
                $default_aes_key = Yii::$app->params['default_aes_key'];
                $default_aes_iv = Yii::$app->params['default_aes_iv'];

                //解密
                $mcryStr = $this->aes_mcrypt_decrypt($default_aes_key , $queryParams['qfb_data'], $default_aes_iv);
                parse_str($mcryStr, $arr);
                if (is_array($arr)) {
                    $_GET = $arr;
                    Yii::$app->request->setQueryParams($arr);
                } else {
                    exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                        , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
                }
            } else {
                exit(json_encode(['code' => Code::COMMON_ERROR_CODE
                    , 'msg' => '安全验签不通过'], JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * @param $key 解密key
     * @param $encryptStr 解密字符串
     * @param $iv  解密iv
     * @return string 返回解析后的字符串
     * @autor luowenjian
     */
    public function aes_mcrypt_decrypt($key, $encryptStr, $val)
    {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $decrypted= @mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key
            , base64_decode($encryptStr), MCRYPT_MODE_CBC, $val);
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s-1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }
}
