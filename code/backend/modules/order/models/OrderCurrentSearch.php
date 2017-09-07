<?php

namespace backend\modules\order\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbOrder;
use common\widgets\datepicker\DatePicker;

/**
 * OrderCurrentSearch represents the model behind the search form about `common\models\QfbOrder`.
 */
class OrderCurrentSearch extends QfbOrder
{
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

        DatePicker::strToTime($this, $params, ['complete_time','complete_time_end']);//转换时间戳

        $this->load($params);

        //关联查询
        $query->joinWith('member');
        $query->joinWith('memberInfo');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'sn', $this->sn])
            ->andFilterWhere(['=', 'qfb_member.account', $this->account])
            ->andFilterWhere(['=', 'qfb_member_info.realname', $this->username])
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
        $query->andFilterWhere(['=', 'sorts', 2]);

        $query->addOrderBy(['complete_time'=>SORT_DESC]);
        return $dataProvider;
    }
}
