<?php

namespace backend\modules\system\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbChannel;

/**
 * QfbChannelSearch represents the model behind the search form about `common\models\QfbChannel`.
 */
class QfbChannelSearch extends QfbChannel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'in_status', 'out_status', 'create_time', 'sort', 'is_default'], 'integer'],
            [['name'], 'safe'],
            [['ds_rate', 'df_money'], 'number'],
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
        $query = QfbChannel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        //排序
        $query->orderBy('sort asc');

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
