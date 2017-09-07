<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%vouchers}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $valid_days
 * @property string $money
 * @property string $use_money
 * @property string $use_members
 * @property integer $use_type
 * @property integer $status
 * @property integer $create_time
 * @property integer $start_time
 * @property integer $end_time
 */
class QfbVouchers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%vouchers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'valid_days', 'use_type', 'status', 'create_time',], 'integer'],
            [['money', 'use_money'], 'number'],
            [['start_time', 'end_time', 'use_members','name','valid_days', 'use_type', 'type','money', 'use_money', 'status'], 'required' , 'message'=>'不能为空'],
            [['name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '代金券编号'),
            'name' => Yii::t('app', '代金券名称'),
            'type' => Yii::t('app', '代金券类型'),
            'valid_days' => Yii::t('app', '有效期(天)'),
            'money' => Yii::t('app', '代金券金额'),
            'use_money' => Yii::t('app', '使用条件'),
            'use_members' => Yii::t('app', '可用人群'),
            'use_type' => Yii::t('app', '可用产品类型'),
            'status' => Yii::t('app', '是否开启'),
            'create_time' => Yii::t('app', '创建时间'),
            'start_time' => Yii::t('app', '开始时间'),
            'end_time' => Yii::t('app', '结束时间'),
        ];
    }
}
