<?php
namespace common\enum;

/**
 * 状态枚举
 * @author Ben
 *
 */
class StatusEnum
{
    /**
     * 无效
     */
    const DISABLED = 0x00;
    
    /**
     * 有效
     */
    const ENABLED = 0x01;
    
    /**
     * 成功
     * @var unknown
     */
    const SUCCESS = 0x01;
    
    /**
     * 失败
     * @var unknown
     */
    const FAILED = 0X00;


    /**
    *待处理
    */
    const WAITING=0x00;

    /**
    *同意
    */
    const YES=0x01;
    /**
    *拒绝
    */
    const NO=0x02;

    public static function getCheckText($type=null){
        $data=[
            self::WAITING=>'待审核',
            self::YES=>'同意',
            self::NO=>'拒绝'
        ];
         return $type===null?$data:$data[$type];
    }


    /**
     * 获取是否有效属性
     * @param unknown $status
     */
    public static function getValidateText($status=null)
    {
        $text=['无效','有效'];
        if(isset($status)&&$status!=null)
            return $text[$status];
        else
            return $text;
    }
    
    /**
     * 是否启用
     * @param unknown $status
     */
    public static function getEnabledText($status=null)
    {
        $text=['禁用','启用'];
        if(isset($status)&&$status!=null)
            return $text[$status];
        else 
            return $text;
    }
    
    /**
     * 是否选项
     * @param unknown $status
     */
    public static function getYesNoText($status=null)
    {
        $text=['否','是'];
        return $status==null?$text:$text[$status];
    }
}

?>