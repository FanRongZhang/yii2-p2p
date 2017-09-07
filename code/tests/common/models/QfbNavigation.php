<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%navigation}}".
 *
 * @property string $id
 * @property string $name
 * @property string $url
 * @property integer $status
 * @property integer $sort
 */
class QfbNavigation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%navigation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'url'], 'required'],
            [['status', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'url' => '链接',
            'status' => '状态',
            'sort' => '排序',
        ];
    }
}
