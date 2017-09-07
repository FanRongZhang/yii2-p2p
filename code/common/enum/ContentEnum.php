<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/17
 * Time: 19:47
 */
namespace common\enum;
class ContentEnum{

    //广告状态 0=>'未发布',1=>'发布'
    const BANNER_UN_ACTIVE = 0;
    const BANNER_ACTIVE = 1;
    public static function getActive($data=null)
    {
        $arr= [
            self::BANNER_UN_ACTIVE => '未发布',
            self::BANNER_ACTIVE => '发布',
        ];
        return ($data === null)?$arr:$arr[$data];
    }

    const BANNER_NO_JUMP = 0;
    const BANNER_WITH_PRO = 1;
    const BANNER_STORE_ADVERTISE = 2;
    const BANNER_URL_TOKEN = 3;
    const BANNER_PHONE_RECHARGE = 4;
    const BANNER_REGULAR_BASIS = 5;
    public static function getBannerStatus($data=null)
    {
        $arr= [
            self::BANNER_NO_JUMP => '无跳转',
            self::BANNER_WITH_PRO => '钱富宝原生',
            self::BANNER_STORE_ADVERTISE => '商城广告',
            self::BANNER_URL_TOKEN => 'URL无token',
            self::BANNER_PHONE_RECHARGE => '手机充值',
            self::BANNER_REGULAR_BASIS => '定期理财',
        ];
        return ($data === null)?$arr:$arr[$data];
    }

    const BANNER_SHARE_NO = 0;
    const BANNER_SHARE_FLOW = 3;
    public static function getShareValue($data=null)
    {
        $arr= [
            self::BANNER_SHARE_NO => '没有分享',
            self::BANNER_SHARE_FLOW => '分享送流量',
        ];
        return ($data === null)?$arr:$arr[$data];
    }


    const BANNER_LOCATION_INDEX = 1;
    const BANNER_LOCATION_ACTIVITY = 2;
    public static function getLocationValue($data=null)
    {
        $arr= [
            self::BANNER_LOCATION_INDEX => '首页',
            self::BANNER_LOCATION_ACTIVITY => '活动',
        ];
        return ($data === null)?$arr:$arr[$data];
    }


    const NOTICE_SEND_NOW = 0;
    const NOTICE_SEND_TIMING = 1;
    public static function getNoticeSendType($data=null)
    {
        $arr= [
            self::NOTICE_SEND_NOW => '定时发送',
            self::NOTICE_SEND_TIMING => '立即发送',
        ];
        return ($data === null)?$arr:$arr[$data];
    }


    const NOTICE_SEND_NO = 0;
    const NOTICE_SEND_SUCCESS = 1;
    const NOTICE_SEND_FAILURE = 2;
    public static function getNoticeSendStatus($data=null)
    {
        $arr= [
            self::NOTICE_SEND_NO => '未发送',
            self::NOTICE_SEND_SUCCESS => '发送成功',
            self::NOTICE_SEND_FAILURE => '发送失败',
        ];
        return ($data === null)?$arr:$arr[$data];
    }

    public static function getSendObject($object=null){
        $data=[
            0   => '全部会员',
            1   => 'VIP1',
            2   => 'VIP2',
            3   => 'VIP3',
            4   => 'VIP4',
            5   => 'VIP5',
            6   => 'VIP6',
            15  => '普通会员',
        ];
        return $object===null?$data:$data[$object];
    }

    public static function getYesNoText($status=null)
    {
        $text = ['否','是'];
        return $status==null?$text:$text[$status];
    }

    public static function getMessageObject($status=null)
    {
        $text = ['会员级别','会员账号'];
        return $status==null?$text:$text[$status];
    }


    const MESSAGE_SEND_DELAY = 0;
    const MESSAGE_SEND_NOW = 1;

    public static function getMessageSendStatus($data=null)
    {
        $arr= [
            self::MESSAGE_SEND_DELAY => '延时发送',
            self::MESSAGE_SEND_NOW => '立即发送',
        ];
        return ($data === null)?$arr:$arr[$data];
    }

    const AGREEMENT_TYPE_OTHER = 0;
    const AGREEMENT_TYPE_CURRENT = 1;
    const AGREEMENT_TYPE_REGULAR = 2;


    public static function getAgreementType($data=null)
    {
        $arr= [
            self::AGREEMENT_TYPE_OTHER => '其他协议',
            self::AGREEMENT_TYPE_CURRENT => '活期协议',
            self::AGREEMENT_TYPE_REGULAR => '定期协议',
        ];
        return ($data === null)?$arr:$arr[$data];
    }

    const SHARE_TYPE_INDEX = 1;
    const SHARE_TYPE_OTHER = 2;
    public static function getShareType($data=null)
    {
        $arr= [
            self::SHARE_TYPE_INDEX => '首页',
            self::SHARE_TYPE_OTHER => '其他',
        ];
        return ($data === null)?$arr:$arr[$data];
    }
}