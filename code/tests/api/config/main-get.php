<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'modules' => [
        'v200' => [
            'class' => 'api\versions\v200\ApiModule'
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableSession' => false,
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'request' => [
            'class' => '\yii\web\Request',
            'enableCookieValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                $statusCode = $response->statusCode;
                //redeclare the format of error response
                //except 404 error
                if ($statusCode != 200 && $statusCode != 404 && $statusCode != 500) {
                    switch ($statusCode) {
                        case '429':
                            $msg = '访问过于频繁, 请稍后再试';
                            break;
                        case '401':
                            $statusCode = 10004;
                            $msg = '未登录';
                            $response->setStatusCode(200);
                            break;
                        default:
                            $msg = $response->statusText;
                            break;
                    }
                    $response->data = [
                        'code' => $statusCode,
                        'msg' => $msg,
                    ];
                } else if ($statusCode == '404') {
                    if ($response->format == 'html') {
                        $response->format = 'json';
                        $response->data = [
                            'code' => $statusCode,
                            'msg' => '页面未找到',
                        ];
                    } else {
                        $response->data = [
                            'code' => $statusCode,
                            'msg' => '没有数据',
                            'data' => [],
                        ];
                    }
                }
            },
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v200/article', ],
                    'pluralize' => false,
                ],
                'GET v200/member/login' => 'v200/user/login',                  //1登录(陈玉东)
                'GET v200/member/setzfpwd' => 'v200/member/set-zfpwd',         //设置支付密码(陈玉东)
                'GET v200/member/updatenick' => 'v200/member/update-nick',     //修改昵称(陈玉东)
                'GET v200/member/updatepwd' => 'v200/member/update-pwd',       //修改登录密码(陈玉东)
                'GET v200/member/resetpassword' => 'v200/member/reset-password',       //忘记密码（陈玉东）不登录
                'GET v200/member/updatereferrer' => 'v200/member/update-referrer',     // 修改推荐人关系（陈玉东）
                'GET v200/member/isphone'=>'v200/member/is-phone',                     //手机号码查询 (陈玉东)
                'GET v200/index/tips'=>'v200/message/tips',                    //首页公告 (陈玉东)
                'GET v200/bank/support' => 'v200/bank/support',                //查看银行卡支持列表 (陈玉东)
                'GET v200/bankcardinfo/getinfo' => 'v200/bankcardinfo/get-info',//银行卡信息查询(陈玉东 不登陆)
                'GET v200/site/verifycode' => 'v200/site/verifycode',           //校验验证码(陈玉东)
                'GET v200/message/content'=> 'v200/message/content',            // 消息详情(陈玉东)

                'GET v200/member/register' => 'v200/user/register',            //2注册（罗文剑）

                'GET v200/current/create'=>'v200/current/create',              //47购买活期理财（熊立）
                'GET v200/regular/create'=>'v200/regular/create',              //46.定期购买（熊立）

                'GET v200/money/contacts' => 'v200/money/contacts',            //73我的人脉v200（金先智）（GET）
                'GET v200/member/authname' => 'v200/member/authname',          //4实名认证v200(GET)(金先智)
                'POST v200/member/avatar' => 'v200/member/avatar',              //13上传头像v200(金先智)(GET)

                'GET v200/bank/othercreate' => 'v200/bank/other-create',       //第三方绑卡(马洪波)
                'GET v200/bank/getcode' => 'v200/bank/get-code',               //快钱重新获取验证码（马洪波）
                'GET v200/bank/othercommit' => 'v200/bank/other-commit',       //75快钱绑卡支付提交(马洪波)
                'GET v200/money/recharge' => 'v200/money/recharge',            //充值(马洪波)
                'GET v200/notify/kuaiqian' => 'v200/notify/kuaiqian',          //快钱回调（马洪波）
                'GET v200/bank/othercreate' => 'v200/bank/other-create',       //71第三方绑卡(马洪波)

                'GET v200/version/new' => 'v200/version/new',                  //16版本更新（王舜峰 不登陆）

                'GET v200/money/withdrawals' => 'v200/money/withdrawals',      //提现(熊兵)

                'GET v200/money/back' => 'v200/money/back',                    //赎回(罗文剑)
                'GET v200/money/yiorder' => 'v200/money/yiorder',              //51易联银行卡支付(罗文剑)
                'GET v200/money/yinotify'=> 'v200/money/yinotify',             //易联回调（罗文剑）

                'GET v200/member/verifypwd' => 'v200/member/verify-pwd',       //验证密码（熊兵）
                'GET v200/money/backrule'=>'v200/money/back-rule',              //赎回规则(熊兵)
                'GET v200/money/withdrawrule' => 'v200/money/withdraw-rule',    //提现规则(熊兵)

                'GET v200/index/icon' => 'v200/index/icon',                     //20首页 （罗文剑 不登陆）
                'GET v200/index/recommend' => 'v200/index/recommend',           //21首页为你推荐（罗文剑 不登陆）
                'GET v200/my/all' => 'v200/my/all',                             //55我的全部投资(罗文剑)
                
                'GET v200/money/buyback' => 'v200/money/buy-back',              //60定期活期输入金额返回优惠券v200（Get）（金先智）
                'GET v200/money/buydetail' => 'v200/money/buy-detail',          //59定期理财投资结果页面v200(Get)（金先智）
                'GET v200/money/buyrule' => 'v200/money/buy-rule',              //58定期活期投资规则获取v200（Get）（金先智）
                'GET v200/money/mymoney' => 'v200/money/mymoney',               //19我的v200(Get)（金先智）
                'GET v200/member/coupon' => 'v200/member/coupon',               //26我的代金券列表v200(Get)（金先智）
                'GET v200/member/money' => 'v200/member/money',                 //32零钱代金券积分查询（金先智 APP不需要）
                'GET v200/member/coupondetail' => 'v200/member/coupon-detail',  //28我的代金券详情(金先智 APP不需要)


                'GET v200/feedback/list' => 'v200/feedback/list',               //38我的反馈（王舜峰 )
                'GET v200/feedback/commit' => 'v200/feedback/commit',           //40提交反馈（王舜峰 )
                'GET v200/feedback/content' => 'v200/feedback/content',         //39反馈详情（王舜峰 )
                'GET v200/version/about'=>'v200/version/about',                 //37关于钱富宝（王舜峰 )
                'GET v200/index/all'=>'v200/index/all',                         //所有理财列表(王舜峰)
                'GET v200/my/record' => 'v200/my/record',                       //24我的资产记录之充值提现定期活期四种列表（王舜峰 )
                'GET v200/my/recorddetail' => 'v200/my/record-detail',          //29我的资产记录详情（王舜峰 )
                'GET v200/member/profit' => 'v200/member/profit',               //53活期理财详情（王舜峰 )
                'GET v200/regular/waitdetail' => 'v200/regular/wait-detail',    //17我的待收收益(王舜峰)
                'GET v200/money/question'=>'v200/money/question',               //常见问题（王舜峰 APP没这个接口）

                'GET v200/product/detail'=>'v200/product/detail',               //54定期详情（熊立）
                'GET v200/product/buylist'=>'v200/product/buylist',             //56定期活期单个项目投资记录（熊立）
                'GET v200/regular/record'=>'v200/regular/record',               //57定期理财用户投资记录（熊立）
                'GET v200/qfb/list'=>'v200/qfb/list',                           //31我的资产记录(熊立)

                'GET v200/site/code' => 'v200/site/code',                      //获取验证码（陈玉东 不登陆）
                'GET v200/message/list'=> 'v200/message/list',                 //消息列表(陈玉东)
                'GET v200/site/getagreement' => 'v200/site/get-agreement',     //协议（陈玉东 不登陆）
                'GET v200/share/file' => 'v200/share/file',                    //协议地址(陈玉东)
                'GET v200/qfb/share'=>'v200/qfb/share',                         //分享（陈玉东）

                'GET v200/money/payment' => 'v200/money/payment',               //70选择支付方式（马洪波）
            ],
        ],
    ],
    'params' => $params,
];
