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
class MenuAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        // 'css/site.css',
        'bui/assets/css/dpl-min.css',
        'bui/assets/css/bui-min.css',
        'bui/assets/css/main.css',
    ];
    public $js = [
        'bui/assets/js/jquery-1.8.1.min.js',
        'bui/assets/js/bui-min.js',
        'bui/assets/js/config-min.js',
    ];
    public $depends = [
        // 'yii\web\YiiAsset',
        // 'yii\bootstrap\BootstrapAsset',
    ];

    public $jsOptions = [
        'position'=>\yii\web\view::POS_HEAD
    ];
}
