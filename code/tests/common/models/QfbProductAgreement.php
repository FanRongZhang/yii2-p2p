<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_agreement}}".
 *
 * @property integer $product_id
 * @property integer $agreement_id
 */
class QfbProductAgreement extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_agreement}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'agreement_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('app', 'Product ID'),
            'agreement_id' => Yii::t('app', '产品协议'),
        ];
    }
    public function getAgreement(){
        return $this->hasOne(QfbAgreement::className(), ['id'=>'agreement_id' ]);
    }
}
