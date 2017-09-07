<?php

namespace frontend\modules\member\controllers;

use common\models\QfbMember;
use frontend\controllers\WebController;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class BaseController extends WebController
{
    public $layout = 'main'; //设置使用的布局文件

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }


}