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
class InvestSearch extends QfbOrderFix
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
            'start_time','end_time','type','order_create_time', 'invest_day', 'profit_type', 'profit_day', 'finish_time',
            'product_status','order_end_time','order_invest_day','day_interest'
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
    public function search($params, $member_id, $type=0)
    {

        $productTable = QfbProduct::tableName();
        $orderFixTable = QfbOrderFix::tableName();

        $query = self::find()
            ->from("$orderFixTable")
            ->RightJoin("$productTable", "$orderFixTable.product_id=$productTable.id")
            ->select(["$productTable.*", "$productTable.id as product_id", "$orderFixTable.create_time as order_create_time",
                "$orderFixTable.end_time as order_end_time", "$orderFixTable.money as money", "$productTable.status as product_status", "$orderFixTable.id",
            "$productTable.invest_day as order_invest_day", "$orderFixTable.day_interest","$orderFixTable.status"]);

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

        $query = $query->where(["$orderFixTable.member_id"=>$member_id])
            ->andFilterWhere(["in","$orderFixTable.status", [1,2,3]]);

        if($type == 1){
            $query = $query->andFilterWhere(['in', "$productTable.status", [0,1,2,5,6,7]]);
        }elseif($type == 2){
            $query = $query->andFilterWhere(["=","$productTable.status",8]);
        }else{
            $query = $query->andFilterWhere(['in', "$productTable.status", [0,1,2,5,6,7,8]]);
        }

        if(!empty($this->start_time)){
            $query->andFilterWhere([
                '>', "$orderFixTable.create_time", strtotime($this->start_time)
            ]);
        }

        if(!empty($this->end_time)){
            $query->andFilterWhere([
                '<', "$orderFixTable.create_time", strtotime($this->end_time)+24*3600
            ]);
        }

        /* 排序 */
        $query->orderBy([
            "$orderFixTable.id" => SORT_DESC,
        ]);

        return $dataProvider;
    }

}
