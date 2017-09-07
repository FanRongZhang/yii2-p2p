<?php

namespace backend\modules\order\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbOrder;
use common\widgets\datepicker\DatePicker;

/**
 * OrderMoneySearch represents the model behind the search form about `common\models\QfbOrder`.
 */
class OrderMoneySearch extends QfbOrder
{
    public $create_time_end;
    public $complete_time_end;
    public $account;
    public $username;
    public $mark;
    public $numbers;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'member_id', 'is_check', 'type', 'sorts', 'bank_id', 'bank_type', 'out_type','mark'], 'integer'],
            [['sn', 'account', 'username'], 'safe'],
            [['price', 'fee', 'money','numbers'], 'number'],
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
        $query = QfbOrder::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        DatePicker::strToTime($this, $params, ['create_time','create_time_end','complete_time','complete_time_end']);//转换时间戳

        $this->load($params);

        //关联查询
        $query->joinWith('member');
        $query->joinWith('bank');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'sn', $this->sn])
            ->andFilterWhere(['=', 'qfb_member.account', $this->account])
            ->andFilterWhere(['=', 'qfb_bank.username', $this->username])
            ->andFilterWhere(['=', 'bank_type', $this->bank_type])
            ->andFilterWhere(['=', 'is_check', $this->is_check])
            ->andFilterWhere(['=', 'out_type', $this->out_type])
            ->andFilterWhere(['BETWEEN', 'qfb_order.create_time', $this->create_time,$this->create_time_end])
            ->andFilterWhere(['BETWEEN', 'qfb_order.complete_time', $this->complete_time,$this->complete_time_end]);

        if($this->mark && $this->numbers>0){
            switch ($this->mark){
                case 1 :
                    $query->andFilterWhere(['>', 'price', $this->numbers]);
                    break;
                case 2 :
                    $query->andFilterWhere(['=', 'price', $this->numbers]);
                    break;
                case 3 :
                    $query->andFilterWhere(['<', 'price', $this->numbers]);
                    break;
            }
        }

        $query->andFilterWhere(['=', 'type', $this->type]);
        $query->andFilterWhere(['=', 'sorts', 1]);

        $query->orderBy(['create_time'=>SORT_DESC]);
        return $dataProvider;
    }

    public function searchAll($params)
    {
        $query = QfbOrder::find();

        DatePicker::strToTime($this, $params, ['create_time','create_time_end','complete_time','complete_time_end']);//转换时间戳

        $this->load($params);

        //关联查询
        $query->joinWith('member');

        $query->andFilterWhere(['like', 'sn', $this->sn])
            ->andFilterWhere(['=', 'qfb_member.account', $this->account])
            ->andFilterWhere(['=', 'qfb_bank.username', $this->username])
            ->andFilterWhere(['=', 'bank_type', $this->bank_type])
            ->andFilterWhere(['=', 'is_check', $this->is_check])
            ->andFilterWhere(['=', 'out_type', $this->out_type])
            ->andFilterWhere(['=', 'sorts', 1])
            ->andFilterWhere(['=', 'type', $this->type])
            ->andFilterWhere(['BETWEEN', 'qfb_order.create_time', $this->create_time,$this->create_time_end])
            ->andFilterWhere(['BETWEEN', 'qfb_order.complete_time', $this->complete_time,$this->complete_time_end]);

        return $query;
    }

}
