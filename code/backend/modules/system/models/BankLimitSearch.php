<?php

namespace backend\modules\system\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbBankLimit;

/**
 * BankLimitSearch represents the model behind the search form about `common\models\QfbBankLimit`.
 */
class BankLimitSearch extends QfbBankLimit
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'trade_num', 'is_support', 'pt_type'], 'integer'],
            [['name', 'create_user', 'iss_users', 'bank_abbr'], 'safe'],
            [['one_trade', 'day_trade', 'month_trade'], 'number'],
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
        $id = Yii::$app->request->get('pt_type');
        $query = QfbBankLimit::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['=', 'pt_type', $id]);

        return $dataProvider;
    }
}
