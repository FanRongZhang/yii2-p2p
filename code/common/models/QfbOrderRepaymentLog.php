<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_repayment_log}}".
 *
 * @property integer $order_repayment
 * @property integer $repayment_type
 * @property string $total_acount
 * @property string $out_account_id
 * @property string $in_account_id
 * @property string $remark
 * @property integer $create_time
 */
class QfbOrderRepaymentLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    protected  static $postfix = '';

    public function __construct($_order_repayment_id)
    {
//        if($_order_repayment_id != 'order_repayment_log'){
//            $number = $_order_repayment_id%10;
//            self::$postfix = '_t'.$number;
//        }
    }

    public static function tableName()
    {
//        if(!empty(self::$postfix)){
//           return '{{%order_repayment_log'.self::$postfix.'}}';
//        }
        return '{{%order_repayment_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_repayment_id'], 'required'],
            [['order_repayment_id', 'repayment_type', 'create_time'], 'integer'],
            [['money', 'interest_money', 'other_money', 'total_money'], 'number'],
            [['out_account_id', 'in_account_id'], 'string', 'max' => 20],
            [['sn'], 'string', 'max' => 30],
            [['remark'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_repayment_id' => 'Order Repayment Id',
            'sn' => 'sn',
            'repayment_type' => 'Repayment Type',
            'money' => 'money',
            'interest_money' => 'interest_money',
            'other_money' => 'other_money',
            'total_money' => 'total_money',
            'out_account_id' => 'Out Account ID',
            'in_account_id' => 'In Account ID',
            'remark' => 'Remark',
            'create_time' => 'Create Time',
        ];
    }


    /*
     * 获取日志
     * @params is array $parmas[字段名称] = 值
     * */
    public function get_log($parmas){

        if(!is_array($parmas)){
            return false;
        }

        $data = self::find()->where($parmas)->asArray()->all();
        return $data;
    }
}
