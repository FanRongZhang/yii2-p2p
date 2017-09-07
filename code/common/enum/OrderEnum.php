<?php
namespace common\enum;

/**
 * 订单枚举,罗列各种订单状态
 *
 */
class OrderEnum
{
    /**
     * 待支付
     */
    const WAITING_PAYMENT = 0x00;
    
    /**
     * 已支付
     */
    const ALREADY_PAYMENT = 0x01;

    /**
     *失败
     */
    const FAIL_PAYMENT = 0x02;

    /*
     * 处理中
     * */
    const WAITING_MIDDLE_PAYMENT = 0x03;

    /*
     * 无此交易
     * */
    const PAY_NO = 0x04;

    /*
     * 审核通过
     * */
    const PASS_PAYMENT = 0x05;

    /*
     * 转入
     * */
    const MONEY_IN = 0x01;

    /*
     * 转出
     * */
    const MONEY_OUT = 0x02;

    /*
     * 零钱
     * */
    const SMALL_MONEY = 0x01;

    /*
     * 活期
     * */
    const FLOW_MONEY = 0x02;

    const ALL = '';

    //提现类型
    public static function getOutType($type){
        $data =[
            self::ALL=>'全部',
            0=>'其它',
            1=>'当日到账',
            2=>'1-3工作日到账',
        ];
        return is_null($type)?$data:$data[$type];
    }

    //是否对账
    public static function getIsCheck($type){
        $data =[
            self::ALL=>'全部',
            0=>'待处理',
            1=>'成功',
            2=>'失败',
            3=>'处理中',
            4=>'无此交易',
            5=>'审核通过'
        ];
        return is_null($type)?$data:$data[$type];
    }

    //订单渠道
    public static function getChannel($type){
        $data =[
            self::ALL=>'全部',
            /*0=>'银嘉',
            1=>'易联',
            2=>'支付宝',*/
            3=>'零钱',
            //4=>'京东',
            5=>'快钱',
            8=>'易宝'
        ];
        return is_null($type)?$data:$data[$type];
    }

    //定期的状态
    public static function getRegular($type){
        $data =[
            self::ALL=>'全部',
            0=>'待支付',
            1=>'投资中',
            2=>'收益中',
            3=>'已到期',
            4=>'支付失败',
        ];
        return is_null($type)?$data:$data[$type];
    }

    //金额标记
    public static function getMark(){
        $data =[
            1=>'大于',
            2=>'等于',
            3=>'小于',
        ];
        return $data;
    }

    public static function getRepaymentStatus($type)
    {
        $data = [
            0=>'未还款',
            1=>'已还款，待确认',
            2=>'确认还款',
            9=>'还款异常'
        ];

        return $data[$type];
    }
}

?>