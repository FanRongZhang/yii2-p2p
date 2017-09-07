<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%bank_limit}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $trade_num
 * @property string $one_trade
 * @property string $day_trade
 * @property string $month_trade
 * @property string $create_user
 * @property string $iss_users
 * @property integer $is_support
 * @property integer $pt_type
 * @property string $bank_abbr
 */
class QfbBankLimit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_limit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'bank_abbr'], 'required'],
            [['trade_num', 'is_support', 'pt_type','create_user'], 'integer'],
            [['one_trade', 'day_trade', 'month_trade'], 'number'],
            [['name'], 'string', 'max' => 20],
            [['iss_users'], 'string', 'max' => 50],
            [['bank_abbr'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '银行名称',
            'trade_num' => '每月交易次数',
            'one_trade' => '单笔交易限额',
            'day_trade' => '单日交易限额',
            'month_trade' => '单月交易限额',
            'create_user' => '添加人',
            'iss_users' => '银行代号',
            'is_support' => '是否支持该银行卡',
            'pt_type' => '平台类别',
            'bank_abbr' => '银行缩写'
        ];
    }
}
