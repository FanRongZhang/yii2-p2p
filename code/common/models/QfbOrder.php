<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property integer $id
 * @property string $sn
 * @property integer $member_id
 * @property string $price
 * @property integer $is_check
 * @property string $remark
 * @property integer $create_time
 * @property integer $complete_time
 * @property integer $type
 * @property integer $sorts
 * @property integer $bank_id
 * @property string $fee
 * @property string $money
 * @property string $bank_sn
 * @property integer $bank_type
 */
class QfbOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sn', 'member_id'], 'required'],
            [['member_id', 'is_check', 'create_time', 'complete_time', 'type', 'sorts', 'bank_id', 'bank_type'], 'integer'],
            [['price', 'fee', 'money'], 'number'],
            ['price', 'number', 'max' => 9999999],
            [['sn', 'remark', 'bank_sn', 'hr_sn'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '订单id'),
            'sn' => Yii::t('app', '订单编号'),
            'member_id' => Yii::t('app', 'Member ID'),
            'price' => Yii::t('app', '总价格'),
            'is_check' => Yii::t('app', '订单状态'),
            'remark' => Yii::t('app', '备注'),
            'create_time' => Yii::t('app', '添加时间'),
            'complete_time' => Yii::t('app', '完成时间'),
            'type' => Yii::t('app', '类型1转入2转出'),
            'sorts' => Yii::t('app', '订单类别'),
            'bank_id' => Yii::t('app', '银行卡id'),
            'fee' => Yii::t('app', '手续费'),
            'money' => Yii::t('app', '到账金额'),
            'bank_sn' => Yii::t('app', '第三方流水号'),
            'bank_type' => Yii::t('app', '所属通道'),
            'account' => Yii::t('app', '会员帐号'),
            'username' => Yii::t('app', '会员姓名'),
            'numbers' => Yii::t('app', ''),
            'mark' => Yii::t('app', '金额'),
            'create_time_end' => Yii::t('app', '至'),
            'complete_time_end' => Yii::t('app', '至'),
            'out_type' => Yii::t('app', '提现方式'),
        ];
    }
    /*
    *  关联银行表
    */
    public function getBank()
    {
        return $this->hasOne(QfbBank::className(), ['id' => 'bank_id']);
    }

    /*
     * 连表
     */
    public function getMember(){
        return $this->hasOne(QfbMember::className(), ['id'=>'member_id']);
    }

    public function getMemberInfo(){
        return $this->hasOne(QfbMemberInfo::className(), ['member_id'=>'member_id']);
    }

}
