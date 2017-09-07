<?php

namespace backend\modules\messages\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbMessage;
use common\widgets\datepicker\DatePicker;

/**
 * MessageSearch represents the model behind the search form about `common\models\QfbMessage`.
 */
class MessageSearch extends QfbMessage
{
    public $send_time_end;

    public function attributeLabels()
    {
        $data = parent::attributeLabels();
        $data['send_time_end']= Yii::t('app', '至');
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'send_ob', 'send_mode', 'send_type', ], 'integer'],
            [['title', 'content', 'send_ob_value'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = QfbMessage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        DatePicker::strToTime($this, $params, ['send_time','send_time_end']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['>=', QfbMessage::tableName().'.send_time' , $this->send_time])
            ->andFilterWhere(['<=', QfbMessage::tableName().'.send_time' , $this->send_time_end]);

        return $dataProvider;
    }
}
