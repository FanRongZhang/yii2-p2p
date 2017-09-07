<?php

namespace backend\modules\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbPcImage;

/**
 * PcImageSearch represents the model behind the search form about `common\models\QfbPcImage`.
 */
class PcImageSearch extends QfbPcImage
{
    public $type='';
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'time', 'sort', 'status'], 'integer'],
            [['name', 'image', 'url'], 'safe'],
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
    public function search($params,$type)
    {
        $query = QfbPcImage::find();

        $this->type=$type;

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
            'time' => $this->time,
            'sort' => $this->sort,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}
