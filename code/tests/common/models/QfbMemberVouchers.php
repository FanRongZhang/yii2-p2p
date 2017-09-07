<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%member_vouchers}}".
 *
 * @property integer $id
 * @property integer $vouchers_id
 * @property integer $member_id
 * @property integer $status
 * @property integer $receive_time
 * @property integer $invalid_time
 * @property string $remark
 * @property string $sn
 * @property integer $product_id
 */
class QfbMemberVouchers extends \yii\db\ActiveRecord
{
    public $receive_time_end;
    public $accout;
    public $realname;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member_vouchers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[], 'required'],
            [['vouchers_id', 'member_id', 'status', 'product_id','receive_time','invalid_time'], 'integer'],
            [['remark', 'sn'], 'string', 'max' => 50],
        ];
    }
    
    public function getVouchers() {
        return $this->hasOne(QfbVouchers::className(), ['id'=>'vouchers_id'])->from(QfbVouchers::tableName().' vouchers');
    }
    public function getMember() {
        return $this->hasOne(QfbMember::className(), ['id'=>'member_id'])->from(QfbMember::tableName().' member');
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'vouchers_id' => Yii::t('app', '代金券ID'),
            'member_id' => Yii::t('app', '用户id'),
            'status' => Yii::t('app', '状态'),
            'receive_time' => Yii::t('app', '领取时间'),
            'invalid_time' => Yii::t('app', '失效时间'),
            'remark' => Yii::t('app', '获得途径'),
            'sn' => Yii::t('app', 'Sn'),
            'product_id' => Yii::t('app', '使用项目id'),
        ];
    }
}
