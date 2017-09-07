<?php
namespace api\controllers;

use yii;
use yii\web\Controller;
use yii\web\Response;
use common\models\QfbProduct;
use common\enum\ProductEnum;
use api\common\helpers\ReseponseCode as Code;

/**
 * 产品h5页面控制器
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class ProductController extends Controller
{
    /**
     * 产品详情
     */
    public function actionIndex($id)
    {
        $model = QfbProduct::find()->joinWith('product_detail')
        ->where(['qfb_product.id' => $id])->one();
        if ($model) {
            //起息日
            $profit_day_str = ProductEnum::getProfitDay($model->profit_day);
            //收益方式
            $profit_type_str = ProductEnum::getProfitType($model->profit_type);
            //结束时间
            $end_time = ProductEnum::getEndTime($model->profit_day, $model->invest_day);
            //投资限制，可用代金券，加息券情况
            $limit_str = "";
            if ($model->can_rate_ticket || $model->can_money_ticket) {
                if ($model->can_rate_ticket) {
                    $limit_str .= "可使用加息券";
                }
                if ($model->can_money_ticket) {
                    if (strlen($limit_str)) {
                        $limit_str .= ";可使用代金券";
                    } else {
                        $limit_str .= "可使用代金券";
                    }
                }
            } else {
                $limit_str = "只能使用余额或银行卡";
            }
            return $this->render('index', compact('model', 'profit_day_str'
                , 'profit_type_str', 'limit_str', 'end_time'));
        }
    }
}
