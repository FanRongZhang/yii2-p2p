<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%member_money}}".
 *
 * @property integer $member_id
 * @property string $money
 * @property string $live_money
 * @property string $fix_money
 * @property string $pre_live_money
 * @property string $lock_money
 * @property integer $last_profit_time
 *
 * @property Member $member
 */
class QfbMemberMoney extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member_money}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['member_id', 'last_profit_time'], 'integer'],
            [['money', 'live_money', 'fix_money', 'pre_live_money', 'lock_money'], 'number'],
            //[['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => QfbMember::className(), 'targetAttribute' => ['id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'money' => 'Money',
            'live_money' => 'Live Money',
            'fix_money' => 'Fix Money',
            'pre_live_money' => 'Pre Live Money',
            'lock_money' => 'Lock Money',
            'last_profit_time' => 'Last Profit Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(QfbMember::className(), ['member_id' => 'member_id']);
    }
}
