<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pro_category".
 *
 * @property integer $id
 * @property string $category_name
 * @property string $category_des
 * @property string $rate
 * @property string $rate_tips
 * @property string $pic
 * @property string $icon
 */
class QfbProCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qfb_pro_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_name'], 'string', 'max' => 30],
            [['category_des', 'rate', 'rate_tips', 'pic', 'icon'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_name' => Yii::t('app', 'Category Name'),
            'category_des' => Yii::t('app', 'Category Des'),
            'rate' => Yii::t('app', 'Rate'),
            'rate_tips' => Yii::t('app', 'Rate Tips'),
            'pic' => Yii::t('app', 'Pic'),
            'icon' => Yii::t('app', 'Icon'),
        ];
    }
    public function getProduct()
    {
        return $this->hasMany(QfbProduct::className(), ['category_id' => 'id']);
    }
}
