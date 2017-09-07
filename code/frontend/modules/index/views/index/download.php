<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
    
    $this->title = '下载APP';
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="downloadbox">
        <div class="download-contents">
            <div class="down-maines">
                <div class="down-writes"><img src="../../image/dtext.png"></div>
                <div class="down-btn">
                    <div><a href="https://itunes.apple.com/us/app/%E9%92%B1%E5%AF%8C%E5%AE%9Dpro/id1262960542?mt=8"><img src="../../image/btnapple.png"></a></div>
                    <div class="down-anter"><a href="http://zhushou.360.cn/detail/index/soft_id/3873699?recrefer=SE_D_%E9%92%B1%E5%AF%8C%E5%AE%9DPro"><img src="../../image/btnan.png"></a></div>
                </div>
            </div>
            <div class="down-dimen"><img src="../../image/dimen.png"></div>
            <div class="down-phontes-paniel"><img src="../../image/guzt.png"></div>
        </div>
    </div>
</div>
<!-- content product end-->
<?php $this->beginBlock('test'); ?>


<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>