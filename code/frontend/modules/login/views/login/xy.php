<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\AppAsset;
    
    $this->title = '注册协议';
    AppAsset::register($this);
    //echo '<pre/>';var_dump($memberdata);exit;
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
        <div class="xymsbox">
            <h2 style="text-align: center"><?php echo $data['title'] ?></h2>
            <p><?php echo $data['content'] ?></p>
        </div>
    </div>
</div>
<!-- content product end-->



<?php $this->beginBlock('test'); ?>

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>