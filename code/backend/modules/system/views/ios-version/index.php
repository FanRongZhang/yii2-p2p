<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\models\QfbVersion;
use common\enum\PermissionEnum;
use common\service\AdminService;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\system\models\IosVersionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'ios版本');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        //判断是否拥有创建按钮权限
        if(AdminService::hasPermision($this,PermissionEnum::ADD))
            echo Html::a(Yii::t('app', '+ 添加版本'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
</div>

<?php
    $gridColumns = [
            ['class' => 'yii\grid\SerialColumn'],

            'ver_code',

            'ver_name',

            ['attribute' => 'create_time','content' => function($data) {
                    return date("Y-m-d H:i:s",$data->create_time);
                }
            ],

            'content:ntext',

            ['attribute' => 'is_force','content' => function($data) {
                    return $data->is_force == 0 ? '建议更新' : '强制更新';
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
                            //查询最新的一条记录
                            $new = QfbVersion::find()->where(['=','type',2])->orderBy('create_time desc')->limit(1)->one();
                            if ($model->id == $new->id) {
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>编辑', '/system/ios-version/update?id='.$model->id, $options);
                            }
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




