<?php

namespace backend\modules\order\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbPtAccount;

/**
 * PtAccountSearch represents the model behind the search form about `common\models\QfbPtAccount`.
 */
class PtAccountSearch extends QfbPtAccount
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'bank', 'is_open'], 'integer'],
            [['name', 'bank_code'], 'safe'],
            [['money', 'frozen'], 'number'],
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
        $query = QfbPtAccount::find();

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
            'money' => $this->money,
            'frozen' => $this->frozen,
            'bank' => $this->bank,
            'is_open' => $this->is_open,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'bank_code', $this->bank_code]);

        return $dataProvider;
    }
}
