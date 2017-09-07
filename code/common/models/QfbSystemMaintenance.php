<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "qfb_system_maintenance".
 *
 * @property integer $is_maintenance
 * @property string $msg
 */
class QfbSystemMaintenance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qfb_system_maintenance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_maintenance'], 'integer'],
            [['msg'], 'required'],
            [['msg'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'is_maintenance' => '维护开关',
            'msg' => '维护消息',
        ];
    }
}
