<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%imei_notice}}".
 *
 * @property string $imei
 * @property integer $notice_id
 */
class QfbImeiNotice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%imei_notice}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['imei', 'notice_id'], 'required'],
            [['notice_id'], 'integer'],
            [['imei'], 'string', 'max' => 100],
            [['imei'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'imei' => '设备号',
            'notice_id' => '记录消息id',
        ];
    }
}
