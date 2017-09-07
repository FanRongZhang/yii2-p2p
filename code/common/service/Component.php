<?php
namespace common\service;
use common\enum\ComponentEnum;

/**
 * 组件数据
 * @author Ben
 *
 */
class Component implements IComponent
{
    public function getComponentData($type=1,$sourceData=null)
    {
        $data=null;
        switch ($type)
        {
            case ComponentEnum::RADIOLIST:
                $data=[0=>'否',1=>'是'];
                break;
        }
        return $data;
    }
    
    /**
     * 获取数据
     */
    public static function getRadioListData()
    {
        return (new Component())->getComponentData(ComponentEnum::RADIOLIST);
    }
    
    /**
     * 获取下拉框数据
     */
    public static function getDropdownListData($data)
    {
        $arr = [-1=>'不限'];
        foreach($data as $k=>$v)
        {
            $arr[$k]=$v;
        }
        return $arr;
    }
}

?>