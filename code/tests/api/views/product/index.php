<!--项目详情-->
<div class="qfb-cencer">
    <!--qfb-text-content begin-->
    <ul class="qfb-text-content">
        <li class="qfb-headings">
            <p class="qfb-text">项目金额</p>
            <span class="qfb-text-right"><?=$model->stock_money?>元</span>
        </li>
        <li class="qfb-headings">
            <p class="qfb-text">发售时间</p>
            <span class="qfb-text-right"><?=date("Y年m月d日 H:i", $model->start_time)?></span>
        </li>
        <li class="qfb-headings">
            <p class="qfb-text">起投金额</p>
            <span class="qfb-text-right"><?=$model->min_money?>元</span>
        </li>
        <li class="qfb-headings">
            <p class="qfb-text">投资上限</p>
            <span class="qfb-text-right"><?=$model->max_money?>元</span>
        </li>
        <li class="qfb-headings">
            <p class="qfb-text">投资限制</p>
            <span class="qfb-text-right"><?=$limit_str?></span>
        </li>
        <li class="qfb-headings">
            <p class="qfb-text">计息时间</p>
            <span class="qfb-text-right"><?=$profit_day_str?></span>
        </li>
        <li class="qfb-headings">
            <p class="qfb-text">结束时间</p>
            <span class="qfb-text-right"><?=$end_time?></span>
        </li>
        <li class="qfb-headings">
            <p class="qfb-text">收益方式</p>
            <span class="qfb-text-right"><?=$profit_type_str?></span>
        </li>
    </ul>
    <div class="qfb-title">
        <h3>产品介绍</h3>
        <p><?=$model->product_detail ? $model->product_detail->detail : ''?></p>
    </div>
    <!--qfb-text-content end-->
</div>