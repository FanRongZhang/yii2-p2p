<?php

namespace common\models;

use Yii;
use trntv\filekit\behaviors\UploadBehavior;

/**
 * This is the model class for table "{{%about_me}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $position
 * @property string $image
 * @property string $content
 * @property integer $status
 * @property integer $sort
 */
class QfbAboutMe extends \yii\db\ActiveRecord
{
    public $thumbnail;
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
        return '{{%about_me}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'position', 'image', 'content'], 'required'],
            [['status', 'sort'], 'integer'],
            [['name', 'position', 'image'], 'string', 'max' => 50],
            [['content'], 'string'],
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
            'name' => '名称',
            'position' => '职位',
            'image' => '头像',
            'content' => '简介',
            'status' => '状态',
            'sort' => '排序',
            'thumbnail' =>'头像'
        ];
    }
}
