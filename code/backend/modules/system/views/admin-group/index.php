<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\service\AdminService;
use common\enum\PermissionEnum;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\system\models\AdminGroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '用户组权限');
$this->params['breadcrumbs'][] = $this->title;
?>


<?php if (!empty($msg)){ 
    echo '<script type="text/javascript">
            $(function(){
                alert("'.$msg.'");
            });
    </script>';
 }?>

<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo  Html::a(Yii::t('app', '新建权限组'), ['create'], ['class' => 'btn btn-success']); ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
    		'pager'=>array(
    				'firstPageLabel'=>'首页',
    				'lastPageLabel'=>'尾页',
    				'nextPageLabel'=>'下一页',
    				'prevPageLabel'=>'前一页',
    		),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'remark',
            [
                'attribute'=>'users',
                'value'=>function($data){
                    return common\service\AdminService::getGroupUsers($data->users);
                }
            ],
            
            /**
             * 判断按钮，对应格式如下，调用额时候注意。如果只有基本增删改功能则第二个参数不用填写 只用 common\service\AdminService::getGrideViewButtons($this）即可
             * 如果有自定义按钮则填写格式如下,array(0=>按钮标识，1=>按钮对应权限制,2=>按钮回调方法)
             */
           common\service\AdminService::getGrideViewButtons($this,
                    ([
                       [      
                         'updatepermission',
                         common\enum\PermissionEnum::UPDATE,
                         function($url,$model,$key){
                             $options=[
                                 'title'=>\Yii::t('app','Update Permisson'),
                                 'aria-label'=>\Yii::t('app','Update Permisson'),
                                 'data-pjax'=>0
                             ];
                             return \Yii\helpers\Html::a('<span class="glyphicon glyphicon-lock"></span>',$url,$options);
                         }
                     ],
                     /*如果有其他按钮格式如上方式填写，加在后面即可*/
                   ]),
                   '{view} {delete}'
            )
        ],
    ]); ?>
    

</div>
