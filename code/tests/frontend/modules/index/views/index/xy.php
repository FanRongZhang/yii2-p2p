<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '协议';
?>
<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
        <div class="xymsbox">
            <h2 style="text-align: center"><?php echo $xy_data['title'] ?></h2>
            <?php echo $xy_data['content'] ?>
        </div>
    </div>
</div>
<!-- content product end-->
<?php $this->beginBlock('test'); ?>


<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>