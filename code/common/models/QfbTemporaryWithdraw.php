<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%temporary_withdraw}}".
 *
 * @property integer $id
 * @property string $withdraw_time
 * @property string $member_id
 * @property string $ls_sn
 * @property string $sn
 * @property integer $operation_type
 * @property string $money
 * @property string $currency
 * @property string $initiator_id
 * @property string $platform_id
 * @property string $no
 * @property string $bank_abbr
 * @property string $account_money
 * @property string $remark
 * @property string $status
 * @property string $way
 * @property string $advance
 * @property string $speed_type
 * @property string $withdaw_type
 * @property string $date
 */
class QfbTemporaryWithdraw extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%temporary_withdraw}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['withdraw_time', 'member_id', 'ls_sn', 'sn', 'money', 'initiator_id', 'platform_id', 'no', 'account_money'], 'required'],
            [['operation_type'], 'integer'],
            [['money', 'account_money', 'advance'], 'number'],
            [['remark'], 'string'],
            [['withdraw_time', 'member_id', 'currency', 'initiator_id', 'platform_id', 'status', 'way', 'speed_type', 'withdaw_type', 'date'], 'string', 'max' => 32],
            [['ls_sn', 'sn', 'no'], 'string', 'max' => 30],
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
            'withdraw_time' => 'Withdraw Time',
            'member_id' => 'Member ID',
            'ls_sn' => 'Ls Sn',
            'sn' => 'Sn',
            'operation_type' => 'Operation Type',
            'money' => 'Money',
            'currency' => 'Currency',
            'initiator_id' => 'Initiator ID',
            'platform_id' => 'Platform ID',
            'no' => 'No',
            'bank_abbr' => 'Bank Abbr',
            'account_money' => 'Account Money',
            'remark' => 'Remark',
            'status' => 'Status',
            'way' => 'Way',
            'advance' => 'Advance',
            'speed_type' => 'Speed Type',
            'withdaw_type' => 'Withdaw Type',
            'date' => 'Date',
        ];
    }
}
