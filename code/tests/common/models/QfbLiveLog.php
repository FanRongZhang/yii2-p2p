<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%live_log}}".
 *
 * @property integer $id
 * @property string $year_rate
 * @property integer $create_time
 * @property string $recommend_rate
 * @property integer $operator
 */
class QfbLiveLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%live_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year_rate', 'recommend_rate'], 'number'],
            [['create_time', 'operator'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'id'),
            'year_rate' => Yii::t('app', '年化收益率'),
            'create_time' => Yii::t('app', '记录时间'),
            'recommend_rate' => Yii::t('app', '三级分润、管理奖奖励收益率'),
            'operator' => Yii::t('app', '最后操作人'),
        ];
    }
}
