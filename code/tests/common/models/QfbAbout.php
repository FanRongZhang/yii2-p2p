<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%about}}".
 *
 * @property integer $id
 * @property string $mobile
 * @property string $weixin
 * @property string $qq
 * @property string $open_time
 * @property string $remark
 * @property string $wx_pic
 */
class QfbAbout extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%about}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile'], 'required'],
            [['mobile'], 'string', 'max' => 15],
            [['weixin', 'qq'], 'string', 'max' => 100],
            [['open_time'], 'string', 'max' => 500],
            [['remark'], 'string', 'max' => 20],
            [['wx_pic'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'mobile' => Yii::t('app', '电话'),
            'weixin' => Yii::t('app', '微信'),
            'qq' => Yii::t('app', 'qq'),
            'open_time' => Yii::t('app', '开放时间设置'),
            'remark' => Yii::t('app', '备注'),
            'wx_pic' => Yii::t('app', '微信图片'),
        ];
    }
}
