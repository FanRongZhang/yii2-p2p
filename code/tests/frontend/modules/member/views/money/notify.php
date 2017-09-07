<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

empty($data) == 1?$this->title = '提现成功' : $this->title = '充值成功';
$this->params['breadcrumbs'][] = $this->title;

var_dump($data);

?>