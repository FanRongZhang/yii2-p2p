<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\enum\ProductEnum;
?>
<div class="detail-view">

    <p>
        <?php if ($model->status === 0) {?>           
            <?= Html::a(Yii::t('app','编辑'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php }?>
        <?= Html::a(Yii::t('app','返回列表'), ['index'], ['class' => 'btn btn-primary']) ?>
        <?php if ($model->status === 2 && $model->end_time > time()) {?>           
            <?= Html::a(Yii::t('app','放款'), ['loan', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php }?>
    </p>

    <table id="w0" class="table table-striped table-bordered detail-view">
        <tbody>
            <tr><th>产品id</th><td><?php echo $model->id; ?></td></tr>
            <tr><th>产品编号</th><td><?php echo $model->sn; ?></td></tr>
            <tr><th>产品类型</th><td><?php echo ProductEnum::getTip($model->product_type); ?></td></tr>
            <tr><th>产品名称</th><td><?php echo $model->product_name; ?></td></tr>
            <tr><th>分类id</th><td><?php echo $model->category_id; ?></td></tr>
            <tr><th>起投金额(元)</th><td><?php echo $model->min_money; ?></td></tr>
            <tr><th>投资上限金额(元)</th><td><?php echo $model->max_money; ?></td></tr>
            <tr><th>递增金额(元)</th><td><?php echo $model->step_money; ?></td></tr>
            <tr><th>已投金额(元)</th><td><?php echo $model->has_money; ?></td></tr>
            <tr><th>项目总额(元)</th><td><?php echo $model->stock_money; ?></td></tr>
            <tr><th>年化收益率(%)</th><td><?php echo $model->year_rate; ?></td></tr>
            <tr><th>分润比例(%)</th><td><?php echo $model->profit_settings->recommond_rate; ?></td></tr>
            <tr><th>管理奖比例(%)</th><td><?php echo $model->profit_settings->manage_rate; ?></td></tr>
            <tr><th>推荐代理奖(%)</th><td><?php echo $model->profit_settings->agent_rate; ?></td></tr>
            <tr><th>是否可用加息券</th><td><?php echo $model->can_rate_ticket === 1 ? '是' : '否'; ?></td></tr>
            <tr><th>是否可用代金券</th><td><?php echo $model->can_money_ticket === 1 ? '是' : '否'; ?></td></tr>
            <tr><th>收益方式</th><td><?php echo ProductEnum::getProfitType($model->profit_type); ?></td></tr>
            <tr><th>是否新手</th><td><?php echo $model->is_newer === 1 ? '是' : '否'; ?></td></tr>
            <tr><th>锁定期(天)</th><td><?php echo $model->lock_day; ?></td></tr>
            <tr><th>投资期限(天)</th><td><?php echo $model->invest_day; ?></td></tr>
            <tr><th>起息日</th><td><?php echo ProductEnum::getProfitDay($model->profit_day); ?></td></tr>
            <tr><th>状态</th><td><?php echo ProductEnum::getStatus($model->status); ?></td></tr>
            <tr><th>创建时间</th><td><?php echo date("Y-m-d H:i:s",$model->create_time); ?></td></tr>
            <tr><th>开始时间</th><td><?php echo !empty($model->start_time) ? date("Y-m-d H:i:s",$model->start_time) : ''; ?></td></tr>
            <tr><th>筹集到期时间</th><td><?php echo date("Y-m-d H:i:s",$model->end_time); ?></td></tr>
            <tr><th>筹集完成时间</th><td><?php echo !empty($model->finish_time) ? date("Y-m-d H:i:s",$model->finish_time) : ''; ?></td></tr>
            <tr><th>是否在首页显示</th><td><?php echo $model->is_index === 1 ? '是' : '否'; ?></td></tr>
            <tr><th>隐藏</th><td><?php echo $model->is_hidden === 1 ? '是' : '否'; ?></td></tr>
            <tr><th>产品简述</th><td><?php echo $model->product_detail->content; ?></td></tr>
            <tr><th>产品详情</th><td><?php echo !empty($detailmodel->detail) ? strip_tags($detailmodel->detail) : ''; ?></td></tr>
            <tr><th>协议</th><td><?php echo $ag_str; ?></td></tr>
            <tr><th>已发放平台收益(元)</th><td><?php echo $model->credit_incomme; ?></td></tr>
            <tr><th>平台收益率(%)</th><td><?php echo $model->platform_income_rate; ?></td></tr>
            <tr><th>已放款金额</th><td><?php echo $model->actual_credit_money; ?></td></tr>
            <tr><th>收款人账号</th><td><?php echo $data['account']; ?></td></tr>
            <tr><th>收款人姓名</th><td><?php echo $data['username']; ?></td></tr>
            <tr><th>收款人身份证号</th><td><?php echo $data['card_no']; ?></td></tr>
            <tr><th>收款人银行卡号</th><td><?php echo $data['bankno']; ?></td></tr>
            <tr><th>收款人所属银行</th><td><?php echo $data['bankname']; ?></td></tr>
            <?php if($model->warranty_type == 1){ ?>
                <tr><th>牌照号</th><td><?php echo $warranty['plate_number']; ?></td></tr>
                <tr><th>型号</th><td><?php echo $warranty['model']; ?></td></tr>
                <tr><th>发动机号</th><?php echo $warranty['engine_number']; ?><td></td></tr>
                <tr><th>车架号</th><td><?php echo $warranty['vin']; ?></td></tr>
                <tr><th>合同编码</th><td><?php echo $warranty['contract_number']; ?></td></tr>
            <?php }elseif($model->warranty_type == 2){ ?>
                <tr><th>合同编码</th><td><?php echo $warranty['contract_number']; ?></td></tr>
            <?php }elseif($model->warranty_type == 3){ ?>
                <tr><th>保证人</th><td><?php echo $warranty['warrantor']; ?></td></tr>
                <tr><th>身份证号码</th><td><?php echo $warranty['id_card']; ?></td></tr>
                <tr><th>联系电话</th><td><?php echo $warranty['mobile']; ?></td></tr>
                <tr><th>保证方式</th><td><?php echo $warranty['guarantee_way'] == 1 ? '一般保证' : '连带责任保'; ?></td></tr>
                <tr><th>合同编码</th><td><?php echo $warranty['contract_number']; ?></td></tr>
            <?php } ?>
        </tbody>
    </table>



    
</div>
