<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%channel}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $ds_rate
 * @property string $df_money
 * @property integer $in_status
 * @property integer $out_status
 * @property integer $create_time
 * @property integer $sort
 * @property integer $is_default
 * @property integer $need_certification
 */
class QfbChannel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%channel}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ds_rate', 'df_money'], 'number'],
            [['in_status', 'out_status', 'create_time', 'sort', 'is_default', 'need_certification'], 'integer'],
            [['name'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '通道名称'),
            'ds_rate' => Yii::t('app', '通道代收费用(%)'),
            'df_money' => Yii::t('app', '通道代付费用(元/笔)'),
            'in_status' => Yii::t('app', '是否支持代收'),
            'out_status' => Yii::t('app', '是否支持代付'),
            'create_time' => Yii::t('app', '创建时间'),
            'sort' => Yii::t('app', '通道排序'),
            'is_default' => Yii::t('app', '是否默认提现通道'),
            'need_certification' => Yii::t('app', '绑卡前是否需要实名'),
        ];
    }
}
