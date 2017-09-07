<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%money_log}}".
 *
 * @property integer $member_id
 * @property integer $type
 * @property integer $money_type
 * @property string $money
 * @property integer $create_time
 * @property string $old_money
 * @property integer $action
 * @property string $remark
 */
class QfbMoneyLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    protected  static $_member_id;
    public function __construct($_member_id='')
    {
        self::$_member_id = $_member_id;
    }

    public static function tableName()
    {
        $member_id = self::$_member_id;
        if(!empty($member_id)){
            $number= self::$_member_id%10;
            $table = 'money_log_t'.$number;
        }else{
            $table = 'money_log';
        }

        return '{{%'.$table.'}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'create_time', 'action'], 'required'],
            [['member_id', 'type', 'money_type', 'create_time', 'action'], 'integer'],
            [['money', 'old_money'], 'number'],
            [['remark'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => Yii::t('app', '会员id'),
            'type' => Yii::t('app', '1收入,2支出'),
            'money_type' => Yii::t('app', '金额类型 1零钱,2活期,3定期'),
            'money' => Yii::t('app', '金额'),
            'create_time' => Yii::t('app', '创建时间'),
            'old_money' => Yii::t('app', '旧金额'),
            'action' => Yii::t('app', '行为类型 1管理奖,2充值,3分润,5推荐奖,6转账,7提现,8收益,9退款,10兑换,12转入'),
            'remark' => Yii::t('app', '备注'),
        ];
    }
}
