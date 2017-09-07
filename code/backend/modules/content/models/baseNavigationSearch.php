<?php

namespace backend\modules\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbBaseNavigation;

/**
 * baseNavigationSearch represents the model behind the search form about `common\models\QfbBaseNavigation`.
 */
class baseNavigationSearch extends QfbBaseNavigation
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'pid', 'level', 'sort', 'status'], 'integer'],
            [['name', 'url'], 'safe'],
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
        $query = QfbBaseNavigation::find();

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
            'pid' => $this->pid,
            'level' => $this->level,
            'sort' => $this->sort,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}
