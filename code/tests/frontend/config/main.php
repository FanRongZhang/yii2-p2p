<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    /* 默认路由 */
    'defaultRoute' => '/index/index/index',
    /* 控制器默认命名空间 */
    'controllerNamespace' => 'frontend\controllers',
    'modules' => require(__DIR__ . '/modules.php'),
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'idParam' => '__user',
            'identityCookie' => ['name' => '__user_identity', 'httpOnly' => true],
            // 'loginUrl' => ['/member/auth'],
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
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,//隐藏index.php 
            //'enableStrictParsing' => false,
            //'suffix' => '.html',//后缀，如果设置了此项，那么浏览器地址栏就必须带上.html后缀，否则会报404错误
            'rules' => [
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];
