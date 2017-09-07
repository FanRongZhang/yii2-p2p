<?php

namespace backend\modules\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbAboutMe;

/**
 * aboutMeSearch represents the model behind the search form about `common\models\QfbAboutMe`.
 */
class aboutMeSearch extends QfbAboutMe
{
    public $thumbnail;
    public function attributeLabels()
    {
        $data = parent::attributeLabels();
        $data['thumbnail']= '头像';
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'sort'], 'integer'],
            [['name', 'position', 'image', 'content'], 'safe'],
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
        $query = QfbAboutMe::find();

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
            'status' => $this->status,
            'sort' => $this->sort,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'position', $this->position])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
