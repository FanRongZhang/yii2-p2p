<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '公告详情';
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">

            <div class="con-news-details clearfix">
                <div class="fl con-news-detail-left">
                    <div class="con-tentes">
                        <div class="news-tites"><h2><?php echo $newsdata['title'] ?></h2><p><?php echo date('Y-m-d H:i:s',$newsdata['create_time']) ?></p></div>
                        <div class="news-total-con">
                            <p><?php echo $newsdata['content'] ?></p>
                        </div>
                    </div>

                </div>
                <div class="fl con-news-detail-right">
                    <div>
                        <h2 class="member-total-tit">其他公告</h2>
                        <div class="member-total-list">
                            <ul>
                            <?php foreach ($newslist as $key => $value) { ?>
                                <li><span><?php echo  date('Y-m-d H:i:s',$value['create_time'])  ?></span><a href="/index/index/newsdetail?id=<?php echo $value['id'] ?>"><?php echo $value['title'] ?></a> </li>
                            <?php } ?>
                            </ul>
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