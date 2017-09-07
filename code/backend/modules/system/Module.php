<?php

namespace backend\modules\system;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'backend\modules\system\controllers';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        \Yii::$app->getI18n()->translations['system*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => __DIR__ . '/messages',
            'fileMap' => [
                'article-category' => 'article-category.php',
            ],
        ];
    }
}
