<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%reconciliation_log}}".
 *
 * @property integer $id
 * @property string $ls_sn
 * @property string $platform_money
 * @property string $account_money
 * @property integer $type
 * @property integer $create_time
 * @property integer $status
 * @property string $remark
 */
class QfbReconciliationLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%reconciliation_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ls_sn', 'platform_money', 'account_money'], 'required'],
            [['platform_money', 'account_money'], 'number'],
            [['type', 'create_time', 'status', 'r_id'], 'integer'],
            [['remark'], 'string'],
            [['ls_sn'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'r_id' => Yii::t('app', '临时对账表id'),
            'ls_sn' => Yii::t('app', '流水号'),
            'platform_money' => Yii::t('app', '平台金额'),
            'account_money' => Yii::t('app', '到账金额'),
            'type' => Yii::t('app', '类型'),
            'create_time' => Yii::t('app', '创建时间'),
            'status' => Yii::t('app', '是否处理'),
            'remark' => Yii::t('app', '备注'),
        ];
    }
}
