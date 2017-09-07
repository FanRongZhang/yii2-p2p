<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "qfb_binding_bank".
 *
 * @property integer $id
 * @property string $sn
 * @property integer $member_id
 * @property string $name
 * @property string $username
 * @property integer $check_status
 * @property string $remark
 * @property integer $token
 * @property string $storable_pan
 * @property string $mobile
 * @property string $bank_abbr
 * @property string $province
 * @property string $city
 */
class QfbBindingBank extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qfb_binding_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sn', 'member_id', 'name', 'username'], 'required'],
            [['member_id', 'check_status', 'token'], 'integer'],
            [['sn', 'username', 'remark'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 50],
            [['storable_pan', 'bank_abbr'], 'string', 'max' => 16],
            [['mobile'], 'string', 'max' => 15],
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
            'sn' => 'Sn',
            'member_id' => 'Member ID',
            'name' => 'Name',
            'username' => 'Username',
            'check_status' => 'Check Status',
            'remark' => 'Remark',
            'token' => 'Token',
            'storable_pan' => 'Storable Pan',
            'mobile' => 'Mobile',
            'bank_abbr' => 'Bank Abbr',
            'province' => 'Province',
            'city' => 'City',
        ];
    }
}
