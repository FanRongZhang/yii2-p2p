<?php

namespace frontend\models\search;

use common\models\QfbOrderRepayment;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbProduct;

/**
 * AdminSearch represents the model behind the search form about `backend\models\Admin`.
 */
class RepaymentSearch extends QfbOrderRepayment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'type', 'product_name','profit_type','profit_day','finish_time','product_status'], 'safe'],
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
            'start_time','end_time','type','product_name','profit_type','profit_day','finish_time','product_status'
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

        $query = self::find()->from(QfbProduct::tableName().' a')->rightJoin(QfbOrderRepayment::tableName().' b', 'b.product_id=a.id')
            ->select(['b.*','a.product_name','a.profit_type','a.profit_day','a.finish_time','a.status as product_status']);

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

        $query = $query->where(['b.member_id'=>$member_id]);

        if($type == 1){
            $query = $query->andFilterWhere(['in', "a.status", [1,2,5,6,7]]);
        }elseif($type == 2){
            $query = $query->andFilterWhere(['=', "a.status", 8]);
        }else{
            $query = $query->andFilterWhere(['in', "a.status", [1,2,5,6,7,8]]);
        }

        if(!empty($this->start_time)){
            $query->andFilterWhere([
                '>', 'a.create_time', strtotime($this->start_time)
            ]);
        }

        if(!empty($this->end_time)){
            $query->andFilterWhere([
                '<', 'a.create_time', strtotime($this->end_time)+24*3600
            ]);
        }

        $query->groupBy('b.id');

        /* 排序 */
        $query->orderBy([
            "b.id" => SORT_DESC,
            'b.periods' => SORT_ASC,
        ]);

        return $dataProvider;
    }

}
