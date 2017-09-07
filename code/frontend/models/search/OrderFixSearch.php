<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbOrderFix;

/**
 * OrderFixSeach represents the model behind the search form about `common\models\QfbOrderFix`.
 */
class OrderFixSearch extends QfbOrderFix
{
    public $product_id;
    public $status;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'member_id', 'product_id', 'status', 'create_time', 'next_profit_time', 'end_time', 'number', 'last_profit_time', 'bank_id', 'bank_type'], 'integer'],
            [['sn', 'bank_sn', 'hr_sn'], 'safe'],
            [['money', 'pay_money', 'year_rate', 'profit_money'], 'number'],
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
    public function search($params,$id)
    {
        $query = QfbOrderFix::find();
        $query->joinWith(['member']);
        $query->joinWith(['info']);
        $this->product_id=$id;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' =>8,
            ],
        ]);

        $this->load($params);

        $query->andFilterWhere([
                'in', 'status', [1,2,3]
            ]);
        /* 排序 */
        $query->orderBy([
            'create_time' => SORT_DESC,
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'member_id' => $this->member_id,
            'product_id' => $this->product_id,
            'money' => $this->money,
            'pay_money' => $this->pay_money,
            'status' => $this->status,
            'create_time' => $this->create_time,
            'next_profit_time' => $this->next_profit_time,
            'end_time' => $this->end_time,
            'year_rate' => $this->year_rate,
            'number' => $this->number,
            'last_profit_time' => $this->last_profit_time,
            'profit_money' => $this->profit_money,
            'bank_id' => $this->bank_id,
            'bank_type' => $this->bank_type,
        ]);

        $query->andFilterWhere(['like', 'sn', $this->sn])
            ->andFilterWhere(['like', 'bank_sn', $this->bank_sn])
            ->andFilterWhere(['like', 'hr_sn', $this->hr_sn]);

        return $dataProvider;
    }
}
