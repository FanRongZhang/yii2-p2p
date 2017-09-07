<?php

namespace backend\modules\market\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbExperienceMoney;

/**
 * ExperienceSearch represents the model behind the search form about `common\models\QfbExperienceMoney`.
 */
class ExperienceSearch extends QfbExperienceMoney
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'valid_days', 'status', 'create_time', 'start_time', 'end_time'], 'integer'],
            [['name', 'use_members'], 'safe'],
            [['money'], 'number'],
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
        $query = QfbExperienceMoney::find();

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
            'type' => $this->type,
            'valid_days' => $this->valid_days,
            'money' => $this->money,
            'status' => $this->status,
            'create_time' => $this->create_time,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'use_members', $this->use_members]);

        return $dataProvider;
    }
}
