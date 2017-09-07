<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\AppAsset;
    
    $this->title = '注册成功';
    AppAsset::register($this);
    //echo '<pre/>';var_dump($memberdata);exit;
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
       <div class="regsuccess-box">
            <div class="siccess-title">
                <img src="../../image/succesicon.png"><span class="f24 gray59">恭喜您注册成功!</span>
            </div>
            <div class="subei-icon">
                <p class="reg-imgtes"><img src="../../image/sucesicon.png"></p>
                <p class="f16 reg-writes-tit">为保障资金安全，本平台已接入海口联合农商银行资金存管系统，需开设资金存管账户才能参与投标。可以选择现在开户或是直接登录平台查看当前标的。</p>
            </div>
            <div class="sucess-erro padt30">
                <div class="sucess-btns-con">
                    <a href="/login/login/reg-bank?member_id=<?php echo $memberdata['id'] ?>&member_type=<?php echo $memberdata['member_type'] ?>>" class="suce-btn-left">开设资金存管账户</a>
                    <a href="/index/index/index" class="suce-btn-right">先不开户，直接登陆</a>
                </div>
            </div>
       </div>
    </div>
</div>
<!-- content product end-->

<?php $this->beginBlock('test'); ?>

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>