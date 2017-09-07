<?php
namespace common\enum;
class MemberEnum{
    const QFB= 1;
    const SHARE= 2;
    const PC = 3;
    const  PHONE = 4;
    const  MALL= 5;

    public static function getName($index=null){
        $arr= [
            ""=>"全部",
            self::QFB => '钱富宝',
            self::SHARE => '分享注册',
            self::PC => 'PC官网',
            self::PHONE => '手机官网',
            self::MALL => '中盾商城',
        ];
        return ($index === null)?$arr:$arr[$index];
    }

}