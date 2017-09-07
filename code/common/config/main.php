<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'timeZone'=>'Asia/Chongqing',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
            // 'class' => 'yii\redis\Cache',
        ],

        'fileStorage' => [
            'class' => '\trntv\filekit\Storage',
            'baseUrl' => 'http://storage.qfb.com/uploads',
            'filesystem' => [
                'class' => 'common\components\filesystem\LocalFlysystemBuilder',
                'path' => '@storage/uploads'
            ],
            'as log' => [
                'class' => 'common\behaviors\FileStorageLogBehavior',
                'component' => 'fileStorage'
            ]
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],

    ],
];
