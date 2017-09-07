<?php

namespace common\service;

use common\extension\middleware\EncryptService;
use common\models\UMember;
use common\models\QfbMember;

/**
 * 密码相关服务类
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class PasswordService extends BaseService
{
    /**
     * 登录密码
     */
    const LOGIN_PASSWORD = 1;

    /**
     * 支付密码
     */
    const PAY_PASSWORD = 1;

    /**
     * 密码验证服务
     * @param  Int $member_id 用户id
     * @param  String $password 支付密码
     * @param  Int $type 类型（登录密码为1，支付密码2）
     * @return Boolean $res 验证结果
     */
    public static function checkPassword($member_id, $password, $type)
    {
        //如果密码未经过加密加密
        if (!empty($password) && strlen($password) != 32) {
            $password = EncryptService::twiceMd5($password);
        }

        if ($type == self::PAY_PASSWORD) {
            $member = QfbMember::findOne($member_id);
            if ($member && $member->zf_pwd == $password) {
                return true;
            }
        }

        return false;
    }
}
