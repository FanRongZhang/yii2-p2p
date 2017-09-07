<?php
namespace common\service;

/**
 * 处理一些控件数据接口
 * @author Administrator
 *
 */
interface IComponent
{
    /**
     * 根据控件类型返回对应数据
     * @param int $type 控件类型
     * @param object $sourceData 数据源
     */
    public function getComponentData($type=1,$sourceData=null);
}

?>