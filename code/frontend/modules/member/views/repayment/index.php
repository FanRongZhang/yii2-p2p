<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use common\toolbox\Tool;

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
        'header'  => '期数',
        'attribute' => 'periods',
    ],
    [
        'header'  => '还款本金',
        'attribute' => 'money',
    ],
    [
        'header'  => '还款利息',
        'attribute' => 'interest',
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
        'header'  => '还款截止日期',
        'attribute' => 'repayment_end_time',
        'content'  => function($model){
            if($model->periods == 1 && $model->is_end == 1){
                if($model->profit_day == 10){
                    $profitDay = $model->finish_time+$model->invest_day*Tool::$dayTime;
                }elseif($model->profit_day == 11){
                    $profitDay = $model->finish_time+($model->invest_day+1)*Tool::$dayTime;
                }elseif($model->profit_day == 20){
                    $profitDay = $model->finish_time+($model->invest_day)*Tool::$dayTime;
                }else{
                    $profitDay = $model->finish_time+($model->invest_day+1)*Tool::$dayTime;
                }
            }else{
                if($model->profit_day == 10){
                    $profitDay = $model->finish_time+Tool::$dayTime*(($model->periods-1)*Tool::$periodsDay+$model->invest_day);
                }elseif($model->profit_day == 11){
                    $profitDay = $model->finish_time+Tool::$dayTime*(($model->periods-1)*Tool::$periodsDay+$model->invest_day+1);
                }elseif($model->profit_day == 20){
                    $profitDay = $model->finish_time+Tool::$dayTime*(($model->periods-1)*Tool::$periodsDay+$model->invest_day);
                }else{
                    $profitDay = $model->finish_time+Tool::$dayTime*(($model->periods-1)*Tool::$periodsDay+$model->invest_day+1);
                }
            }

            return $model->finish_time ? date('Y-m-d', $profitDay) : '';
        }
    ],
    [
        'header'  => '是否逾期',
        'content'  => function($model){
            if($model->is_overdue == 1){
                return '已逾期';
            }else{
                return '未逾期';
            }
        }
    ],
    [
        'header'  => '还款状态',
        'attribute' => 'status',
        'content' => function($model){
            if($model->status == 2){
                $check =  '已还款';
            }elseif($model->status == 1){
                $check =  '已还款待确定';
            }elseif($model->status == 0){
                $check =  '未还款';
            }elseif($model->status == 9){
                $check =  '无效订单';
            }else{
                $check =  '还款失败';
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

                $start_time = time();
                if($model->periods == 1 && $model->is_end == 1){
                    if($model->profit_day == 10){
                        $profitDay = $model->finish_time+($model->invest_day)*Tool::$dayTime;
                        $startTime = $model->finish_time;
                    }elseif($model->profit_day == 11){
                        $profitDay = $model->finish_time+($model->invest_day+1)*Tool::$dayTime;
                        $startTime = $model->finish_time+Tool::$dayTime;
                    }elseif($model->profit_day == 20){
                        $profitDay = $model->finish_time+($model->invest_day)*Tool::$dayTime;
                        $startTime = $model->finish_time;
                    }else{
                        $profitDay = $model->finish_time+($model->invest_day+1)*Tool::$dayTime;
                        $startTime = $model->finish_time+Tool::$dayTime;
                    }
                }else{
                    if($model->profit_day == 10){
                        $profitDay = $model->finish_time+Tool::$dayTime*(($model->periods-1)*Tool::$periodsDay+$model->invest_day);
                        $startTime = $model->finish_time+(Tool::$periodsDay)*Tool::$dayTime*($model->periods-1);
                    }elseif($model->profit_day == 11){
                        $profitDay = $model->finish_time+Tool::$dayTime*(($model->periods-1)*Tool::$periodsDay+$model->invest_day+1);
                        $startTime = $model->finish_time+(Tool::$periodsDay*($model->periods-1)+1)*Tool::$dayTime;
                    }elseif($model->profit_day == 20){
                        $profitDay = $model->finish_time+Tool::$dayTime*(($model->periods-1)*Tool::$periodsDay+$model->invest_day);
                        $startTime = $model->finish_time+(Tool::$periodsDay)*Tool::$dayTime*($model->periods-1);
                    }else{
                        $profitDay = $model->finish_time+Tool::$dayTime*(($model->periods-1)*Tool::$periodsDay+$model->invest_day+1);
                        $startTime = $model->finish_time+(Tool::$periodsDay*($model->periods-1)+1)*Tool::$dayTime;
                    }
                }

                $profitTime = Tool::endTime($profitDay);
                $startTime = Tool::endTime($startTime);
                if($model->status == 0 && $model->is_commutation == 0 && $start_time <= $profitTime && $start_time >= $startTime){
                    return Html::a('<i class="fa fa-edit"></i> 还款', 'javascript:void(0)', [
                        'title' => Yii::t('app', '还款'),
                        'class' => 'btn btn-xs yellow',
                        'onclick' => 'repayment('.$model->id.')'
                    ]);
                }
            },

        ],
    ],
];
?>
<div class="fr memberright">
    <div class="member-main-box1">
        <h2 class="member-total-tit">还款</h2>
        <div class="member-tment-main">
            <div class="member-stais-listmy ">
                <div class="con-prdpages member-con-tents">
                    <ul class="tabs clearfix">
                        <li class="pr-active"><a href="#tab1">全部</a> </li>
                        <li><a href="#tab2">参与中</a></li>
                        <li><a href="#tab3">已结束</a></li>
                    </ul>
                    <div class="ordertop orderright">
                        <?= $this->render('_search', ['model' => $model]); ?>
                    </div>
                </div>
                <div class="tab_container mar15">
                    <?php  Pjax::begin();?>
                    <div id="tab1" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider0,
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
                    <?php  Pjax::begin();?>
                    <div id="tab2" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider1,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box"><div class="tdb-page clearfix"><div class="b-count">{pager}</div>{summary}</div></div>',
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
                    <?php  Pjax::begin();?>
                    <div id="tab3" class="tab_content ">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider2,
                            'options' => ['class' => 'tab-total-mains'],
                            /* 表格配置 */
                            'tableOptions' => ['style'=>'width:100%'],
                            /* 重新排版 摘要、表格、分页 */
                            'layout' => '{items}<div class="page-box"><div class="tdb-page clearfix"><div class="b-count">{pager}</div>{summary}</div></div>',
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

function repayment(repay_id)
{
    var url = "<?php echo Url::to(['repayment/option']);?>";
    $.post(
        url,
        {repay_id:repay_id},

        function(res){
            if(res.status == 1){
                $('.withbox2-1 .black1').text(res.data.repayment_money);
                $('.withbox2-2').children('.close-sucess').attr('href', '/member/repayment/repayment?repay_id='+repay_id);
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