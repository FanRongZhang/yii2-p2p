<?php

namespace backend\modules\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbArticle;
use common\widgets\datepicker\DatePicker;

/**
 * ArticleSearch represents the model behind the search form about `common\models\QfbArticle`.
 */
class ArticleSearch extends QfbArticle
{
    public $create_time_end;

    public function attributeLabels()
    {
        $data = parent::attributeLabels();
        $data['create_time_end']= Yii::t('app', '至');
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'operator_id', 'update_time', 'sortord'], 'integer'],
            [['title', 'content'], 'safe'],
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
        $query = QfbArticle::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['>=', QfbArticle::tableName().'.create_time' , $this->create_time])
            ->andFilterWhere(['<=', QfbArticle::tableName().'.create_time' , $this->create_time_end]);

        return $dataProvider;
    }
}
