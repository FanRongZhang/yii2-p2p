<?php

namespace backend\modules\market\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbVouchers;

/**
 * VouchersSearch represents the model behind the search form about `common\models\QfbVouchers`.
 */
class VouchersSearch extends QfbVouchers
{
    public $create_time_end;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'valid_days', 'use_type', 'status', ], 'integer'],
            [['name', 'use_members'], 'safe'],
            [['money', 'use_money'], 'number'],
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
        $query = QfbVouchers::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $this->create_time = empty($params['VouchersSearch']['create_time']) ? null : strtotime($params['VouchersSearch']['create_time']);
        $this->create_time_end = empty($params['VouchersSearch']['create_time_end']) ? null : strtotime($params['VouchersSearch']['create_time_end']);
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'valid_days' => $this->valid_days,
            'money' => $this->money,
            'use_money' => $this->use_money,
            'use_type' => $this->use_type,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['>', 'create_time', $this->create_time ])
            ->andFilterWhere(['<', 'create_time', $this->create_time_end])
            ->andFilterWhere(['like', 'use_members', $this->use_members]);

        return $dataProvider;
    }
}
