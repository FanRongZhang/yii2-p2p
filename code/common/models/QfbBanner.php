<?php

namespace common\models;

use Yii;
use trntv\filekit\behaviors\UploadBehavior;

/**
 * This is the model class for table "{{%banner}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $location_push
 * @property string $imgurl
 * @property string $linkurl
 * @property integer $status
 * @property integer $display_start_time
 * @property integer $display_end_time
 * @property integer $create_time
 * @property integer $type
 * @property integer $share_type
 * @property integer $sortord 
 */
class QfbBanner extends \yii\db\ActiveRecord
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
                'pathAttribute' => 'imgurl',
                'baseUrlAttribute' => false,
            ]
        ];
    } 

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%banner}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['location_push', 'status', 'create_time', 'type', 'share_type', 'sortord'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['imgurl', 'linkurl'], 'string', 'max' => 255],
            [['thumbnail'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '广告标题'),
            'location_push' => Yii::t('app', '广告投放位置'),
            'imgurl' => Yii::t('app', '广告图片地址'),
            'linkurl' => Yii::t('app', '广告外链地址'),
            'status' => Yii::t('app', '广告发布状态'),
            'display_start_time' => Yii::t('app', '发布开始时间'),
            'display_end_time' => Yii::t('app', '发布结束时间'),
            'create_time' => Yii::t('app', '创建时间'),
            'type' => Yii::t('app', '广告类型'),
            'share_type' => Yii::t('app', '分享类型'),
            'sortord' => Yii::t('app', '排序(从小到大)'),
            'thumbnail' => Yii::t('app', '广告图片地址'),
        ];
    }
}
