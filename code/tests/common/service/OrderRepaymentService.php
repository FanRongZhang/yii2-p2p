<?php
namespace common\service;

use common\models\QfbOrderRepayment;
use Yii;

class OrderRepaymentService extends BankService
{


    public function getOrderRepayment($id)
    {
        $orderRepaymentModel = new QfbOrderRepayment();

        $data = $orderRepaymentModel::find()
            ->with('product')
            ->where([QfbOrderRepayment::tableName().'id'=>$id])
            ->one();

        return $data;

    }

}