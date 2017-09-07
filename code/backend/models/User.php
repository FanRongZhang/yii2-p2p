<?php
namespace backend\models;

use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
use yii\base\Object;

class User extends Admin implements IdentityInterface
{
    public $auth_key;
    
    function __construct()
    {
        //$this->auth_key=\yii::$app->params['auth_key'];
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }
    
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }
    
    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    
    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    
    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = yii::$app->security->generateRandomString();
    }
    
    
    
}

?>