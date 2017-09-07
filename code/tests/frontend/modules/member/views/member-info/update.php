<?php
use yii\helpers\Html;


$this->title='编辑头像';
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
                            <li><a href="#">账户总览</a></li>
                            <li class="memb-acitves"><a href="#">基本信息</a></li>
                            <li><a href="#">消息（1）</a></li>
                        </ul>
                    </div>
                </div>
                <div class="con-member">
                    <h2 class="con-tit-mem con-tit-mem2"><i class="officicon"></i>资金</h2>
                    <div class="con-mem-list">
                        <ul>
                            <li><a href="#">充值</a></li>
                            <li><a href="#">提现</a></li>
                            <li><a href="#">我的投资</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>

    </div>
</div>

