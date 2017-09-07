<?php
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '回调处理';
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
       <div class="regsuccess-box">
            <div class="siccess-title">
                <img src="../image/failicon.png"><span class="f24 gray59">业务受理失败，请稍后重试！</span>
            </div>
          <!--  <div class="con-tishiye pad20">系统故障导致提现失败</div>
           <div class="sucetdsb-con">
               <div class="suctx-bj">提现</div>
               <div class="suce-lister clearfix">
                   <ul>
                       <li>提现金额:100元</li>
                       <li>提现单号：7900965</li>
                       <li>提现时间：2017-6-12</li>
                   </ul>
               </div>
           </div> -->
            <div class="sucess-erro padt30">
                <div class="sucess-btns-con">
                    <!-- <a href="#" class="suce-btn-left">重试</a> -->
                    <a href="/member/member/index" class="suce-btn-right">返回账户</a>
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