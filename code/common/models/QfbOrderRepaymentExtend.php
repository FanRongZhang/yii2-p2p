<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_repayment_extend}}".
 *
 * @property string $id
 * @property integer $order_id
 * @property string $sn
 * @property integer $type
 * @property integer $option_status
 * @property integer $create_time
 */
class QfbOrderRepaymentExtend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_repayment_extend}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'type', 'option_status', 'create_time'], 'integer'],
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
            'order_id' => 'Order ID',
            'sn' => 'Sn',
            'type' => 'Type',
            'option_status' => 'Option Status',
            'create_time' => 'Create Time',
        ];
    }
}
