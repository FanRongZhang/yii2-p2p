<?php
namespace common\extension\hrpay\lib;

/**
 * curl模拟提交
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class Curl
{
    /**
     * 模拟post请求
     * @param String $url 请求地址
     * @param Array $data 请求参数
     */
    public static function curlPost($url, $data)
    {
        $data = http_build_query($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
