<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = '消息中心';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('@web/js/jquery.js');


?>

<div class="fr memberright">
    <div class="member-main-box1">
        <h2 class="member-total-tit">消息中心</h2>
        <div class="member-mailts clearfix">
            <div class="member-news">
                <ul>
                    <?php
                    if($model){
                        foreach($model as $key=>$value){
                            if($value['read'] == true){
                                echo '<li class="member-read"><span>'.date('Y-m-d H:i:s',$value['create_time']).'</span><a href="'.Url::to(['message/detail', 'id'=>$value['id']]).'" class="tdmails-icon">'.$value['title'].'</a></li>';
                            }else{
                                echo '<li><span>'.date('Y-m-d H:i:s',$value['create_time']).'</span><a href="'.Url::to(['message/detail', 'id'=>$value['id']]).'" class="tdmails-icon">'.$value['title'].'</a></li>';
                            }
                        }
                    }else{
                        echo '<li class="me-null-data"><img src="/image/zwxicon.png"><br>暂无消息</li>';
                    }
                    ?>
                </ul>
            </div>
            <!--分页 begin-->
            <div class="page-box">
                <div class="tdb-page clearfix">
<!--                    <ul id="page" class="clearfix fl">-->
                    <?php
                    // 显示分页
                    echo LinkPager::widget([
                        'pagination' => $pagination,
                    ]);
                    ?>
<!--                    </ul>-->
                </div>
            </div>
            <!--分页 end-->
        </div>
    </div>
</div>


