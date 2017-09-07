<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    //$this->title = $data['title'];
?>

<!-- content product begin-->

    <div class="about-title-cons"><h1><?php echo $_GET['title'] ?></h1></div>
    <div class="abou-honores">
    <?php echo $data['content'] ?>
    </div>
<!-- content product end-->

<?php $this->beginBlock('test'); ?>


<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>