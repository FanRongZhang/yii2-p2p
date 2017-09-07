<?php

namespace frontend\models\search;

use common\models\QfbMoneyLog;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbOrder;

/**
 * AdminSearch represents the model behind the search form about `backend\models\Admin`.
 */
class OrderSearch extends QfbMoneyLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time'], 'safe'],
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
        'start_time','end_time',
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
    public function search($params, $member_id, $member_type=1)
    {

        $query = new QfbMoneyLog($member_id);

        $query = $query->find();

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

        $query = $query->where(['member_id'=>$member_id]);

        $query = $query->andFilterWhere(['<>', 'money', 0.00]);

        if($member_type == 1){
            $query->andFilterWhere([
                'in', 'action', [2,7,16,12]
            ]);
        }else{
            $query->andFilterWhere([
                'in', 'action', [2,7,22,21]
            ]);
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
            'create_time' => SORT_DESC,
        ]);

        return $dataProvider;
    }

}
