<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%money_detail}}".
 *
 * @property integer $member_id
 * @property integer $type
 * @property integer $money_type
 * @property integer $from_member_id
 * @property string $money
 * @property integer $create_time
 * @property integer $is_show
 */
class QfbMoneyDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%money_detail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['member_id', 'type', 'money_type', 'from_member_id', 'create_time', 'is_show'], 'integer'],
            [['money'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => Yii::t('app', '收益用户id'),
            'type' => Yii::t('app', '明细类型   1.管理奖  3.分润'),
            'money_type' => Yii::t('app', '1.活期理财  2.定期理财'),
            'from_member_id' => Yii::t('app', '从哪个用户获取的金额'),
            'money' => Yii::t('app', '收益金额'),
            'create_time' => Yii::t('app', '创建时间'),
            'is_show' => Yii::t('app', '是否显示'),
        ];
    }
}
