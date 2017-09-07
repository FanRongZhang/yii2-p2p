<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use trntv\filekit\widget\Upload;
use yii\web\JsExpression;

$this->title = '基本信息';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('@web/js/jquery-2.1.4.min.js');
?>
<!-- content product begin-->
<div class="fr memberright">
    <div class="member-main-box1">
        <h2 class="member-total-tit">基本信息</h2>
        <div class="member-photo-name">
            <div class="clearfix member-borderb">
                <div class="member-photo-img fl"><img src="<?php echo $memberInfo['avatar']?\Yii::$app->fileStorage->baseUrl.'/'.$memberInfo['avatar']:'/image/mrtx.png';?>"></div>
                <div class="fr member-fonts"><a href="<?=Url::to(['member-info/update', 'member_id'=>$memberInfo['member_id']])?>" class="blue1">设置</a> </div>
            </div>
            <div class="member-phonest clearfix">
                <ul>
                    <li class="black1 paphone">手机号码</li>
                    <li class="gray2"><?php echo substr($memberInfo['mobile'],0,3).'****'.substr($memberInfo['mobile'],7)?></li>
                </ul>
            </div>
            <div class="member-phonest clearfix">
                <ul>
                    <li class="black1 paphone">
                        <?php
                        if($memberInfo['is_dredge'] == 1){
                            echo '</i>姓名</li><li class=\"gray2\">*'.substr($memberInfo['realname'],3).'</li>';
                        }else{
                            echo '<i class="officicon con-renzheng"></i>姓名</li><li class="gray2">未认证</li>
                                <li class="te-center"><a href="'.Url::to(['auth/index']).'" class="blue1">前往认证</a> </li>';
                        }
                        ?>

                </ul>
            </div>
            <div class="member-phonest clearfix">
                <ul>
                    <li class="black1 paphone">UID</li>
                    <li class="gray2"><?php echo $memberInfo['member_id'];?></li>
                </ul>
            </div>
            <div class="member-phonest clearfix">
                <ul>
                    <li class="black1 paphone">登录密码</li>
                    <li class="gray2">******</li>
                    <li class="te-center fr"><a href="<?php echo Url::to(['member/member-password']);?>" class="blue1">修改</a> </li>
                </ul>
            </div>
            <div class="member-phonest clearfix">
                <ul>
                    <li class="black1 paphone">交易密码</li>
                    <li class="gray2">******</li>
                    <li class="te-center fr"><a href="<?php echo Url::to(['member-info/rest-hkyh-password']);?>" class="blue1">修改</a> </li>
                </ul>
            </div>
            <div class="member-phonest clearfix">
                <ul>
                    <li class="black1 paphone">
                        <?php
                        if($memberInfo['is_dredge'] == 1){
                            echo '证件号码</li>
                                                <li class="gray2">'.substr($memberInfo['card_no'],0,3).'**********'.substr($memberInfo['card_no'],14).'</li>';
                        }else{
                            echo '<i class="officicon con-renzheng"></i>证件号码</li>
                                                <li class="gray2">未认证</li>
                                                <li class="te-center"><a href="'.Url::to(['auth/index']).'" class="blue1">前往认证</a> </li>';
                        }
                        ?>


                </ul>
            </div>

        </div>
    </div>
</div>