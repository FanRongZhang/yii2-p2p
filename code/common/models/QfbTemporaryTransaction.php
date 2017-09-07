<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%temporary_transaction}}".
 *
 * @property integer $id
 * @property string $generated_time
 * @property string $member_id
 * @property string $ls_sn
 * @property string $sn
 * @property integer $operation_type
 * @property string $money
 * @property string $interest_money
 * @property string $currency
 * @property string $initiator_id
 * @property string $initiator_platform_id
 * @property string $receive_id
 * @property string $receive_platform_id
 * @property string $object_sn
 * @property string $original_sn
 * @property string $remark
 * @property string $bond_share
 * @property string $custom
 */
class QfbTemporaryTransaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%temporary_transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['generated_time', 'member_id', 'ls_sn', 'sn', 'money', 'interest_money', 'initiator_id', 'initiator_platform_id', 'receive_id', 'receive_platform_id', 'object_sn', 'original_sn'], 'required'],
            [['operation_type'], 'integer'],
            [['money', 'interest_money'], 'number'],
            [['remark', 'custom'], 'string'],
            [['generated_time', 'member_id', 'currency', 'initiator_id', 'initiator_platform_id', 'receive_id', 'receive_platform_id', 'bond_share', 'date'], 'string', 'max' => 32],
            [['ls_sn', 'sn', 'object_sn', 'original_sn'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'generated_time' => 'Generated Time',
            'member_id' => 'Member ID',
            'ls_sn' => 'Ls Sn',
            'sn' => 'Sn',
            'operation_type' => 'Operation Type',
            'money' => 'Money',
            'interest_money' => 'Interest Money',
            'currency' => 'Currency',
            'initiator_id' => 'Initiator ID',
            'initiator_platform_id' => 'Initiator Platform ID',
            'receive_id' => 'Receive ID',
            'receive_platform_id' => 'Receive Platform ID',
            'object_sn' => 'Object Sn',
            'original_sn' => 'Original Sn',
            'remark' => 'Remark',
            'bond_share' => 'Bond Share',
            'custom' => 'Custom',
            'date' => 'Date',
        ];
    }
}
