<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%bank_extend}}".
 *
 * @property integer $bank_id
 * @property integer $channel_id
 * @property integer $is_del
 * @property integer $create_time
 * @property integer $is_default
 * @property integer $token
 * @property string $storable_pan
 *
 * @property Bank $bank
 */
class QfbBankExtend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_extend}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_id', 'channel_id'], 'required'],
            [['bank_id', 'channel_id', 'is_del', 'create_time', 'is_default', 'token',
                'storable_pan'], 'integer'],
            // [['storable_pan'], 'string', 'max' => 16],
            // [['bank_id'], 'exist', 'skipOnError' => true, 'targetClass' => QfbBank::className(), 'targetAttribute' => ['id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bank_id' => Yii::t('app', 'Bank ID'),
            'channel_id' => Yii::t('app', '通道id'),
            'is_del' => Yii::t('app', '状态0否1是'),
            'create_time' => Yii::t('app', '绑定时间'),
            'is_default' => Yii::t('app', '是否默认0否1是'),
            'token' => Yii::t('app', '通道token'),
            'storable_pan' => Yii::t('app', '通道短卡号'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBank()
    {
        return $this->hasOne(QfbBank::className(), ['id' => 'bank_id']);
    }
}
