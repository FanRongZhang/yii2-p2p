<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$type == 1?$this->title = '提现' : $this->title = '充值';
$this->params['breadcrumbs'][] = $this->title;

?>
<!-- content product begin-->
<div class="fr memberright">
    <div class="member-main-box1">
        <h2 class="member-total-tit"><?php echo $type == 1 ? '提现' : '充值';?></h2>
        <div class="member-recharge-main">
            <div class="member-recharge-conts">
                <div class="bombbox5 member-width-recharge">
                    <div class="bombbox1 clearfix">
                        <div class="fl"><?php echo $bank->name;?></div>
                        <div class="fr">尾号：<?php echo substr($bank->no, -4, 4);?></div>
                    </div>
                    <div class="bombbox6">注：单笔限额<?php echo $system['per_money'];?>元，每日限额<?php echo $system['day_money'];?>元</div>
                </div>

                <div class="bombbox2">
                    <?php $form = ActiveForm::begin([
                        'action' => [$type==1?'withdraw':'recharge'],
                        'method' => 'post',
                    ]); ?>
                    <div class="mar6">
                        <div class="clearfix mar6">
                            <label class="fl sn-pt1">账户余额</label>
                            <div class="fl lints">
                                <?php
                                if($money->money <= 0){
                                    echo '<span class="black1 f18">0.00</span>元';
                                }else{
                                    echo '<span class="black1 f18 orange2">'.$money->money.'</span>元';
                                }
                                ?>
                            </div>

                        </div>
                        <div class="clearfix mar6">
                            <?php
                            if($type == 1){
                                echo '<label class="fl sn-pt1">提现金额</label>
                                                        <div class="prform-inpt prform-inptts fl">
                                                            <div class="fl"><input name="money" type="text" class="printbox fonwts" placeholder="请输入提现金额"></div>元
                                                        </div>';
                            }else{
                                echo '<label class="fl sn-pt1">充值金额</label>
                                                        <div class="prform-inpt prform-inptts fl">
                                                            <div class="fl"><input name="money" type="text" class="printbox fonwts" placeholder="请输入充值金额"></div>元
                                                        </div>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mar30">
                        <div><button class="login-btnter lgbt-con">提交</button></div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
                <div class="bombbox3">
                    <div class="mar30">
                        <p class="blue1"><i class="officicon eitrs"></i>温馨提示</p>
                        <p class="grya6 mar6">需跳转至海口联合农商银行资金存管系统验证身份</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

