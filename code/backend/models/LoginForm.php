<?php
namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $account;
    public $password;
    public $rememberMe = false;

    private $_user = false;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // account and password are both required
            [['account', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account' => \yii::t('app','account'),
            'password' => \yii::t('app','password'),
            'rememberMe' => \yii::t('app','rememberMe')
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('account', '');
                $this->addError($attribute, yii::t('app','error_password'));
            }
        }
    }
    
    

    /**
     * Logs in a user using the provided account and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[account]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByAccount($this->account,$this->password);
        }
        return $this->_user;
    }
}
