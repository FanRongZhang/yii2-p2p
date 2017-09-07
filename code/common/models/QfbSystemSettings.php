<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%system_settings}}".
 *
 * @property integer $id
 * @property integer $status
 * @property string $min_money
 * @property string $money_fee
 * @property string $fast_rate
 * @property string $slow_rate
 * @property string $per_money
 * @property string $day_money
 * @property string $operator
 * @property integer $open_start_time
 * @property integer $open_end_time
 * @property string $close_content
 */
class QfbSystemSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%system_settings}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'open_start_time', 'open_end_time'], 'integer'],
            [['min_money', 'money_fee', 'fast_rate', 'slow_rate', 'per_money', 'day_money'], 'number'],
            [['operator'], 'string', 'max' => 25],
            [['close_content'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => '出账状态',
            'min_money' => '提现费率计算最小金额',
            'money_fee' => '提现小于固定金额收取',
            'fast_rate' => '快速提现费率',
            'slow_rate' => '慢速提现费率',
            'per_money' => '单笔最大提现金额',
            'day_money' => '单日最大提现金额',
            'operator' => '操作者',
            'open_start_time' => '每日提现开始时间',
            'open_end_time' => '每日提现结束时间',
            'close_content' => '关闭提现显示的内容',
        ];
    }
}
