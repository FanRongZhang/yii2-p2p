<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '公告列表';
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
       <div class="con-news-main">
            <div><img src="../../image/zxtitle.png"></div>
            <div class="con-news-tents">
                <div class="contentews clearfix">
                    <ul>
                    <?php if(empty($newsdata)){ ?>
                        <li class="me-null-data" style="display: ;"><img src="../../image/zwxicon.png"><br>暂无资讯</li>
                    <?php }else{
                        foreach ($newsdata as $key => $value) { ?>
                           <li><span><?php echo date('Y-m-d H:i:s',$value['create_time']) ?></span><a href="/index/index/newsdetail?id=<?php echo $value['id'] ?>"><?php echo $value['title'] ?></a> </li>
                        <?php }?>
                    <?php } ?>
                    </ul>
                </div>
                <!--分页 begin-->
                <div class="page-box">
                    <div class="b-page clearfix">
                        <!-- 分页 start-->
                        <?= $this->render('../../../../views/_page', ['count' => $count, 'page' => $page]) ?>
                        <!-- 分页 end-->
                    </div>
                </div>
                <!--分页 end-->
            </div>
       </div>
    </div>
</div>
<!-- content product end-->



<?php $this->beginBlock('test'); ?>


<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>