<?php

namespace common\models;

use Yii;
use trntv\filekit\behaviors\UploadBehavior;

/**
 * This is the model class for table "{{%operation}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property string $time
 * @property string $bottom
 * @property integer $status
 */
class QfbOperation extends \yii\db\ActiveRecord
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
                'pathAttribute' => 'logo',
                'baseUrlAttribute' => false,
            ]
        ];
    } 
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%operation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'phone', 'time', 'bottom','logo'], 'required'],
            [['status'], 'integer'],
            [['name', 'phone', 'time','logo'], 'string', 'max' => 50],
            [['bottom'], 'string', 'max' => 100],
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
            'name' => '网站名称',
            'phone' => '服务热线',
            'time' => '服务时间',
            'bottom' => '底栏字样',
            'status' => '状态',
            'logo' => 'LOGO图',
            'thumbnail' => 'LOGO图',
        ];
    }
}
