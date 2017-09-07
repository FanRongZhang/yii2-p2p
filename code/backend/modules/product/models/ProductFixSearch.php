<?php

namespace backend\modules\product\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbProduct;
use common\widgets\datepicker\DatePicker;
use common\models\QfbProduct as Product;
/**
 * ProductFixSearch represents the model behind the search form about `common\models\QfbProduct`.
 */
class ProductFixSearch extends QfbProduct
{
    /**
     * 截至时间
     * @var unknown
     */
    public $create_time_end;
    public $end_time_end;

    /**
     * 继承方法
     * (non-PHPdoc)
     */
     public function attributeLabels()
     {
         $data = parent::attributeLabels();
         $data['create_time_end']= '至';
         $data['end_time_end']= '至';
         return $data;
     }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'product_type', 'category_id', 'can_rate_ticket', 'can_money_ticket', 'profit_type', 'is_newer', 'lock_day', 'invest_day', 'profit_day', 'status', 'is_index', 'is_hidden'], 'integer'],
            [['sn', 'product_name'], 'safe'],
            [['min_money', 'max_money', 'step_money', 'has_money', 'stock_money', 'year_rate'], 'number'],
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
        $query = QfbProduct::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //'pagination' => ['pageSize' => 10,]
        ]);
        //关联查询
        $query->joinWith('profit_settings')
              ->joinWith('product_detail');

        //排序
        $query->orderBy('create_time desc');

        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);
        DatePicker::strToTime($this, $params, ['end_time','end_time_end']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'product_name', $this->product_name])
            ->andFilterWhere(['=', Product::tableName().'.product_type', $this->product_type])
            ->andFilterWhere(['=', Product::tableName().'.status', $this->status])
            ->andFilterWhere(['=', 'profit_type', $this->profit_type])
            ->andFilterWhere(['>=', 'create_time', $this->create_time])
            ->andFilterWhere(['<=', 'create_time', $this->create_time_end])
            ->andFilterWhere(['>=', 'end_time', $this->end_time])
            ->andFilterWhere(['<=', 'end_time', $this->end_time_end]);

        return $dataProvider;
    }
}
