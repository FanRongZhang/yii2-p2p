<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace api\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Asset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/css.css',
        'css/wcss.css',
    ];
    /*public $js = [
		'js/jquery-1.9.1.min.js',
        'js/wjs.js',
    ];*/
    /*public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];*/
	public $jsOptions = [
		'position'=>\yii\web\view::POS_HEAD
	];
}
