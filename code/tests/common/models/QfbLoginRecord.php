<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%login_record}}".
 *
 * @property integer $member_id
 * @property integer $flag
 * @property integer $type
 * @property integer $create_time
 * @property string $ip
 */
class QfbLoginRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    protected  static $_member_id;
    public function __construct($_member_id)
    {
        self::$_member_id = $_member_id;
    }

    public static function tableName()
    {
        $number= self::$_member_id%10;
        return '{{%login_record_t'.$number.'}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'ip'], 'required'],
            [['member_id', 'flag', 'type', 'create_time'], 'integer'],
            [['ip'], 'string', 'max' => 25],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'flag' => 'Flag',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'ip' => 'Ip',
        ];
    }
}
