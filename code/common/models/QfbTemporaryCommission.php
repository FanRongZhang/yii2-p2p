<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%temporary_commission}}".
 *
 * @property integer $id
 * @property string $withhold_time
 * @property string $member_id
 * @property string $sn
 * @property integer $operation_type
 * @property string $initiator_platform_id
 * @property string $receive_platform_id
 * @property string $money
 * @property string $currency
 * @property string $product_sn
 * @property string $remark
 * @property string $ls_sn
 */
class QfbTemporaryCommission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%temporary_commission}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['withhold_time', 'member_id', 'sn', 'initiator_platform_id', 'receive_platform_id', 'money', 'product_sn', 'ls_sn'], 'required'],
            [['operation_type'], 'integer'],
            [['money'], 'number'],
            [['remark'], 'string'],
            [['withhold_time', 'member_id', 'sn', 'initiator_platform_id', 'receive_platform_id', 'currency', 'product_sn', 'ls_sn', 'date'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'withhold_time' => 'Withhold Time',
            'member_id' => 'Member ID',
            'sn' => 'Sn',
            'operation_type' => 'Operation Type',
            'initiator_platform_id' => 'Initiator Platform ID',
            'receive_platform_id' => 'Receive Platform ID',
            'money' => 'Money',
            'currency' => 'Currency',
            'product_sn' => 'Product Sn',
            'remark' => 'Remark',
            'ls_sn' => 'Ls Sn',
            'date' => 'Date',
        ];
    }
}
