<?php

namespace common\models;

use Yii;
use trntv\filekit\behaviors\UploadBehavior;

/**
 * This is the model class for table "{{%share}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $pic_url
 * @property string $url
 * @property integer $type
 * @property integer $create_time
 * @property integer $is_open
 */
class QfbShare extends \yii\db\ActiveRecord
{
    public $thumbnail;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'thumbnail',
                'pathAttribute' => 'pic_url',
                'baseUrlAttribute' => false,
            ]
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%share}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content','thumbnail'], 'required'],
            [['content'], 'string'],
            [['type', 'create_time', 'is_open'], 'integer'],
            [['title'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '分享标题',
            'content' => '分享内容',
            'pic_url' => '图片地址',
            'url' => '页面地址',
            'type' => '分享类型',
            'create_time' => '创建时间',
            'is_open' => '是否开启0否1是',
            'thumbnail' => '图片地址',
        ];
    }
}
