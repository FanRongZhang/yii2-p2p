<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%admin_group}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $remark
 * @property string $users
 * @property string $permission
 */
class QfbAdminGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['permission'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 250],
            [['users'], 'string', 'max' => 5000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '自增id'),
            'name' => Yii::t('app', '权限组名'),
            'remark' => Yii::t('app', '备注'),
            'users' => Yii::t('app', '组用户'),
            'permission' => Yii::t('app', '权限值'),
        ];
    }
}
