<?php

namespace backend\modules\system\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbMenu;

/**
 * MenuSearch represents the model behind the search form about `common\models\QfbMenu`.
 */
class MenuSearch extends QfbMenu
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'display', 'parent_id', 'level', 'permision_value', 'sorts'], 'integer'],
            [['name', 'url'], 'safe'],
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
        $parent_id = Yii::$app->request->get('parent_id', '0');

        $query = QfbMenu::find();

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
            'display' => $this->display,
            'parent_id' => $this->parent_id,
            'level' => $this->level,
            'permision_value' => $this->permision_value,
            'sorts' => $this->sorts,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['=', 'parent_id', $parent_id])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}
