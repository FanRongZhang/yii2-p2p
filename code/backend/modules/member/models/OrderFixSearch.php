<?php

namespace backend\modules\member\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\widgets\datepicker\DatePicker;
use common\models\QfbOrderFix;
use common\models\QfbMember;
/**
 * OrderFixSearch represents the model behind the search form about `common\models\QfbOrderFix`.
 */
class OrderFixSearch extends QfbOrderFix
{
    public $create_time_end;
    public $mark;
    public $numbers;

    /**
     * 继承方法
     * (non-PHPdoc)
     */
     public function attributeLabels()
     {
         $data = parent::attributeLabels();
         $data['create_time_end']= '至';
         $data['mark']= '金额';
         $data['numbers'] = '';
         return $data;
     }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'member_id', 'product_id', 'status', 'next_profit_time', 'end_time', 'number', 'last_profit_time','mark'], 'integer'],
            [['sn','product_name','account','realname'], 'safe'],
            [['money', 'pay_money', 'year_rate', 'profit_money','numbers'], 'number'],
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
    public function search($id)
    {
        $query = QfbOrderFix::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        //关联查询
        $query->joinWith('product')
              ->joinWith('member')
              ->joinWith('info');
        $query->andWhere(["in",QfbOrderFix::tableName().'.status',[1,2]]);
        $query->andWhere(["=",QfbOrderFix::tableName().'.member_id',$id]);
        //排序
        $query->orderBy('create_time desc');

        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', QfbOrderFix::tableName().'.sn', $this->sn])
              ->andFilterWhere(['=', QfbOrderFix::tableName().'.status', $this->status])
              ->andFilterWhere(['>=', QfbOrderFix::tableName().'.create_time', $this->create_time])
              ->andFilterWhere(['<=', QfbOrderFix::tableName().'.create_time', $this->create_time_end])
              ->andFilterWhere(['like', 'product_name', $this->product_name])
              ->andFilterWhere(['like', 'account', $this->account])
              ->andFilterWhere(['like', 'realname', $this->realname]);
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
