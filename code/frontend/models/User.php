<?php
namespace frontend\models;
use common\extension\middleware\EncryptService;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{

    public $auth_key;

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
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['account' => $username]);
    }
    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
        ]);
    }
    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
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
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return EncryptService::twiceMd5Verify($password, $this->password);
    }
    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = EncryptService::twiceMd5($password);
    }
    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    /**
     * @inheritdoc
     */
    public function getRateLimit($request, $action)
    {
        if (($request->isPut || $request->isDelete || $request->isPost)) {
            return [Yii::$app->params['maxRateLimit'], Yii::$app->params['perRateLimit']];
        }
        return [Yii::$app->params['maxGetRateLimit'], Yii::$app->params['perGetRateLimit']];
    }
    /**
     * @inheritdoc
     */
    public function loadAllowance($request, $action)
    {
        return [
            \Yii::$app->cache->get($request->getPathInfo() . $request->getMethod() . '_remaining'),
            \Yii::$app->cache->get($request->getPathInfo() . $request->getMethod() . '_ts')
        ];
    }
    /**
     * @inheritdoc
     */
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        \Yii::$app->cache->set($request->getPathInfo() . $request->getMethod() . '_remaining', $allowance);
        \Yii::$app->cache->set($request->getPathInfo() . $request->getMethod() . '_ts', $timestamp);
    }
}