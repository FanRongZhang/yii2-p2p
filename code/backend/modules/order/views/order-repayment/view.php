<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\QfbProduct;
use common\models\QfbMember;
use common\models\QfbMemberInfo;
use common\enum\OrderEnum;
use yii\widgets\LinkPager;
?>
<div class="detail-view">

    <p>
        <?= Html::a(Yii::t('app','返回列表'), ['index'], ['class' => 'btn btn-primary']) ?>       
    </p>

    <table class="table table-striped table-bordered"><thead>
        <tr><th>序号</th><th>投资订单号</th><th>会号账号</th><th>本金</th><th>利息</th><th>结果</th></tr>
        </thead>
        <tbody>
        <?php foreach($model as $key=>$val)
        {

            $interest = $val['invest_day']*$val['day_interest'];


            echo '<tr data-key="99"><td>'.$key.'</td><td>'.$val->sn.'</td><td>'.$val->account.'</td><td>'.$val->pay_money.'</td>';
            if(isset($interest)){
                echo '<td>'.$interest.'</td>';
            }else{
                echo '<td>还未生成利息</td>';
            }

            if($val->option_status == 20){
                echo '<td>还款中</td></tr>';
            }elseif($val->option_status == 21){
                echo '<td>还款成功</td></tr>';
            }elseif($val->option_status == 29){
                echo '<td>还款异常</td></tr>';
            }else{
                echo '<td>--</td></tr>';
            }

        }
        ?>


        </tbody>
    </table>



    <?= LinkPager::widget(['pagination' => $pages]); ?>

</div>
