<?php
namespace common\widgets\datepicker;

use yii\web\AssetBundle;

/**
 * 资源配置文件
 * @author Ben
 *
 */
class DatePickerAsset extends AssetBundle
{
    public $js = [
        'laydate.js',
    ];
    public $css = [
    ];
    public function init()
    {
        $this->sourcePath =$_SERVER['DOCUMENT_ROOT'].\Yii::getAlias('@web').'/datepicker'; //设置资源所处的目录
    }
}

?>