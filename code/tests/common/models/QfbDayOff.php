<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%day_off}}".
 *
 * @property integer $id
 * @property integer $time
 * @property string $operator
 */
class QfbDayOff extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%day_off}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time', 'operator'], 'required'],
            [['operator'], 'string', 'max' => 25],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'time' => '日期',
            'operator' => '操作人',
        ];
    }
}
