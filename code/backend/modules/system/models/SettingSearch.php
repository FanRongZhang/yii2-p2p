<?php

namespace backend\modules\system\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbSystemSettings;

/**
 * SettingSearch represents the model behind the search form about `common\models\QfbSystemSettings`.
 */
class SettingSearch extends QfbSystemSettings
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'open_start_time', 'open_end_time'], 'integer'],
            [['min_money', 'money_fee', 'fast_rate', 'slow_rate', 'per_money', 'day_money'], 'number'],
            [['operator', 'close_content'], 'safe'],
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
        $query = QfbSystemSettings::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'min_money' => $this->min_money,
            'money_fee' => $this->money_fee,
            'fast_rate' => $this->fast_rate,
            'slow_rate' => $this->slow_rate,
            'per_money' => $this->per_money,
            'day_money' => $this->day_money,
            'open_start_time' => $this->open_start_time,
            'open_end_time' => $this->open_end_time,
        ]);

        $query->andFilterWhere(['like', 'operator', $this->operator])
            ->andFilterWhere(['like', 'close_content', $this->close_content]);

        return $dataProvider;
    }
}
