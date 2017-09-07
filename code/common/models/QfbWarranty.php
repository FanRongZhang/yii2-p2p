<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%warranty}}".
 *
 * @property integer $id
 * @property string $product_id
 * @property string $plate_number
 * @property string $model
 * @property string $engine_number
 * @property string $vin
 * @property string $contract_number
 * @property string $warrantor
 * @property string $id_card
 * @property string $mobile
 * @property integer $guarantee_way
 */
class QfbWarranty extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warranty}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['guarantee_way', 'product_id'], 'integer'],
            [['plate_number', 'model', 'engine_number', 'vin', 'contract_number', 'warrantor', 'id_card', 'mobile'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '产品id'),
            'product_id' => Yii::t('app', '产品ID'),
            'plate_number' => Yii::t('app', '牌照号'),
            'model' => Yii::t('app', '型号'),
            'engine_number' => Yii::t('app', '发动机号'),
            'vin' => Yii::t('app', '车架号'),
            'contract_number' => Yii::t('app', '合同编码'),
            'warrantor' => Yii::t('app', '保证人'),
            'id_card' => Yii::t('app', '身份证号码'),
            'mobile' => Yii::t('app', '联系电话'),
            'guarantee_way' => Yii::t('app', '保证方式'),
        ];
    }
}
