<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%index}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $icon
 * @property string $url
 * @property integer $click
 * @property string $click_tips
 * @property integer $type
 */
class QfbIndex extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%index}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['click', 'type'], 'integer'],
            [['title', 'click_tips'], 'string', 'max' => 30],
            [['icon', 'url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '自增id'),
            'title' => Yii::t('app', '宫格标题'),
            'icon' => Yii::t('app', '宫格图标url'),
            'url' => Yii::t('app', '如果是会员权益'),
            'click' => Yii::t('app', '是否可点击进入，默认为true可以进入，如果为false就表示不能进入'),
            'click_tips' => Yii::t('app', '如果click为false不可点击，客户端弹出提示语，默认『当前未开放』'),
            'type' => Yii::t('app', 'Type'),
        ];
    }
}
