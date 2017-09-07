<?php

namespace common\models;

use Yii;
use trntv\filekit\behaviors\UploadBehavior;

/**
 * This is the model class for table "{{%agreement}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $pic_url
 * @property integer $create_time
 * @property integer $type
 * @property integer $is_del
 */
class QfbAgreement extends \yii\db\ActiveRecord
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
        return '{{%agreement}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'create_time','thumbnail'], 'required'],
            [['content'], 'string'],
            [['create_time', 'type', 'is_del'], 'integer'],
            [['title'], 'string', 'max' => 30],
            [['pic_url'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '协议名称',
            'content' => '内容',
            'pic_url' => '图标地址',
            'create_time' => '创建时间',
            'type' => '协议类型',
            'thumbnail' => '图标地址',
        ];
    }
    public function getProduct_agreement(){
        return $this->hasOne(QfbProductAgreement::className(), ['agreement_id'=>'id' ]);
    }
}
