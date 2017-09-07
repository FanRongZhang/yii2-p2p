<?php

namespace backend\modules\order\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbPtOrder;

/**
 * PtOrderSearch represents the model behind the search form about `common\models\QfbPtOrder`.
 */
class PtOrderSearch extends QfbPtOrder
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'is_check', 'create_time', 'complete_time', 'sorts', 'bank_type', 'out_type'], 'integer'],
            [['sn', 'pt_number'], 'safe'],
            [['price', 'fee', 'money'], 'number'],
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
        $query = QfbPtOrder::find();

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
            'price' => $this->price,
            'is_check' => $this->is_check,
            'create_time' => $this->create_time,
            'complete_time' => $this->complete_time,
            'sorts' => $this->sorts,
            'fee' => $this->fee,
            'money' => $this->money,
            'bank_type' => $this->bank_type,
            'out_type' => $this->out_type,
        ]);

        $query->andFilterWhere(['like', 'sn', $this->sn])
            ->andFilterWhere(['like', 'pt_number', $this->pt_number]);

        return $dataProvider;
    }
}
