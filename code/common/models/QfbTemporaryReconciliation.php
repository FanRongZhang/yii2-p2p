<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%temporary_reconciliation}}".
 *
 * @property integer $id
 * @property string $file_name
 * @property integer $file_type
 * @property integer $status
 * @property integer $withhold_time
 * @property string $remark
 */
class QfbTemporaryReconciliation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%temporary_reconciliation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_name', 'withhold_time', 'end_time'], 'required'],
            [['file_type', 'status', 'affirm_status', 'withhold_time', 'end_time'], 'integer'],
            [['remark'], 'string'],
            [['file_name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'file_name' => Yii::t('app', '对账日期字符'),
            'file_type' => Yii::t('app', '文件类型'),
            'status' => Yii::t('app', '状态'),
            'affirm_status' => Yii::t('app', '对账确认状态'),
            'withhold_time' => Yii::t('app', '开始时间'),
            'end_time' => Yii::t('app', '结束时间'),
            'remark' => Yii::t('app', '备注'),
        ];
    }
}
