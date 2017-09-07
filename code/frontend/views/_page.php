<?php
use yii\widgets\LinkPager;
?>
<style>
	/*.pagination > li > a:hover, .pagination > li > span:hover, .pagination > li > a:focus, .pagination > li > span:focus{
		color:red;
		background-color:yellow;
	}
	.pagination > .active > a, .pagination > .active > span, .pagination > .active > a:hover, .pagination > .active > span:hover, .pagination > .active > a:focus, .pagination > .active > span:focus{
		color:red;
		background-color:yellow;
	}*/
</style>
    <?php echo LinkPager::widget([
    	'pagination' => isset($page) ? $page : '', 
    	'nextPageLabel' => '下一页', 
    	'prevPageLabel' => '上一页',
    	'firstPageLabel' => '首页', 
    	'lastPageLabel'=>'尾页',
    	'maxButtonCount' =>10,
    	// 'options' => ['class' => 'm-pagination'], 
    ]);?>

