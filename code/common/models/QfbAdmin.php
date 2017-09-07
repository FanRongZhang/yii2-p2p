<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%admin}}".
 *
 * @property integer $id
 * @property string $account
 * @property string $password
 * @property integer $enabled
 * @property integer $is_sys
 * @property integer $create_time
 * @property integer $last_login
 * @property string $permission
 * @property string $true_name
 */
class QfbAdmin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'password','enabled', 'is_sys', 'true_name'], 'required'],
            [['enabled', 'is_sys', 'create_time', 'last_login'], 'integer'],
            [['permission'], 'string'],
            [['account'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 32],
            [['true_name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '自增id'),
            'account' => Yii::t('app', '登录账号'),
            'password' => Yii::t('app', '登录密码'),
            'enabled' => Yii::t('app', '是否启用'),
            'is_sys' => Yii::t('app', '是否系统管理员'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_login' => Yii::t('app', '最后登录时间'),
            'permission' => Yii::t('app', '权限值'),
            'true_name' => Yii::t('app', '姓名'),
        ];
    }
}
