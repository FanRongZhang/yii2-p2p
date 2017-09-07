<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@app/runtime/logs/'.date('Y').'/'.date('m').'/'.date("Ymd").'hkyh.log',
                    'logVars' => [], //该参数是记录$_GET, $_POST, $_FILES, $_COOKIE, $_SESSION 和 $_SERVER等相关的变量
                ],
            ],
        ],
    ],
    'params' => $params,
];
