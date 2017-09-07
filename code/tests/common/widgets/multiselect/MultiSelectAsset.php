<?php
namespace common\widgets\multiselect;
use yii\web\AssetBundle;

class MultiSelectAsset extends AssetBundle
{
    public $js = [
        'js/multi-selector.js',
    ];
     
    public function init()
    {
        $this->sourcePath =$_SERVER['DOCUMENT_ROOT'].\Yii::getAlias('@web').'/multiselect';
    }
}

?>