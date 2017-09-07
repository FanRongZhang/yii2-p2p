<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%pt_account}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $money
 * @property string $frozen
 * @property integer $bank
 * @property string $bank_code
 * @property integer $is_open
 */
class QfbPtAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pt_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['money', 'frozen'], 'number'],
            [['is_open'], 'integer'],
            [['name','zn_name'], 'string', 'max' => 50],
            [['bank_code'], 'string', 'max' => 10],
            [['bank'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '账户',
            'money' => '金额',
            'frozen' => '冻结金额',
            'commutation_money' => '代偿金额',
            'bank' => '银行卡号',
            'bank_code' => '银行编码',
            'is_open' => 'Is Open',
            'zn_name' => '账户名'

        ];
    }
}
