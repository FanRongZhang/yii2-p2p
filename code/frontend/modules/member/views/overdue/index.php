<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = '还款';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/datepicker/laydate.js');

$columns = [
    [
        'label'=>'标的名称',
        'format'=>'raw',
        'value' => function($model){
            return Html::a($model->product_name, 'javascript:void(0)', ['title' => '查看', 'onclick'=> 'product_detail('.$model->product_id.')']);
        }
    ],
    [
        'header'  => '逾期金额',
        'attribute' => 'overdue_money',
        'content' => function($model){
            return $model->o_money+$model->o_interest;
        }
    ],
    [
        'header'  => '还款方式',
        'attribute' => 'profit_type',
        'content' => function($model){
            if($model->profit_type == 1){
                $profit = '到期还本付息';
            }elseif($model->profit_type == 2){
                $profit = '按月等额付息，到期还本';
            }elseif($model->profit_type == 3){
                $profit = '按日等额付息，到期还本';
            }elseif($model->profit_type == 4){
                $profit = '按月等额本息';
            }elseif($model->profit_type == 5){
                $profit = '按日等额本息';
            }elseif($model->profit_type == 6){
                $profit = '按月等额还本，到期付息';
            }elseif($model->profit_type == 7){
                $profit = '按日等额还本，到期付息';
            }

            return $profit;
        },
    ],
    [
        'header'  => '逾期天数',
        'attribute' => 'overdue_day',
    ],
    [
        'header'  => '罚息比例',
        'attribute' => 'overdue_day',
        'content' => function(){
            return (100*Yii::$app->params['overdue_interest']).'%';
        }
    ],
    [
        'header'  => '罚息金额',
        'attribute' => 'o_interest',
        'content' => function($model){
            return $model->overdue_money;
        }
    ],
    [
        'header'  => '状态',
        'attribute' => 'overdue_status',
        'content' => function($model){
            if($model->overdue_status == 2){
                $check =  '已还款';
            }elseif($model->overdue_status == 0){
                $check =  '未还款';
            }elseif($model->overdue_status == 1){
                $check =  '待确定';
            }else{
                $check =  '还款异常';
            }

            return $check;
        },
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => '操作',
        'template' => ' {edit} ',
        //'options' => ['width' => '200px;'],
        'buttons' => [
            'edit' => function ($url, $model, $key) {
                $isCommutation = \common\models\QfbOrderRepayment::find()
                    ->where(['product_id'=>$model->product_id, 'is_overdue'=>1,])
                    ->andFilterWhere(['in', 'status', [0,1]])->one();
                if($model->overdue_status == 0 && !$isCommutation){
                    return Html::a('<i class="fa fa-edit"></i> 还款', 'javascript:void(0)', [
                        'title' => Yii::t('app', '还款'),
                        'class' => 'btn btn-xs yellow',
                        'onclick' => 'repayment('.$model->overdue_id.')'
                    ]);
                }
            },

        ],
    ],
];
?>
<div class="fr memberright">
    <div class="member-main-box1">
        <h2 class="member-total-tit">逾期</h2>
        <div class="member-tment-main">
            <div class="member-stais-listmy ">
                <div class="con-prdpages member-con-tents">
                    <ul class="tabs clearfix">
                        <li class="pr-active"><a href="#tab1">全部</a> </li>
                    </ul>
                    <div class="ordertop orderright">

                    </div>
                </div>
                <div class="tab_container mar15">
                    <?php  Pjax::begin();?>
                    <div id="tab1" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box"><div class="tdb-page clearfix"><div class="b-count" >{pager}</div>{summary}</div></div>',
                            /* 配置摘要 */
//                                        'summaryOptions' => ['class' => 'pagination'],
                            /* 配置分页样式 */
                            'pager' => [
                                'options' => ['class'=>'pagination','style'=>'visibility: visible;'],
                                'nextPageLabel' => '下一页',
                                'prevPageLabel' => '上一页',
                                'firstPageLabel' => '第一页',
                                'lastPageLabel' => '最后页'
                            ],
                            /* 定义列表格式 */
                            'columns' => $columns,
                        ]); ?>

                    </div>
                    <?php  Pjax::end();?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="with-taste">
    <div class="with-con-box">
        <div class="withbox1"><i class="officicon with-iconclose"></i></div>
        <div class="withbox2">
            <div class="withbox2-1">还款金额：<span class="black1 f18 orange2">50000.00</span>元</div>
            <div class="withbox2-2"><a href="javascript:void" class="close-sucess">确定</a> <a href="javascript:void " class="close-concel">取消</a></div>
        </div>
    </div>
</div>
<div class="d-bodybg dm-popup-box" id="ShowNewUserBox"></div>
<script>

</script>

<?php $this->beginBlock('test'); ?>

function repayment(id)
{
    var url = "<?php echo Url::to(['overdue/option']);?>";
    $.post(
        url,
        {id:id},

        function(res){
            if(res.status == 1){
                $('.withbox2-1 .black1').text(res.data.repayment_money);
                $('.withbox2-2').children('.close-sucess').attr('href', '/member/overdue/repayment?id='+id);
                $('.with-taste,.dm-popup-box').show();
            }else{
                alert(res.msg);
            }
        },'json'
    );
    $(function(){
        $('.with-taste').hide();
        $(".close-concel").click(function(){
        $('.with-taste,.dm-popup-box').hide();
        });
        $(".with-iconclose").click(function(){
        $('.with-taste,.dm-popup-box').hide();
        });
    });
    }

    function product_detail(id){
    window.location.href = '/index/index/detail?id='+id;
    }


<?php $this->endBlock()  ?>
    <!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>