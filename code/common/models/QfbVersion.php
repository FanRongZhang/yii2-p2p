<?php

namespace common\models;
use trntv\filekit\behaviors\UploadBehavior;
use Yii;

/**
 * This is the model class for table "{{%version}}".
 *
 * @property integer $id
 * @property integer $ver_code
 * @property string $ver_name
 * @property integer $create_time
 * @property string $content
 * @property integer $type
 * @property string $url
 * @property integer $is_force
 * @property integer $channel
 * @property string $imprint
 */
class QfbVersion extends \yii\db\ActiveRecord
{
    public $url_file;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%version}}';
    }

      /**
     **图片上传
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'url_file',
                'pathAttribute' => 'url',
                'baseUrlAttribute' => false,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ver_code', 'create_time', 'type', 'is_force', 'channel'], 'integer'],
            [['content','ver_code','type', 'channel','ver_name','url_file'], 'required'],
            [['content'], 'string'],
            [['ver_name'], 'string', 'max' => 6],
            [['url'], 'string', 'max' => 255],
            [['url_file'],'safe'],
            [['imprint'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ver_code' => Yii::t('app', '版本code'),
            'ver_name' => Yii::t('app', '版本名称'),
            'create_time' => Yii::t('app', '创建时间'),
            'content' => Yii::t('app', '更新内容介绍'),
            'type' => Yii::t('app', '设备类型'),
            'url' => Yii::t('app', '文件地址'),
            'url_file' => Yii::t('app', '应用安装包'),
            'is_force' => Yii::t('app', '是否强制更新'),
            'channel' => Yii::t('app', '是否上传市场'),
            'imprint' => Yii::t('app', '版本说明'),
        ];
    }
}
