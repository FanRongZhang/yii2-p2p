<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%menu}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $display
 * @property integer $parent_id
 * @property integer $level
 * @property string $url
 * @property integer $permision_value
 */
class Menu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['display', 'parent_id', 'level','permision_value'], 'integer'],
            [['url'], 'required'],
            [['name'], 'string', 'max' => 32],
            [['url'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '菜单id'),
            'name' => Yii::t('app', '菜单名'),
            'display' => Yii::t('app', '是否显示'),
            'parent_id' => Yii::t('app', '父菜单ID'),
            'level' => Yii::t('app', '菜单级别'),
            'url' => Yii::t('app', '链接地址'),
        ];
    }
}
