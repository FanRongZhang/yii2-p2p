<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    //yii登录验证密钥
    'auth_key'=>'dmjt!$*(^&',

    'encrypt_key'=>substr(md5('94738237'),0,16),
    'encrypt_iv'=>substr(md5('12s23sdq'),0,16),

    //短信通道序列号
    'sms_sn' => 'qfb',
    //短信通道密码
    'sms_pwd' => 'dmjtd068',
    'source'=>8,
    'sms_name'=>'钱富宝',
    'sms_content'=>'钱富宝Pro',
    //协议地址
    'agreement_url' => 'http://dmallapi.dm188.cn',

  'mongodb-153' => [
      'db_host' => '192.168.16.220',
      'db_port' => '27017',
      'db_user' => 'admin',
      'db_pass' => 'Dmjt@123',
  ],
//    'mongodb-153' => [
//        'db_host' => '192.168.6.12',
//        'db_port' => '27017',
//        'db_user' => 'root',
//        'db_pass' => 'Dmjtd@068',
//    ],

    //分润
    'share-profit' => [
        'direct_rate' => 30,  //直接会员分润比例
        'indirect_rate' => 30,  //间接推荐分润比例
        'share_rate' => 0  //分享会员分润比例
    ],

    //管理奖
    'manage-rate' => [
        'province_rate' => 40,  //省级代理管理奖比例
        'city_rate' => 30,    //市级代理管理奖比例
        'area_rate' => 20    //区级代理管理奖比例
    ],

   'overdue_interest' => 0.003, //逾期利息

  'platform_id' => 2, //逾期还款平台id
];
