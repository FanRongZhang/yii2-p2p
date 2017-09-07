<?php

namespace common\models;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "qfb_error_msg".
 *
 * @property integer $id
 * @property integer $channel_id
 * @property string $code
 * @property string $msg
 * @property integer $create_time
 */
class QfbErrorMsg extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qfb_error_msg';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'create_time'], 'integer'],
            [['code'], 'string', 'max' => 16],
            [['msg'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel_id' => '支付通道',
            'code' => '错误代码',
            'msg' => '错误说明',
            'create_time' => '创建时间',
        ];
    }
}
