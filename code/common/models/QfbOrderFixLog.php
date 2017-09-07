<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_fix_log}}".
 *
 * @property integer $order_id
 * @property string $money
 * @property integer $from_member
 * @property integer $to_member
 * @property string $remark
 */
class QfbOrderFixLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_fix_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'from_member', 'to_member', 'remark'], 'required'],
            [['order_id', 'from_member', 'to_member'], 'integer'],
            [['money'], 'number'],
            [['remark'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => '定期理财订单表',
            'money' => '金额',
            'from_member' => '投资人id',
            'to_member' => '推荐人id',
            'remark' => '备注',
        ];
    }
}
