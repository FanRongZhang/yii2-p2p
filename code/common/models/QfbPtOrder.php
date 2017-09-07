<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%pt_order}}".
 *
 * @property integer $id
 * @property string $sn
 * @property string $pt_number
 * @property string $price
 * @property integer $is_check
 * @property integer $create_time
 * @property integer $complete_time
 * @property integer $sorts
 * @property string $fee
 * @property string $money
 * @property integer $bank_type
 * @property integer $out_type
 */
class QfbPtOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pt_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sn', 'pt_number'], 'required'],
            [['price', 'fee', 'money'], 'number'],
            [['is_check', 'create_time', 'complete_time', 'sorts', 'bank_type', 'out_type'], 'integer'],
            [['sn', 'pt_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sn' => '订单编号',
            'pt_number' => '平台账户名称',
            'price' => '金额',
            'is_check' => 'Is Check',
            'create_time' => '创建时间',
            'complete_time' => '完成时间',
            'sorts' => '类型',
            'fee' => '手续费',
            'money' => '实际金额',
            'bank_type' => 'Bank Type',
            'out_type' => 'Out Type',
        ];
    }
}
