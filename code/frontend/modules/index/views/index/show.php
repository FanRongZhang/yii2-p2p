<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = $data['title'];
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
            <div class="con-news-details clearfix">
                <div style="background: #fff;">
                    <div class="con-tentes">
                        <div class="news-tites"><h2><?php echo $data['title'] ?></h2></div>
                        <div class="news-total-con">
                            <p><?php echo $data['content'] ?></p>
                        </div>
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