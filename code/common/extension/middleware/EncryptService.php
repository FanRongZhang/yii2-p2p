<?php

namespace common\extension\middleware;

/**
 * 加密类
 * @author xiaomalover <xiaomalover@gmail.com>
 */


class EncryptService{

    /**
     * 大明首创双md5加密 (^_^)
     * 应用场景，用户注册，修改密码
     * @param  String $password 未加密的字符串
     * @return String 加密后的字符串
     */
    public static function twiceMd5($password)
    {
        return md5(md5($password) . "q!wse#r4t%yhu&i8o(p;");
    }


    /**
     * 校验密码
     * @param  String $noEncrypt 未加密的串
     * @param  String $encrypted 加密后的串
     * @return Boolean $result 验证是否通过的标识
     */
    public static function twiceMd5Verify($noEncrypt, $encrypted)
    {
        return md5(md5($noEncrypt) . "q!wse#r4t%yhu&i8o(p;") == $encrypted;
    }
}