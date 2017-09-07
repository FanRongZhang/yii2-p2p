<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $create_time
 * @property integer $operator_id
 * @property integer $update_time
 * @property integer $sortord
 */
class QfbArticle extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%article}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'operator_id'], 'required'],
            [['content'], 'string'],
            [['create_time', 'operator_id', 'update_time', 'sortord'], 'integer'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '文章id'),
            'title' => Yii::t('app', '文章标题'),
            'content' => Yii::t('app', '文章内容'),
            'create_time' => Yii::t('app', '创建时间'),
            'operator_id' => Yii::t('app', '对应管理员id'),
            'update_time' => Yii::t('app', '修改时间'),
            'sortord' => Yii::t('app', '排序，从小到大'),
        ];
    }
}
