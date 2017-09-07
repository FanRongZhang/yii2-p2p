<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '安全保障';
?>
<!--q-header end-->
<div class="an-baobox">
    <div><img src="../../image/aq1.png"></div>
    <div><img src="../../image/aq2.png"></div>
    <div><img src="../../image/aq3.png"></div>
    <div><img src="../../image/aq4.png"></div>
</div>
<?php $this->beginBlock('test'); ?>


<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>
