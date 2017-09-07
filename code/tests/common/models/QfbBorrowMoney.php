<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%borrow_money}}".
 *
 * @property integer $id
 * @property integer $type
 * @property string $money
 * @property integer $sey
 * @property string $guarantee
 * @property string $purpose
 * @property string $name
 * @property integer $tel
 * @property integer $status
 * @property integer $time
 * @property integer $reply_time
 */
class QfbBorrowMoney extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%borrow_money}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'sey', 'tel', 'status', 'time', 'reply_time'], 'integer'],
            [['money'], 'number'],
            [['guarantee'], 'string', 'max' => 100],
            [['purpose'], 'string', 'max' => 200],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型',
            'money' => '金额',
            'sey' => '周期(天)',
            'guarantee' => '抵押物',
            'purpose' => '借款用途',
            'name' => '姓名',
            'tel' => '电话',
            'status' => '状态',
            'time' => '申请时间',
            'reply_time' => '回访时间',
        ];
    }
}
