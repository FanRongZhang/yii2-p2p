<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '回调处理';
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
       <div class="regsuccess-box">
            <div class="siccess-title">
                <img src="../../image/succesicon.png"><span class="f24 gray59"><?php echo $data['msg'] ?></span>
            </div>
            <div class="sucetdsb-con">
                <!-- <div class="suctx-bj">提现</div> -->
                <div class="suce-lister clearfix">
                    <ul>
                    <?php if (!empty($data['money'])) { ?>
                        <li><?php echo $data['title'] ?>:<?php echo $data['money'] ?></li>
                    <?php }?>
                        <li></li>
                        <li>处理时间：<?php echo date('Y-m-d H:i:s',$data['time']) ?></li>
                    </ul>
                </div>
            </div>
            <div class="sucess-erro padt30">
                <div class="sucess-btns-con">
                    <a href="/member/member/index" class="suce-btn-left">返回账户</a>
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