<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbOrderFix;
use common\models\QfbProduct;

/**
 * AdminSearch represents the model behind the search form about `backend\models\Admin`.
 */
class InvestSearch extends QfbProduct
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
        return array_merge(parent::attributes(), [
            'start_time','end_time','type','order_create_time', 'invest_day', 'profit_type'
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

        $productTable = QfbProduct::tableName();
        $orderFixTable = QfbOrderFix::tableName();

        $query = self::find()
            ->from("$productTable")
            ->RightJoin("$productTable", "$orderFixTable.product_id=$productTable.id")
            ->select(["$productTable.*", "$orderFixTable.create_time as order_create_time", "$orderFixTable.money as money"]);


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

        if($this->type == 1){
            $query = $query->orFilterWhere(['in', "$productTable.status", [1,2]]);
        }elseif($this->type == 2){
            $query = $query->where(["$productTable.status"=>4]);
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

        $query->groupBy("$productTable.id");

        /* 排序 */
        $query->orderBy([
            "$productTable.id" => SORT_DESC,
        ]);

        return $dataProvider;
    }

}
