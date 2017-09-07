<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_fix}}".
 *
 * @property integer $id
 * @property string $sn
 * @property integer $member_id
 * @property integer $product_id
 * @property string $money
 * @property string $pay_money
 * @property integer $status
 * @property integer $create_time
 * @property integer $next_profit_time
 * @property integer $end_time
 * @property string $year_rate
 * @property integer $number
 *
 * @property OrderFixExtend $orderFixExtend
 */
class QfbOrderFix extends \yii\db\ActiveRecord
{
    public $product_name;
    public $account;
    public $realname;
    public $vouchers;
    public $invest_day;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_fix}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sn', 'member_id', 'product_id', 'money', 'pay_money', 'status', 'create_time', 'next_profit_time', 'end_time', 'year_rate'], 'required'],
            [['member_id', 'product_id', 'status', 'create_time', 'next_profit_time', 'end_time', 'number'], 'integer'],
            [['money', 'pay_money', 'year_rate','profit_money', 'day_interest'], 'number'],
            [['sn', 'hr_sn'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '定期订单id'),
            'sn' => Yii::t('app', '订单编号'),
            'member_id' => Yii::t('app', '下单用户'),
            'product_id' => Yii::t('app', '产品id'),
            'money' => Yii::t('app', '订单金额(元)'),
            'pay_money' => Yii::t('app', '支付金额(元)'),
            'status' => Yii::t('app', '订单状态'),
            'create_time' => Yii::t('app', '创建时间'),
            'day_interest' => Yii::t('app', '每天利息'),
            'next_profit_time' => Yii::t('app', '下次分润时间'),
            'end_time' => Yii::t('app', '到期时间'),
            'year_rate' => Yii::t('app', '年化收益率'),
            'number' => Yii::t('app', '分润次数'),
            'profit_money' => Yii::t('app', '预期分润金额'),
            'account' => Yii::t('app','会员账户'),
            'realname' => Yii::t('app','会员姓名'),
            'last_profit_time' => Yii::t('app','最后分润时间'),
            'vouchers' => Yii::t('app','代金券'),
            'product_name' => Yii::t('app','产品名称'),
            'account' => Yii::t('app','会员账户'),
            'realname' => Yii::t('app','会员姓名')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getorder_fix_extend()
    {
        return $this->hasOne(QfbOrderFixExtend::className(), ['order_id' => 'id']);
    }

    public function getProduct() 
    {
        return $this->hasOne(QfbProduct::className(), ['id' => 'product_id']);
    }

    public function getMember()
    {
        return $this->hasOne(QfbMember::className(), ['id' => 'member_id']);
    }

    public function getInfo()
    {
        return $this->hasOne(QfbMemberInfo::className(), ['member_id' => 'member_id']);
    }

    public function getRepayment()
    {
        return $this->hasMany(QfbOrderRepayment::className(), ['product_id' => 'product_id']);
    }




}
