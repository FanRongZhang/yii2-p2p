<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%base_navigation}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $pid
 * @property string $url
 * @property integer $level
 * @property integer $sort
 * @property integer $status
 */
class QfbBaseNavigation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%base_navigation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'url'], 'required'],
            [['pid', 'level', 'sort', 'status'], 'integer'],
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
            'pid' => '所属父级',
            'url' => '跳转链接',
            'level' => '等级',
            'sort' => '排序',
            'status' => '状态',
        ];
    }

    public static function getname($pid)
    {
        $name=self::find()->select('name')->where(['id'=>$pid])->one();
        return $name['name'];
    }
}
