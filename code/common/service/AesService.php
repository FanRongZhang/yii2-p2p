<?php
namespace common\service;
use Yii;
class AesService extends  BaseService
{

    private $iv = 'fedcba9876543210';
    private $key = '0123456789abcdef';


    function __construct($key,$iv)
    {
        $this->key = $key;
        $this->iv= $iv;
    }

    function encrypt($str) {

        //$key = $this->hex2bin($key);
        $iv = $this->iv;

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);
        $str = $this->addPKCS7Padding(trim($str));
        mcrypt_generic_init($td, $this->key, $iv);
        $encrypted = mcrypt_generic($td, $str);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return base64_encode($encrypted);
    }

    function decrypt($code) {
        //$key = $this->hex2bin($key);
        $code = base64_decode($code);
        $iv = $this->iv;

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

        mcrypt_generic_init($td, $this->key, $iv);
        $decrypted = mdecrypt_generic($td, $code);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return utf8_encode(trim($decrypted));
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    function addPKCS7Padding($source){
        $source = trim($source);
        $block = mcrypt_get_block_size('rijndael-128', 'cbc');
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }
    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    function stripPKSC7Padding($source){
        $source = trim($source);
        $char = substr($source, -1);
        $num = ord($char);
        if($num==62)return $source;
        $source = substr($source,0,-$num);
        return $source;
    }

}