<?php

namespace common\models;

use Yii;
use trntv\filekit\behaviors\UploadBehavior;

/**
 * This is the model class for table "{{%pc_image}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $image
 * @property integer $type
 * @property string $url
 * @property integer $time
 * @property integer $sort
 * @property integer $status
 */
class QfbPcImage extends \yii\db\ActiveRecord
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
                'pathAttribute' => 'image',
                'baseUrlAttribute' => false,
            ]
        ];
    } 
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pc_image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'time', 'sort', 'status'], 'integer'],
            [['name', 'image'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 100],
            [['thumbnail'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '标题',
            'image' => '图片',
            'type' => 'Type',
            'url' => '链接',
            'time' => '添加时间',
            'sort' => '排序',
            'status' => '状态',
            'thumbnail' => '图片'
        ];
    }
}
