<?php

namespace backend\modules\system\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbErrorMsg;

/**
 * ErrorMsgSearch represents the model behind the search form about `common\models\QfbErrorMsg`.
 */
class ErrorMsgSearch extends QfbErrorMsg
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'channel_id', 'create_time'], 'integer'],
            [['code', 'msg'], 'safe'],
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
        $query = QfbErrorMsg::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        //排序
        $query->orderBy('create_time desc');

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['=', 'channel_id', $this->channel_id]);

        return $dataProvider;
    }
}
