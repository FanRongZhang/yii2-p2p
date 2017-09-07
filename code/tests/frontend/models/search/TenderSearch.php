<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbProduct;

/**
 * AdminSearch represents the model behind the search form about `backend\models\Admin`.
 */
class TenderSearch extends QfbProduct
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'type'], 'safe'],
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
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(),[
            'start_time','end_time','type',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'start_time' => '开始时间',
            'end_time' => '结束时间',
        ]);
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

        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query = $query->where(['member_id'=>$this->member_id]);

        if($this->type == 1){
            $query = $query->andFilterWhere(['=', "status", 2]);
        }elseif($this->type == 2){
            $query = $query->andFilterWhere(['=', "status", 6]);
        }else{
            $query = $query->andFilterWhere(['in', "status", [2,5,6]]);
        }

        if(!empty($this->start_time)){
            $query->andFilterWhere([
                '>', 'create_time', strtotime($this->start_time)
            ]);
        }

        if(!empty($this->end_time)){
            $query->andFilterWhere([
                '<', 'create_time', strtotime($this->end_time)+24*3600
            ]);
        }

        /* 排序 */
        $query->orderBy([
            "id" => SORT_DESC,
        ]);

        return $dataProvider;
    }

}
