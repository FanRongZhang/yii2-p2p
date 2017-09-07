<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '关于我们';
?>

<!-- content product begin-->
<div class="main-cloum">

            <div class="about-title-cons"><h1>团队介绍</h1></div>
                <div class="con-about-tints">

                    <ul>
                    <?php foreach ($data as $key => $value) { ?>

                        <li class="clearfix">
                            <div class="fl con-about-details"><img src="<?php echo Yii::$app->fileStorage->baseUrl.'/'.$value['image'] ?>"></div>
                            <div class="fl con-about-names">
                                <div class="con-about-natest">
                                    <h3><?php echo $value['name'] ?></h3>
                                    <p><?php echo $value['position'] ?></p>
                                </div>
                                <div class="con-about-pites">
                                    <?php echo $value['content'] ?>
                                </div>

                            </div>
                        </li>
                    <?php }?>    
                    </ul>
                </div>

</div>
<!-- content product end-->


<!-- content product end-->
<?php $this->beginBlock('test'); ?>


<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>