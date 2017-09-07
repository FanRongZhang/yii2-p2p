<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\member\models\Meminfosearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Qfb Members');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Qfb Member'), ['create'], ['class' => 'btn btn-success']) ?>
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

            'id',
            'level',
            'relations',
            'r_member_id',
            'layer',
            // 'mobile',
            // 'account',
            // 'access_token',
            // 'channel_id',
            // 'last_access_time:datetime',
            // 'imei',
            // 'zf_pwd',
            // 'last_ip',
            // 'operator',
            // 'experience',
            // 'is_newer',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
