<?php

namespace backend\modules\system\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbAdmin;

/**
 * AdminSearch represents the model behind the search form about `common\models\QfbAdmin`.
 */
class AdminSearch extends QfbAdmin
{
    /**
     * 截至时间
     * @var unknown
     */
    public $last_login_end;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'enabled', 'is_sys'], 'integer'],
            [['account', 'password', 'permission', 'true_name'], 'safe'],
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
        $query = QfbAdmin::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        
        $this->last_login = empty($params['AdminSearch']['last_login']) ? null : strtotime($params['AdminSearch']['last_login']);
        $this->last_login_end = empty($params['AdminSearch']['last_login_end']) ? null : strtotime($params['AdminSearch']['last_login_end']);
        
        $query->andFilterWhere([
            'enabled' => $this->enabled,
            'is_sys' => $this->is_sys,
        ]);

        $query->andFilterWhere(['like', 'account', $this->account])
            ->andFilterWhere(['>', 'last_login', $this->last_login ])
            ->andFilterWhere(['<', 'last_login', $this->last_login_end]);
        return $dataProvider;
    }
}
