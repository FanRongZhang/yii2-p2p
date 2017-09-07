<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "dmu_member".
 *
 * @property integer $id
 * @property string $account
 * @property string $password
 * @property string $mobile
 * @property string $email
 * @property string $pay_password
 * @property integer $status
 * @property integer $create_time
 * @property integer $source
 */
class UMember extends \yii\db\ActiveRecord
{
    public $rememberMe = true;

    /**
     * 指定为非默认库
     */
    public static function getDb()
    {
        return Yii::$app->dbuc;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dmu_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'password', 'mobile'  ], 'required'],
            [['status', 'create_time', 'source'], 'integer'],
            [['account'], 'string', 'max' => 20],
            ['rememberMe', 'boolean'],
            [['password' ], 'string', 'max' => 32],
            [['mobile'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'account' => Yii::t('app', 'Account'),
            'password' => Yii::t('app', 'Password'),
            'mobile' => Yii::t('app', 'Mobile'),
            'email' => Yii::t('app', 'Email'),
            'status' => Yii::t('app', '1.'),
            'create_time' => Yii::t('app', 'Create Time'),
            'source' => Yii::t('app', 'Source'),
        ];
    }
}
