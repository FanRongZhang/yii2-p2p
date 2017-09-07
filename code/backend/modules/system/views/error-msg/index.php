<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\enum\OrderEnum;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\system\models\ErrorMsgSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '报错类型');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a(Yii::t('app', '+ 添加报错'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

</div>
<?php
$gridColumns = [
        ['class' => 'yii\grid\SerialColumn'],

        ['attribute' => 'channel_id','content' => function($data) {
                return OrderEnum::getChannel($data->channel_id);
            }
        ],

        'code',

        'msg',

        ['attribute' => 'create_time','content' => function($data) {
                return date("Y-m-d H:i:s",$data->create_time);
            }
        ],

        common\service\AdminService::getGrideViewButtons($this,
            ([
                [
                    'update',
                    PermissionEnum::UPDATE,
                    function($url,$model,$key){
                        $options=[
                            'title'=>Yii::t('app','btn_update'),
                            'aria-label'=>Yii::t('app','btn_update'),
                            'data-pjax'=>0
                        ];
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>编辑', '/system/error-msg/update?id='.$model->id, $options);
                    }
        
                ],
            ]),'{update}'
        )
];

$menuColumns = $gridColumns;
unset($menuColumns[count($menuColumns)-1]);

// Renders a export dropdown menu
echo ExportMenu::widget([
    'dataProvider' => $dataProvider,
    'columns' => $menuColumns
]);

// You can choose to render your own GridView separately
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'pager'=>array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'尾页',
            'nextPageLabel'=>'下一页',
            'prevPageLabel'=>'前一页',
    ),
    //'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'export' => false,
]);


