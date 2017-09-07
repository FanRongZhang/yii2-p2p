<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_repayment}}".
 *
 * @property string $id
 * @property string $sn
 * @property integer $member_id
 * @property string $money
 * @property string $interest
 * @property integer $create_time
 * @property integer $status
 * @property integer $confirm_time
 */
class QfbOrderRepayment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_repayment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'create_time', 'status', 'complete_time'], 'integer'],
            [['money', 'interest', 'repay_money'], 'number'],
            [['sn'], 'string', 'max' => 255],
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
            'member_id' => '用户ID',
            'money' => '待还本金',
            'interest' => '待还利息',
            'repay_money' => '已还金额',
            'create_time' => '创建时间',
            'status' => '状态',
            'complete_time' => '完成时间',
        ];
    }

    public function getProduct(){
        return $this->hasOne(QfbProduct::className(), ['id'=>'product_id']);
    }

    public function getMember()
    {
        return $this->hasOne(QfbMember::className(), ['id' => 'member_id']);
    }

    public function getInfo()
    {
        return $this->hasOne(QfbMemberInfo::className(), ['member_id' => 'member_id']);
    }
}
