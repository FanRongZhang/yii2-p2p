<?php

namespace backend\modules\order\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbTemporaryReconciliation;

/**
 * ReconciliationSearch represents the model behind the search form about `common\models\QfbReconciliationLog`.
 */
class TemporaryReconciliationSearch extends QfbTemporaryReconciliation
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_name', 'withhold_time'], 'required'],
            [['file_type', 'status', 'withhold_time'], 'integer'],
            [['remark'], 'string'],
            [['file_name'], 'string', 'max' => 30],
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
        $query = QfbTemporaryReconciliation::find()->orderBy('file_name desc, file_type asc');

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
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'withhold_time' => $this->withhold_time,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }
}
