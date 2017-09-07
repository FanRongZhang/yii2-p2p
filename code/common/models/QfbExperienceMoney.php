<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%experience_money}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $valid_days
 * @property string $money
 * @property string $use_members
 * @property integer $status
 * @property integer $create_time
 * @property integer $start_time
 * @property integer $end_time
 */
class QfbExperienceMoney extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%experience_money}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'valid_days', 'use_members','money','name','status'], 'required','message'=>'不能为空'],
            [['type', 'valid_days', 'status', 'create_time', 'start_time', 'end_time'], 'integer'],
            [['money'], 'number'],
            [['name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '编号'),
            'name' => Yii::t('app', '名称'),
            'type' => Yii::t('app', '类型'),
            'valid_days' => Yii::t('app', '有效期(天)'),
            'money' => Yii::t('app', '金额'),
            'use_members' => Yii::t('app', '可用人群'),
            'status' => Yii::t('app', '是否开启'),
            'create_time' => Yii::t('app', '创建时间'),
            'start_time' => Yii::t('app', '体验金开始时间'),
            'end_time' => Yii::t('app', '体验金结束时间'),
        ];
    }
}
