<?php

namespace backend\modules\system\models;

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
 * @property string $last_ip
 * @property string $permission
 * @property string $true_name
 * @property integer $department_id
 */
class Admin extends \yii\db\ActiveRecord
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
            [['account', 'password', 'create_time'], 'required'],
            [['enabled', 'is_sys', 'create_time', 'last_login', 'department_id'], 'integer'],
            [['permission'], 'string'],
            [['account'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 32],
            [['last_ip', 'true_name'], 'string', 'max' => 20]
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
            'enabled' => Yii::t('app', 'Enabled'),
            'is_sys' => Yii::t('app', 'Is Sys'),
            'create_time' => Yii::t('app', 'Create Time'),
            'last_login' => Yii::t('app', 'Last Login'),
            'last_ip' => Yii::t('app', 'Last Ip'),
            'permission' => Yii::t('app', 'Permission'),
            'true_name' => Yii::t('app', 'True Name'),
            'department_id' => Yii::t('app', 'Department ID'),
        ];
    }
}
