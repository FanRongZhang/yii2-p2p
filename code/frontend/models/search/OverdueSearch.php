<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbProduct;
use common\models\QfbOrderOverdue;

/**
 * AdminSearch represents the model behind the search form about `backend\models\Admin`.
 */
class OverdueSearch extends QfbOrderOverdue
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['overdue_id','product_id', 'member_id', 'to_member_id', 'overdue_status', 'create_time', 'complete_time', 'overdue_day', 'profit_type'], 'integer'],
            [['o_money', 'o_interest', 'overdue_money', 'repay_money'], 'number'],
            [['sn', 'product_name'], 'string', 'max' => 60],
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
        return array_merge(parent::attributes(),['overdue_id','product_id','to_member_id',
            'overdue_status', 'create_time', 'complete_time', 'overdue_day',
            'o_money', 'o_interest', 'overdue_money', 'repay_money', 'product_name', 'profit_type']);
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
    public function search($params, $member_id)
    {

        $query = self::find();

        $query = $query->innerJoin(QfbProduct::tableName(), QfbOrderOverdue::tableName().'.product_id='.QfbProduct::tableName().'.id')
            ->select([QfbProduct::tableName().".product_name", QfbProduct::tableName().".profit_type", QfbOrderOverdue::tableName().'.status as overdue_status',  QfbOrderOverdue::tableName().'.overdue_day',
                QfbOrderOverdue::tableName().'.money as o_money',QfbOrderOverdue::tableName().'.interest as o_interest',QfbOrderOverdue::tableName().'.overdue_money',
                QfbOrderOverdue::tableName().'.repay_money', QfbOrderOverdue::tableName().'.id as overdue_id', QfbOrderOverdue::tableName().'.product_id']);
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

        $query = $query->where([QfbOrderOverdue::tableName().'.to_member_id'=>$member_id]);

        $query = $query->groupBy([QfbOrderOverdue::tableName().'.id']);

        /* 排序 */
        $query->orderBy([
            QfbOrderOverdue::tableName().".create_time" => SORT_DESC,
        ]);

        return $dataProvider;
    }

}
