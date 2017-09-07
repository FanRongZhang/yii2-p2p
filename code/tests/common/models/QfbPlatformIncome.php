<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%platform_income}}".
 *
 * @property integer $id
 * @property string $sn
 * @property integer $member_id
 * @property string $remark
 * @property integer $complete_time
 * @property string $amount
 * @property string $balance
 */
class QfbPlatformIncome extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%platform_income}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sn', 'member_id', 'balance'], 'required'],
            [['member_id', 'complete_time'], 'integer'],
            [['amount', 'balance'], 'number'],
            [['sn', 'remark'], 'string', 'max' => 50],
            [['product_name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sn' => 'Sn',
            'product_name' => 'Product Name',
            'member_id' => 'Member ID',
            'remark' => 'Remark',
            'complete_time' => 'Complete Time',
            'amount' => 'Amount',
            'balance' => 'Balance',
        ];
    }
}
