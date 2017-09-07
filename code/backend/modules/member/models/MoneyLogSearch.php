<?php

namespace backend\modules\member\models;

use common\models\moneyLog\MoneyLog;
use common\models\QfbMoneyLog;
use common\models\Vmember;
use common\widgets\datepicker\DatePicker;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbMember;

/**
 * Membersearch represents the model behind the search form about `common\models\QfbMember`.
 */
class MoneyLogSearch extends QfbMoneyLog
{
    public $create_time_end;

    public function attributeLabels()
    {
        $data = parent::attributeLabels();
        $data['create_time_end']= '至';
        $data['type']= '收支类型';
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['money', 'old_money','type'], 'number'],
            [['remark'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    /*public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }*/

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = QfbMoneyLog::find()->where(['member_id'=>$params['id']])->orderBy(["create_time"=>SORT_DESC]);
        unset($params['id']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query ->andFilterWhere(['>=', QfbMoneyLog::tableName().'.create_time', $this->create_time])
        ->andFilterWhere(['<=', QfbMoneyLog::tableName().'.create_time', $this->create_time_end]);

            $query->andFilterWhere(['=', QfbMoneyLog::tableName().'.type', $this->type]);


        return $dataProvider;
    }

}
