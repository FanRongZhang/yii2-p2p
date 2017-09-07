<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%level}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $experience
 * @property integer $top_experience
 * @property integer $sort
 */
class QfbLevel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%level}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['experience', 'top_experience', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 8],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '等级名称'),
            'experience' => Yii::t('app', '所需成长值'),
            'top_experience' => Yii::t('app', '最高成长值'),
            'sort' => Yii::t('app', '排序从小到大'),
        ];
    }
}
