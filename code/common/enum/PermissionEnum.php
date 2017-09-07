<?php
namespace common\enum;

/**
 * 权限操作枚举
 * @author Administrator
 *
 */
class PermissionEnum
{
    /**
     * 查看权限值  1
     * @var integer
     */
    const VIEW = 0x01;
    
    /**
     * 添加权限制 2
     * @var integer
     */
    const ADD = 0x02;
    
    /**
     * 修改权限制  4
     * @var integer
     */
    const UPDATE = 0x04;
    
    /**
     * 删除权限制 8
     * @var integer
     */
    const DELETE = 0x08;
    
    /**
     * 回复权限     16
     * @var integer
     */
    const REPLY = 0x10;
    
    /**
     * 明细权限   32
     * @var integer
     */
    const DETAILS = 0x20;
    
    /**
     * 发布权限 64
     * @var integer
     */
    const PUBLISH = 0x40;

    /**
     * 放款权限 128
     * @var integer
     */
    const LOAN_MONEY = 0x80;

    /**
     * 绑卡权限 256
     * @var integer
     */
    const OPEN = 0x100;

    /**
     * 充值权限 512
     * @var integer
     */
    //const RECHARGE = 0x200;

    /**
     * 提现权限 1024
     * @var integer
     */
    //const WITHDRAW= 0x400;

    /**
     * 获取权限说明
     * @param integer $val
     */
    public static function getPermissionText($val=null)
    {
        $text=[
            self::VIEW=>'查看',
            self::ADD=>'添加',
            self::UPDATE=>'修改',
            self::DELETE=>'删除',
            self::REPLY=>'回复',
            self::DETAILS=>'明细',
            self::PUBLISH=>'发布',
            self::LOAN_MONEY=>'放款',
            self::OPEN=>'绑卡',
            // self::RECHARGE=>'充值',
            // self::WITHDRAW=>'提现',
        ];
        return $val==null?$text:$text[$val];
    }
    
    /**
     * 获取权限所对应的视图按钮
     * @param $template 用户自定义模版
     * @param integer $per 权限值
     * @param boolean $is_administrator 是否管理员
     * @param $user_buttons 用户扩展按钮
     */
    public static function getPermissionViewButtons($template,$per,$user_buttons=null)
    {
        $buttons = [
            self::VIEW=>'view',
            self::ADD=>'create',
            self::UPDATE=>'update',
            self::DELETE=>'delete'
        ];
        
        $new_buttons =[];
        foreach($buttons as $k=>$v)
        {            
            if(strpos($template,$v))
            {
                $new_buttons[$k]=$v;
            }
        }

        $permission = [];
        $per_array = static::getPermissionText();
        foreach($per_array as $k=>$v)
        {
            if(($per&$k)==$k)
            {
               if(isset($new_buttons[$k]) && $new_buttons[$k]!='')
                  $permission[$new_buttons[$k]]=$k;
            }
        } 
        //组合用户自定义按钮
        if($user_buttons!=null && count($user_buttons)>0)
        {
            foreach($user_buttons as $k=>$v)
            {
                if(($per&$v[1])==$v[1])
                $permission[$v[0]] = $v[2];
            }
        }
        return $permission;
    }
    
    /**
     * 获取权限值数组
     * @param int $per
     * @return array
     */
    public static function getPermissionArray($per)
    {
        $permission = [];
        $per_array = static::getPermissionText();
        foreach($per_array as $k=>$v)
        {
            if(($per&$k)==$k)
            {
                $permission[$k]=$v;
            }
        }
        return $permission;
    }
}

?>