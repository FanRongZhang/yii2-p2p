<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%imei}}".
 *
 * @property integer $id
 * @property integer $member_id
 * @property string $imei
 * @property integer $imei_count
 * @property integer $member_count
 * @property integer $edit_time
 */
class QfbImei extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%imei}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['member_id', 'imei_count', 'member_count', 'edit_time'], 'integer'],
            [['imei'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'member_id' => Yii::t('app', 'Member ID'),
            'imei' => Yii::t('app', '设备唯一标识'),
            'imei_count' => Yii::t('app', '此设备认证失败次数'),
            'member_count' => Yii::t('app', '对应人在此设备认证失败次数'),
            'edit_time' => Yii::t('app', '编辑时间'),
        ];
    }
}
