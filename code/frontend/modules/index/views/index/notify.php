<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '通知';
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
       <div class="regsuccess-box">
            <div class="siccess-title">
                <img src="<?php if ($status==1) {
                        echo '../../image/succesicon.png';
                    } else {
                        echo '../image/failicon.png';
                    } ?>"><span class="f24 gray59"><?php echo $msg ?></span>
            </div>
            <div class="sucetdsb-con">
                <!-- <div class="suctx-bj">提现</div> -->
                <div class="suce-lister clearfix">
                    <ul>
                        <li>业务:申请借款</li>
                    <li></li>
                        <li>申请时间：<?php echo date('Y-m-d H:i:s',time()) ?></li>
                    </ul>
                </div>
            </div>
            <div class="sucess-erro padt30">
                <div class="sucess-btns-con">
                    <a href="/index/index/index" class="suce-btn-left">返回首页</a>
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