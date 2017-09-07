<?php 

use yii\helpers\Url;
use yii\helpers\Html;
use frontend\assets\AppAsset;
use common\models\QfbOperation;
use common\models\QfbPcImage;
use common\models\QfbNavigation;
use common\models\QfbBaseNavigation;

    AppAsset::register($this);

    //获取运营数据
    $operation=QfbOperation::find()->where(['status'=>1])->asArray()->one();

    //获取用户登录状态
    $member_data=\Yii::$app->session->get('LOGIN');

    //获取头部导航
    $navigation=QfbNavigation::find()->where(['status'=>0])->orderBy('sort asc')->asArray()->all();

    //获取底部导航
    $base_navigation=QfbBaseNavigation::find()->where(['pid'=>0,'status'=>0])->orderBy('sort asc')->asArray()->all();

    foreach ($base_navigation as $key => $value) {
        $base_navigation[$key]['child'] = QfbBaseNavigation::find()->where(['pid'=>$value['id'],'status'=>0])->orderBy('sort asc')->asArray()->all();
    }

    //获取底部图片
    $base_image=QfbPcImage::find()->where(['type'=>3,'status'=>1])->orderBy('sort asc')->asArray()->all();

    //echo '<pre/>';var_dump($member_data);exit;

    
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
        $this->registerMetaTag(['http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8']);
        $this->registerMetaTag(['keywords' => 'Cache-Control', 'content' => '后台管理系统']);
        $this->registerMetaTag(['description' => 'Cache-Control', 'content' => '轻量级扁平化网站后台']);
        $this->head();
    ?>
    <!-- <meta charset="UTF-8"> -->
    <!-- <title>登录界面</title> -->
    <title><?php echo Html::encode($this->title); ?></title>
    <!-- 
    <link rel="stylesheet" href="css/comm.css">
    <link rel="stylesheet" href="css/css1.css">
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/unslider.min.js"></script>
    <script type="text/javascript" src="js/jqPaginator.min.js"></script>
    <script type="text/javascript" src="js/comm.js"></script>
     -->
</head>
<body>
    <?php $this->beginBody(); ?>
<!--q-header begin-->

<div class="q-header">
    <div class="q-headertop">
        <div class="q-wide">
            <div class="q-toptp fl">
                <i class="officicon tpiconer"></i><span>服务热线：<b class="grya6"><?php echo $operation['phone'];?></b>（<?php echo $operation['time'];?>）</span>
            </div>
            <div class="fr clearfix q-topvipment">
                <ul>
                     
                    <?php if (empty($member_data)) { ?>

                        <li><a href='/login/login/register' >注册</a></li>
                        <li><a href='/login/login/login' >登录</a></li>

                    <?php } else { ?>

                        <li><a href='/member/member/index' >我的账户</a></li>
                        <li><a href='/login/login/logout' >退出登录</a></li>

                    <?php }?>
                    
                </ul>
            </div>
        </div>
    </div>
    <div class="q-nav q-opcatiy">
        <div class="q-wide clearfix">
            <div class="q-logo fl">
                <a href="<?php echo Url::to(['/index/index/index']);?>" class="q-logobox"><img src="<?php echo \Yii::$app->fileStorage->baseUrl.'/'. $operation['logo'];?>"></a>
            </div>
            <div class="fr clearfix q-menue clearfix">
                <ul id="example-one">
                    <li class="q-cueractive"><a href="<?php echo Url::to(['/index/index/index']);?>">首页</a></li>
                     
                    <?php  foreach ($navigation as $key => $value) { ?>

                            <li><a href="<?php echo $value['url'] ?>" class="q-hoveirt"><?php echo $value['name'] ?></a></li>

                    <?php }?>

                </ul>
            </div>
        </div>
    </div>
</div>
<!--q-header end-->

<!-- content product begin-->
    <?php echo $content ; ?>
<!-- content product end-->


<!--footer begin-->
<div class="footerbox">
    <div class="q-wide">
        <div class="footer-box clearfix">
            <ul>
            <?php foreach ($base_navigation as $key => $value) { ?>
                <li>
                    <dl>
                        <dd><a href="<?php echo $value['url'] ?>"></a><?php echo $value['name'] ?></dd>
                        <?php foreach ($value['child'] as $k => $v) { ?>
                            <dt><a href="<?php echo $v['url'] ?>"><?php echo $v['name'] ?></a> </dt>
                        <?php }?>
                    </dl>
                </li>
            <?php }?>
                
            </ul>
            <div class="fr">
                <span class="f24 gray4">服务热线：</span><span class="f30 blue1 fontblod"><?php echo $operation['phone'];?></span>
                <span class="times-tit f18 gray2">（<?php echo $operation['time'];?>）</span>
            </div>
        </div>
        <div class="statement f14 gray2 pad20"><?php echo $operation['bottom'];?></div>
        <div class="statement-img clearfix">
            <ul>
            <?php foreach ($base_image as $key => $value) { ?>
                <li><a href="<?php echo $value['url'] ?>"><img src="<?php echo \Yii::$app->fileStorage->baseUrl.'/'. $value['image'];?>"></a></li>
            <?php } ?>
            </ul>
        </div>
    </div>
</div>
<!--footer end-->


<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>