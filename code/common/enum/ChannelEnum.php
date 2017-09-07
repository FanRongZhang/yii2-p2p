<?php

namespace common\enum;

/**
 * 支付渠道枚举类
 * @author xiaoma <xiaomalover@gmail.com>
 * @since 2.0
 */
class ChannelEnum
{
    /**
     * 易联支付通道
     */
    const YILIAN = 1;

    /**
     * 支付宝通道
     */
    const ALI = 2;

    /**
     * 零钱支付
     */
    const MONEY = 3;

    /**
     * 京东支付通道
     */
    const JD = 4;

    /**
     * 快钱支付通道
     */
    const KUAIQIAN = 5;

    /**
     * 证联支付通道
     */
    const ZHENGLIAN = 6;

    /**
     * 华融支付通道
     */
    const HUARONG = 7;

    /**
     * 海口银行通道
     */
    const HKYH = 8;

    /**
     * 充值
     */
    const RECHARGE = 1;

    /**
     * 提现
     */
    const WITHDRAW =2;

    /**
     * 购买活期
     */
    const BUY_CURRENT = 3;

    /**
     * 购买定期
     */
    const BUY_FIXED = 4;

    public static function getChannelType(){

        $data =[
            // 易联
            1=>'yilian',       
            // 块钱
            5=>'kuaiqian',
            // 证联
            6=>'zhenglian',
            // 华融
            7=>'huarong',
            // 海口银行
            8=>'hkyh',
        ];
        return $data;

        return is_null($type)?$data:$data[$type];
    }


    //支付渠道
    public static function getChannelList($type){
        $data =[
            0=>'银嘉',
            1=>'易联',
            2=>'支付宝',
            3=>'零钱',
            4=>'京东',
            5=>'快钱',
            6=>'证联',
            7=>'华融'
        ];
        return is_null($type)?$data:$data[$type];
    }

}