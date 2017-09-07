<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'company_name');
$this->params['breadcrumbs'][] = $this->title;
?>
  <div class="header">

      <div class="dl-title">

          <span class="dl-title-text"><?=yii::t('app','company_name')?></span>

      </div>

    <div class="dl-log"><?=yii::t('app','welcome')?><span class="dl-log-user"><?=yii::$app->user->identity->account?></span>
    <a href="<?=Url::to('/site/logout')?>" title=<?=yii::t('app','btn_loginout')?> class="dl-log-quit">[<?=yii::t('app','btn_loginout')?>]</a>
    </div>
  </div>
   <div class="content">
    <div class="dl-main-nav">
      <div class="dl-inform"><div class="dl-inform-title">贴心小秘书<s class="dl-inform-icon dl-up"></s></div></div>
      <ul id="J_Nav"  class="nav-list ks-clear">
        <li class="nav-item dl-selected"><div class="nav-item-inner nav-home">首页</div></li>
      <!--    <li class="nav-item"><div class="nav-item-inner nav-order">表单页</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">搜索页</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-supplier">详情页</div></li>-->
      </ul>
    </div>
    <ul id="J_NavContent" class="dl-tab-conten">

    </ul>
   </div>

  <script>
  BUI.use('common/main',function(){

      var config = [{
          id:'menu',
     //     homePage : 'code',
          menu:<?php echo $menu;?>
          }
      	];
      new PageUtil.MainPage({
        modulesConfig : config
      });
    });
  </script>
