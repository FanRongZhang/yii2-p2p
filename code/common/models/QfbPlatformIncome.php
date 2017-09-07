<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%platform_income}}".
 *
 * @property integer $id
 * @property string $platform_name
 * @property string $sn
 * @property string $product_name
 * @property integer $member_id
 * @property string $remark
 * @property integer $complete_time
 * @property string $amount
 * @property string $balance
 * @property string $ls_sn
 * @property integer $type
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
            [['member_id', 'complete_time', 'type'], 'integer'],
            [['amount', 'balance'], 'number'],
            [['balance'], 'required'],
            [['platform_name', 'product_name'], 'string', 'max' => 30],
            [['sn', 'remark', 'ls_sn'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform_name' => 'Platform Name',
            'sn' => 'Sn',
            'product_name' => 'Product Name',
            'member_id' => 'Member ID',
            'remark' => 'Remark',
            'complete_time' => 'Complete Time',
            'amount' => 'Amount',
            'balance' => 'Balance',
            'ls_sn' => 'Ls Sn',
            'type' => 'Type',
        ];
    }
}
