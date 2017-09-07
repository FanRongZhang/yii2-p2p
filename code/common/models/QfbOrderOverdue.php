<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_overdue}}".
 *
 * @property string $id
 * @property integer $product_id
 * @property string $sn
 * @property integer $member_id
 * @property integer $to_member_id
 * @property string $money
 * @property string $interest
 * @property string $overdue_money
 * @property string $repay_money
 * @property integer $status
 * @property integer $create_time
 * @property integer $complete_time
 */
class QfbOrderOverdue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_overdue}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'member_id', 'to_member_id', 'status', 'create_time', 'complete_time', 'overdue_day'], 'integer'],
            [['money', 'interest', 'overdue_money', 'repay_money'], 'number'],
            [['sn'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'sn' => 'Sn',
            'member_id' => 'Member ID',
            'to_member_id' => 'To Member ID',
            'money' => 'Money',
            'interest' => 'Interest',
            'overdue_money' => 'Overdue Money',
            'repay_money' => 'Repay Money',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'complete_time' => 'Complete Time',
            'overdue_day' => 'Overdue Day'
        ];
    }
}
