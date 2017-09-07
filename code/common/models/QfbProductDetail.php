<?php

namespace common\models;

use Yii;
use common\models\QfbProduct as Product;


/**
 * This is the model class for table "{{%product_detail}}".
 *
 * @property integer $product_id
 * @property string $content
 * @property string $detail
 * @property string $tips
 *
 * @property Product $product
 */
class QfbProductDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_detail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['product_id'], 'integer'],
            [['detail'], 'string'],
            [['content'], 'string', 'max' => 100],
            [['tips'], 'string', 'max' => 50],
            //[['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('app', '产品id'),
            'content' => Yii::t('app', '产品简述'),
            'detail' => Yii::t('app', '产品详情'),
            'tips' => Yii::t('app', '图片地址'),
        ];
    }


}
