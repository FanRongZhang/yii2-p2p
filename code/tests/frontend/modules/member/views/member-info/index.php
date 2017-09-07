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

$this->title = 'MemberInfo';
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide2">
        <div class="member-content clearfix">
            <div class="fl memberleft">
                <div class="con-member">
                    <h2 class="con-tit-mem"><i class="officicon"></i>我的账户</h2>
                    <div class="con-mem-list">
                        <ul>
                            <li><a href="<?php echo Url::to(['member/index']);?>">账户总览</a></li>
                            <li class="memb-acitves"><a href="<?php echo Url::to(['member-info/index']);?>">基本信息</a></li>
                            <li><a href="<?php echo Url::to(['message/index']);?>">消息（1）</a></li>
                        </ul>
                    </div>
                </div>
                <div class="con-member">
                    <h2 class="con-tit-mem con-tit-mem2"><i class="officicon"></i>资金</h2>
                    <div class="con-mem-list">
                        <ul>
                            <li><a href="<?php echo Url::to(['money/index']);?>">充值</a></li>
                            <li><a href="<?php echo Url::to(['money/index', 'type'=>1]);?>">提现</a></li>
                            <li><a href="<?php echo Url::to(['invest/index']);?>">我的投资</a></li>
                        </ul>
                    </div>
                </div>

            </div>
            <div class="fr memberright">
                <div class="member-main-box1">
                    <h2 class="member-total-tit">基本信息</h2>
                    <div class="member-photo-name">
                        <div class="clearfix member-borderb">
                            <div class="member-photo-img fl"><img src="<?php echo $memberInfo['avatar']?>"></div>
                            <div class="fr member-fonts"><a href="<?=Url::to(['member-info/update', 'member_id'=>$memberInfo['member_id']])?>" class="blue1">设置</a> </div>
                        </div>
                        <div class="member-phonest clearfix">
                            <ul>
                                <li class="black1 paphone">手机号码</li>
                                <li class="gray2"><?php echo $memberInfo['mobile']?></li>
                            </ul>
                        </div>
                        <div class="member-phonest clearfix">
                            <ul>
                                <li class="black1 paphone">
                                <?php
                                if($memberInfo['is_dredge'] == 1){
                                    echo '</i>姓名</li><li class=\"gray2\">'.$memberInfo['realname'].'</li>';
                                }else{
                                    echo '<i class="officicon con-renzheng"></i>姓名</li><li class="gray2">未认证</li>
                                <li class="te-center"><a href="'.Url::to('auth/index').'" class="blue1">前往认证</a> </li>';
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
                                <li class="te-center fr"><a href="#" class="blue1">修改</a> </li>
                            </ul>
                        </div>
                        <div class="member-phonest clearfix">
                            <ul>
                                <li class="black1 paphone">
                                <?php
                                    if($memberInfo['is_dredge'] == 1){
                                        echo '证件号码</li>
                                                <li class="gray2">'.$memberInfo['card_no'].'</li>';
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
        </div>

    </div>
</div>
