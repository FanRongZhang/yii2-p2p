<?php
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\order\models\OrderMoneySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Qfb Orders');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel,'type'=>$type]); ?>

    <p>
        <!-- <?= Html::a(Yii::t('app','批量通过审核'), 'javascript:;', ['class' => 'btn btn-success is_pass']) ?> -->
        <!--<?= Html::a(Yii::t('app','批量拒绝审核'), 'javascript:;', ['class' => 'btn btn-success is_reject']) ?>-->
    </p>
    <div class="index" style="margin: 5px">
    <?php
    echo ExportMenu::widget([
    'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'sn',
            'member.account',
            'bank.username',
            'price',
            'fee',
            'money',

            [
                'attribute'=>'out_type',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getOutType($model->out_type);
                }
            ],

            [
                'attribute'=>'bank_type',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getChannel($model->bank_type);
                }
            ],

            'bank.name',
            'bank.no',

            [
                'attribute'=>'is_check',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getIsCheck($model->is_check);
                }
            ],

            [
                'attribute'=>'create_time',
                'content'=>function($model){
                    return $model->create_time ? date('Y-m-d H:i',$model->create_time) : '--';
                }
            ],
            [
                'attribute'=>'complete_time',
                'content'=>function($model){
                    return $model->complete_time ? date('Y-m-d H:i',$model->complete_time) : '--';
                }
            ],
        ],
        'filename'=>'TX'.date('Y-m-d',time())
    ]);
    ?>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'sn',
            'member.account',
            'bank.username',
            'price',
            'fee',
            'money',

            [
                'attribute'=>'out_type',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getOutType($model->out_type);
                }
            ],

            [
                'attribute'=>'bank_type',
                'content'=>function($model){
                    if(\Yii::$app->request->get('type') == 2){
                        return '无';
                    }

                    return \common\enum\OrderEnum::getChannel($model->bank_type);
                }
            ],

            'bank.name',
            'bank.no',

            [
                'attribute'=>'is_check',
                'content'=>function($model){
                    return \common\enum\OrderEnum::getIsCheck($model->is_check);
                }
            ],

            [
                'attribute'=>'create_time',
                'content'=>function($model){
                    return $model->create_time ? date('Y-m-d H:i',$model->create_time) : '--';
                }
            ],
            [
                'attribute'=>'complete_time',
                'content'=>function($model){
                    return $model->complete_time ? date('Y-m-d H:i',$model->complete_time) : '--';
                }
            ],
            /*
            [
                'class' => 'yii\grid\ActionColumn',
                'header'=>'操作',
                'template' => '{remove} {pass}',
                'buttons'=>[
                    'remove'=>function($url,$model){
                        if($model->is_check==0)
                            return Html::a('<span class="glyphicon glyphicon-remove"></span>撤销', 'javascript:;' , ['id' => $model->id,'class' =>'NotDisplayOrder'] );
                    },
                    'pass'=>function($url,$model){
                        if($model->is_check==0)
                            return Html::a('<span class="glyphicon glyphicon-ok"></span>通过', 'javascript:;' , ['id' => $model->id,'class' =>'YesDisplayOrder'] );
                    },

                ],
            ],
             */
        ],
    ]); ?>

</div>

<script type="text/javascript">

    $(function(){
        $(".NotDisplayOrder").click(function(){
            var id = $(this).attr("id");
            if(confirm("确定撤销所选订单？")){
                $.post('/order/order-money/remove',
                    {'id' : id},
                    function(data){
                        if(data.code==200){
                            alert(data.msg);
                            $('#'+id).parents('tr').remove();
                        }else{
                            alert(data.msg);
                        }
                    },'json');
            }
        });

        $(".YesDisplayOrder").click(function(){
            var id = $(this).attr("id");
            if(confirm("确定审核通过所选订单？")){
                $.post('/order/order-money/pass',
                    {'id' : id},
                    function(data){
                        if(data.code==200){
                            alert(data.msg);
                            $('#'+id).parents('tr').remove();
                        }else{
                            alert(data.msg);
                        }
                    },'json');
            }
        });

        <?php
        /*
        $(".is_pass").click(function(){
            if(confirm("确定审核通过查询的订单？")){
                $.get("/order/order-money/audit?type=2&sn=<?//=$data['sn']?>&account=<?=$data['account']?>&username&<?=$data['username']?>&out_type&<?=$data['out_type']?>&bank_type=<?=$data['bank_type']?>&is_check=<?=$data['is_check']?>&create_time=<?=$data['create_time']?>&create_time_end=<?=$data['create_time_end']?>&complete_time=<?=$data['complete_time']?>&complete_time_end=<?=$data['complete_time_end']?>",

                    function(data){
                        if(data.code==200){
                            alert(data.msg);
                            location.reload();
                        }else{
                            alert(data.msg);
                        }
                    },'json');
            }
        });
        */
        ?>

    });
</script>
