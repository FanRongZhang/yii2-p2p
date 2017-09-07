<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%member_info}}".
 *
 * @property integer $member_id
 * @property string $realname
 * @property string $nickname
 * @property string $avatar
 * @property integer $card_type
 * @property string $card_no
 * @property integer $is_verify
 *
 * @property Member $member
 */
class QfbMemberInfo extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%member_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['member_id', 'card_type', 'is_verify','bindId'], 'integer'],
            [['realname', 'nickname', 'card_no'], 'string', 'max' => 20],
            [['avatar'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'realname' => '会员姓名',
            'nickname' => '昵称',
            'avatar' => 'Avatar',
            'card_type' => 'Card Type',
            'card_no' => 'Card No',
            'is_verify' => 'Is Verify',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }
}
