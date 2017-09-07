<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%temporary_recharge}}".
 *
 * @property integer $id
 * @property string $recharge_time
 * @property string $member_id
 * @property string $zf_company
 * @property string $ls_sn
 * @property string $sn
 * @property integer $operation_type
 * @property string $money
 * @property string $currency
 * @property string $initiator_id
 * @property string $platform_id
 * @property string $no
 * @property string $bank_abbr
 * @property string $payment_type
 * @property string $account_money
 * @property string $remark
 */
class QfbTemporaryRecharge extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%temporary_recharge}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['recharge_time', 'member_id', 'zf_company', 'ls_sn', 'sn', 'money', 'initiator_id', 'platform_id', 'no', 'payment_type', 'account_money'], 'required'],
            [['operation_type'], 'integer'],
            [['money', 'account_money'], 'number'],
            [['remark'], 'string'],
            [['recharge_time', 'member_id', 'zf_company', 'ls_sn', 'sn', 'currency', 'initiator_id', 'platform_id', 'no', 'payment_type', 'date'], 'string', 'max' => 32],
            [['bank_abbr'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recharge_time' => 'Recharge Time',
            'member_id' => 'Member ID',
            'zf_company' => 'Zf Company',
            'ls_sn' => 'Ls Sn',
            'sn' => 'Sn',
            'operation_type' => 'Operation Type',
            'money' => 'Money',
            'currency' => 'Currency',
            'initiator_id' => 'Initiator ID',
            'platform_id' => 'Platform ID',
            'no' => 'No',
            'bank_abbr' => 'Bank Abbr',
            'payment_type' => 'Payment Type',
            'account_money' => 'Account Money',
            'remark' => 'Remark',
            'date' => 'Date',
        ];
    }
}
