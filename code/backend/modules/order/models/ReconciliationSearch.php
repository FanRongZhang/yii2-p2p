<?php

namespace backend\modules\order\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbReconciliationLog;

/**
 * ReconciliationSearch represents the model behind the search form about `common\models\QfbReconciliationLog`.
 */
class ReconciliationSearch extends QfbReconciliationLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'create_time', 'status', 'r_id'], 'integer'],
            [['ls_sn', 'remark'], 'safe'],
            [['platform_money', 'account_money'], 'number'],
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
        $id = Yii::$app->request->get('r_id');
        // var_dump($id);die;

        $query = QfbReconciliationLog::find();

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
            'platform_money' => $this->platform_money,
            'account_money' => $this->account_money,
            'type' => $this->type,
            'create_time' => $this->create_time,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'ls_sn', $this->ls_sn])
            ->andFilterWhere(['=', 'r_id', $id])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }
}
