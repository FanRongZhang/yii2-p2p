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
            'tips'=>'赢华-钱富宝智享专项基金<br>提供收益保障',
            'tips_url' => 'http://dmallapi.dm188.cn/img/icon_pingan_logo.png'
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
];
