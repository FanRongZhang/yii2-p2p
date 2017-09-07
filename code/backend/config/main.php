<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'language' => 'zh-CN',
    'bootstrap' => ['log'],
    'modules' => [
        //导出表单用模块
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
            // enter optional module parameters below - only if you need to  
            // use your own export download action or custom translation 
            // message source
            // 'downloadAction' => 'gridview/export/download',
            // 'i18n' => []
        ],
        //文章模块（实例）
        'article' => [
            'class' => 'backend\modules\article\Module',
        ],
		 //系统模块
        'system' => [
            'class' => 'backend\modules\system\Module',
        ],
		 //产品模块
        'product' => [
            'class' => 'backend\modules\product\Module',
        ],
        //订单模块
        'order' => [
            'class' => 'backend\modules\order\Module',
        ],
		//内容模块
        'content' => [
            'class' => 'backend\modules\content\Module',
        ],
        //消息模块
        'messages' => [
            'class' => 'backend\modules\messages\Module',
        ],
        //营销模块
        'market' => [
            'class' => 'backend\modules\market\Module',
        ],
        //会员模块
        'member' => [
            'class' => 'backend\modules\member\Module',
        ],
        //反馈模块
        'feedback' => [
            'class' => 'backend\modules\feedback\Module',
        ],
    ],
    'components' => [
        //国际化管理
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@backend/messages',
                    //'sourceLanguage' => 'en',
                ],
            ],
        ],
        'user' => [
            'identityClass' => 'backend\models\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];
