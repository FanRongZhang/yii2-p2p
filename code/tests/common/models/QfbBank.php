<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%bank}}".
 *
 * @property integer $id
 * @property integer $member_id
 * @property string $username
 * @property string $no
 * @property string $mobile
 * @property integer $create_time
 * @property string $bank_abbr
 * @property string $province
 * @property string $city
 *
 * @property BankExtend $bankExtend
 */
class QfbBank extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'username', 'no', 'mobile', 'create_time'], 'required'],
            [['member_id', 'create_time'], 'integer'],
            [['username'], 'string', 'max' => 25],
            [['no'], 'string', 'max' => 30],
            [['mobile'], 'string', 'max' => 15],
            [['bank_abbr'], 'string', 'max' => 16],
            [['province', 'city'], 'string', 'max' => 18],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'username' => '姓名',
            'name'  => '银行名称',
            'no' => '银行卡号',
            'mobile' => '预留手机',
            'create_time' => 'Create Time',
            'bank_abbr' => '银行缩写(如CCB)',
            'province' => '省份',
            'city' => '城市',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBankExtend()
    {
        return $this->hasOne(QfbBankExtend::className(), ['bank_id' => 'id']);
    }
}
