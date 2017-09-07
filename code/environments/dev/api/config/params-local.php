<?php
return [
    //api访问频率限制
    'maxRateLimit'=>1,
    'perRateLimit'=>3,
    'maxGetRateLimit'=>2,
    'perGetRateLimit'=>1,

    //担保配置
    'guarantee'=>[
        [
            'tips'=>'账号资金安全由太平洋保险100%承保',
            'tips_url' => 'http://dmallapi.dm188.cn/img/icon_safe.png'
        ]
    ],

    'sms_name'  => '钱富宝',
    'sms_content'   => '钱富宝',
    
    //实名认证
    'idcard_userid'=>'dmsjwsquery',
    'idcard_password'=>'{MD5}5WcEtUA5iispoHSvLhgmUg==',
    'idcard_endpoint'=>'http://www.pycredit.com:9001',

    //充值最小金额
    'recharge_min_money' => 1,

    //安全验签参数
    'is_sign' => false, //验签开关(true表示要验签, false表示不要验签)
    'default_aes_key' => 'a610a3285c883de2',
    'default_aes_iv' => '6109240608fe732b',
    'not_decrypt_action' => [],
];
