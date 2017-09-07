<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;


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
 */
class Admin extends ActiveRecord  
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
            [['enabled', 'is_sys', 'create_time' ], 'integer'],
            [['account'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 32],
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
            'last_ip' => Yii::t('app', 'Last Ip'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }
    
    /**
     * 获取帐号
     */
    public function getUsername()
    {
        return $this->account;
    }
    
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return yii::$app->security->validatePassword($password,yii::$app->security->generatePasswordHash($password));
    }
    
    
    /**
     * 根据帐号密码查找
     * @param 帐号 $account
     * @param 密码 $password
     * @return Ambigous <\yii\db\static, NULL>
     */
    public static function findByaccount($account,$password)
    {
        return static::findOne(['account' => $account, 'password' => md5($password),'enabled'=>\common\enum\StatusEnum::ENABLED]);
    }
}
