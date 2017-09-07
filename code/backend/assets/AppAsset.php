<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/bui-min.css',
        'css/dpl-min.css',
        'css/main.css',
        'css/page-min.css',
        'css/jquery-ui-1.10.0.custom.css'
    ];
    public $js = [
        'js/jquery-ui-1.10.0.custom.min.js',
        'js/layer/layer.js',
        'js/jquery.form.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $jsOptions = [
        'position'=>\yii\web\view::POS_HEAD
    ];
}
