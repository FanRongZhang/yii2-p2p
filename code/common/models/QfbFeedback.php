<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%feedback}}".
 *
 * @property string $id
 * @property integer $member_id
 * @property string $title
 * @property string $content
 * @property integer $reply
 * @property integer $pid
 * @property integer $create_time
 * @property integer $is_read
 */
class QfbFeedback extends \yii\db\ActiveRecord
{
    public $reply_content;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'title', 'content'], 'required'],
            [['member_id', 'reply', 'pid', 'create_time', 'is_read'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '反馈id'),
            'member_id' => Yii::t('app', '用户账号'),
            'title' => Yii::t('app', '标题'),
            'content' => Yii::t('app', '反馈的内容'),
            'reply' => Yii::t('app', '回复状态'),
            'pid' => Yii::t('app', '对应回复的id'),
            'create_time' => Yii::t('app', '反馈时间'),
            'is_read' => Yii::t('app', '是否已读0未读1已读'),
            'reply_content' => Yii::t('app', '回复内容'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     *  关联会员表
     */
    public function getMember()
    {
        return $this->hasOne(QfbMember::className(), ['id' => 'member_id']);
    }

    public function getr_content(){
        return $this->hasOne(QfbFeedback::className(), ['pid'=>'id'])->from(QfbFeedback::tableName().' r_content');
    }
}
