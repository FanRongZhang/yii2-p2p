<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%message}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $send_ob
 * @property string $send_ob_value
 * @property integer $send_mode
 * @property integer $send_type
 * @property integer $create_time
 * @property integer $send_time
 */
class QfbMessage extends \yii\db\ActiveRecord
{
    public $send_ob_value0;
    public $send_ob_value1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'send_ob_value'], 'required'],
            [['content', 'send_ob_value'], 'string'],
            [['send_ob', 'send_mode', 'send_type',], 'integer'],
            [['title'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '消息标题',
            'content' => '消息内容',
            'send_ob' => '按标签消息发送对象',
            'send_ob_value' => '消息发送对象的值',
            'send_mode' => '消息发送的方式0延时1立即',
            'send_type' => '消息发送状态0未1成功2失败',
            'create_time' => '创建时间',
            'send_time' => '消息开始发送时间',
            'send_ob_value0' => '发送级别',
            'send_ob_value1' => '发送会员账号',
        ];
    }
}
