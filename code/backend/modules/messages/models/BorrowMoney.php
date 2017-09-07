<?php

namespace backend\modules\messages\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbBorrowMoney;

/**
 * BorrowMoney represents the model behind the search form about `common\models\QfbBorrowMoney`.
 */
class BorrowMoney extends QfbBorrowMoney
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'sey', 'tel', 'status', 'time', 'reply_time'], 'integer'],
            [['money'], 'number'],
            [['guarantee', 'purpose', 'name'], 'safe'],
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
        $query = QfbBorrowMoney::find();

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
            'money' => $this->money,
            'sey' => $this->sey,
            'tel' => $this->tel,
            'status' => $this->status,
            'time' => $this->time,
            'reply_time' => $this->reply_time,
        ]);

        $query->andFilterWhere(['like', 'guarantee', $this->guarantee])
            ->andFilterWhere(['like', 'purpose', $this->purpose])
            ->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
