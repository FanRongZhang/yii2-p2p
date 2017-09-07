<?php

namespace backend\modules\order\models;

use common\models\QfbProduct;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbOrderRepayment;
use common\widgets\datepicker\DatePicker;

/**
 * OrderMoneySearch represents the model behind the search form about `common\models\QfbOrder`.
 */
class OrderRepaymentSearch extends QfbOrderRepayment
{
    public $create_time_end;
    public $complete_time_end;
    public $account;
    public $username;
    public $product_name;
    public $mark;
    public $numbers;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'member_id', 'finish_time'], 'integer'],
            [['sn', 'account', 'username', 'product_name','create_time','create_time_end','complete_time','complete_time_end','profit_day'], 'safe'],
            [['money', 'interest'], 'number'],
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['product_name', 'finish_time','profit_day']);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'account' => '会员账号',
                'username'=> '会员姓名',
                'create_time_end' => '至',
                'complete_time_end' => '至',
                'product_name' => '产品名称'
            ]
        );
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
        ]);

        //关联查询
        $query->from(QfbOrderRepayment::tableName())
            ->joinWith('product')
            ->joinWith('member')
            ->joinWith('info')
            ->select([QfbOrderRepayment::tableName().'.*',QfbProduct::tableName().'.product_name',
                QfbProduct::tableName().'.finish_time', QfbProduct::tableName().'.profit_day']);

        //排序
        $query->orderBy('create_time desc');

        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', QfbOrderRepayment::tableName().'.sn', $this->sn])
            ->andFilterWhere(['=', QfbOrderRepayment::tableName().'.status', $this->status])
            ->andFilterWhere(['>=', QfbOrderRepayment::tableName().'.create_time', $this->create_time])
            ->andFilterWhere(['<=', QfbOrderRepayment::tableName().'.create_time', $this->create_time_end])
            ->andFilterWhere(['>=', QfbOrderRepayment::tableName().'.confirm_time', $this->complete_time])
            ->andFilterWhere(['<=', QfbOrderRepayment::tableName().'.confirm_time', $this->complete_time_end])
            ->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['like', 'realname', $this->username])
            ->andFilterWhere(['like', 'account', $this->account]);
        //订单金额
        if($this->mark && $this->numbers>0){
            switch ($this->mark){
                case 1 :
                    $query->andFilterWhere(['>', 'money', $this->numbers]);
                    break;
                case 2 :
                    $query->andFilterWhere(['=', 'money', $this->numbers]);
                    break;
                case 3 :
                    $query->andFilterWhere(['<', 'money', $this->numbers]);
                    break;
            }
        }

        return $dataProvider;
    }

}
