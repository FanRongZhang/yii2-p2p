<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%sms_code}}".
 *
 * @property integer $id
 * @property string $phone
 * @property string $code
 * @property integer $sen_time
 * @property integer $use_time
 * @property integer $status
 */
class QfbSmsCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms_code}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'code'], 'required'],
            [['sen_time', 'use_time', 'status'], 'integer'],
            [['phone'], 'string', 'max' => 24],
            [['code'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'code' => 'Code',
            'sen_time' => 'Sen Time',
            'use_time' => 'Use Time',
            'status' => 'Status',
        ];
    }
}
