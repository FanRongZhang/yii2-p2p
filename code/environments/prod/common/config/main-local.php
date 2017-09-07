<?php
return [
    'components' => [
        'db' => [
            // 主库配置（用于写操作）
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.10.10.1;dbname=dm_qfb',
            'username' => 'xiongbing',
            'password' => 'Dmsj@1366',
            'charset' => 'utf8',
            'tablePrefix' => 'qfb_',
            // 通用从库配置 (用于读操作)
            'slaveConfig' => [
                'username' => 'xiongbing',
                'password' => 'Dmsj@1366',
                'charset' => 'utf8',
                'tablePrefix' => 'qfb_',
                'attributes' => [
                    // 超时时间
                    PDO::ATTR_TIMEOUT => 10,
                ],
            ],
            //从库列表 (可多个)
            'slaves' => [
                ['dsn' => 'mysql:host=10.10.10.1;dbname=dm_qfb'],
            ],
        ],

        'ucenter' => [
            // 主库配置（用于写操作）
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.10.10.1;dbname=dm_ucenter',
            'username' => 'xiongbing',
            'password' => 'Dmsj@1366',
            'charset' => 'utf8',
            'tablePrefix' => 'qfb_',
            // 通用从库配置 (用于读操作)
            'slaveConfig' => [
                'username' => 'xiongbing',
                'password' => 'Dmsj@1366',
                'charset' => 'utf8',
                'tablePrefix' => 'qfb_',
                'attributes' => [
                    // 超时时间
                    PDO::ATTR_TIMEOUT => 10,
                ],
            ],
            //从库列表 (可多个)
            'slaves' => [
                ['dsn' => 'mysql:host=10.10.10.1;dbname=dm_ucenter'],
            ],
        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],

        /**
         * 快钱支付组件
         */
        'kuaiQian' => [
            'class' => 'common\extension\kuaiqian\KuaiQian',
            'pemFile' => '81231006211009690.pem',
            'merchantId' => '812310062110096',
            'certPassword' => 'vpos123',
            'terminalIdBind' => '00005464',
            'terminalIdCharge' => '00005463',
            'tr3Url' => 'http://dmallapi.dm188.cn/notify/kuaiqian',
            'debug' => false,
            'baseUrl' => 'https://mas.99bill.com',
        ],

        /**
         * 华融支付组件
         */
        'hrpay' => [
            'class' => 'common\extension\hrpay\HrPay',
            'mercNo' => '00000000000365',
            'md5Key' => 'FbPgHIeb0omZ',
            'smsUrl' => 'https://www.rongyf.com/user/SendSMS.mob',
            'payUrl' => 'https://www.rongyf.com/user/YB0001.wpay',
            'authUrl' => 'https://www.rongyf.com/user/HC0001.tran',
            'notifyUrl' => 'http://dmallapi.dm188.cn/v200/notify/hr',
        ],

        /**
         * mongodb组件
         */
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://test:test@112.74.105.153:27017/d_wallet',
        ],
    ],
];
