<?php
use yii\helpers\Html;
use api\assets\Asset;

/**
 * @var \yii\web\View $this
 * @var string $content
 */
Asset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <meta charset="utf-8" />
	<!--<meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=yes" />设置viewport，适应移动设备的显示宽度-->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="format-detection" content="telephone=no" /><!--禁止safari电话默认拨打-->
	<meta name="apple-mobile-web-app-capable" content="yes"/><!--隐藏safari导航栏及工具栏-->
	<meta property="qc:admins" content="13666753740606375" />
	<meta property="wb:webmaster" content="02d1e0af77e62d70" />
    <?php $this->head() ?>
</head>
<body onload="setHeight();" onresize=" setHeight()" <?php 
	$ctl = $this->context;
	if($ctl->id == 'share' && ($ctl->action->id == 'flow' || $ctl->action->id == 'fortune')){ ?> 
		onload="setHeight();" onresize=" setHeight()" 
	<?php } ?>
>
<?php $this->beginBody() ?>

    <?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
