<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%profit_settings}}".
 *
 * @property integer $id
 * @property integer $product_type
 * @property integer $product_id
 * @property integer $status
 * @property string $year_rate
 * @property string $direct_rate
 * @property string $indirect_rate
 * @property string $share_rate
 * @property string $recommond_rate
 * @property string $manage_rate
 * @property string $province_rate
 * @property string $city_rate
 * @property string $area_rate
 * @property string $agent_rate
 * @property integer $operator
 * @property integer $edit_time
 */
class QfbProfitSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%profit_settings}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_type', 'product_id', 'recommond_rate','manage_rate','agent_rate'], 'required'],
            [['product_type', 'product_id', 'status', 'operator', 'edit_time'], 'integer'],
            [['direct_rate', 'indirect_rate', 'share_rate', 'recommond_rate','manage_rate', 'province_rate', 'city_rate', 'area_rate', 'agent_rate'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_type' => Yii::t('app', '产品类型,1活期，2定期'),
            'product_id' => Yii::t('app', '产品id'),
            'status' => Yii::t('app', '是否可用,1可用，0不可用'),
            'direct_rate' => Yii::t('app', '直接会员分润比例'),
            'indirect_rate' => Yii::t('app', '间接推荐分润比例'),
            'share_rate' => Yii::t('app', '分享会员分润比例'),
            'recommond_rate' => Yii::t('app', '分润比例(%)'),
            'manage_rate' => Yii::t('app','管理奖比例(%)'),
            'province_rate' => Yii::t('app', '省级代理管理奖比例'),
            'city_rate' => Yii::t('app', '市级代理管理奖比例'),
            'area_rate' => Yii::t('app', '区级代理管理奖比例'),
            'agent_rate' => Yii::t('app', '推荐代理奖(%)'),
            'operator' => Yii::t('app', '最后操作人'),
            'edit_time' => Yii::t('app', '编辑时间'),
        ];
    }
}
