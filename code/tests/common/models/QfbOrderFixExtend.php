<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_fix_extend}}".
 *
 * @property integer $order_id
 * @property string $money_ticket_num
 * @property integer $money_ticket_id
 * @property string $rate_ticket_num
 * @property integer $rate_ticket_id
 * @property string $admin_rate
 * @property string $share_rate
 *
 * @property OrderFix $order
 */
class QfbOrderFixExtend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_fix_extend}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'money_ticket_id', 'rate_ticket_id'], 'integer'],
            [['money_ticket_num', 'rate_ticket_num', 'admin_rate', 'share_rate'], 'number'],
         //   [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => QfbOrderFix::className(), 'targetAttribute' => ['id']],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'money_ticket_num' => Yii::t('app', '代金券金额'),
            'money_ticket_id' => Yii::t('app', '代金券id'),
            'rate_ticket_num' => Yii::t('app', '加息卷比例'),
            'rate_ticket_id' => Yii::t('app', '加息券id'),
            'admin_rate' => Yii::t('app', '管理奖利率'),
            'share_rate' => Yii::t('app', '分润比例'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(QfbOrderFix::className(), ['id' => 'order_id']);
    }
}
