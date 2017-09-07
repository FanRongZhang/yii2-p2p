<?php

namespace backend\modules\messages\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbNotice;
use common\widgets\datepicker\DatePicker;

/**
 * NoticeSearch represents the model behind the search form about `common\models\QfbNotice`.
 */
class NoticeSearch extends QfbNotice
{

    public $send_time_end;

    public function attributeLabels()
    {
        $data = parent::attributeLabels();
        $data['send_time_end']= Yii::t('app', '至');
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['title', 'summary', 'content'], 'safe'],
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
        $query = QfbNotice::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        DatePicker::strToTime($this, $params, ['send_time','send_time_end']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['>=', QfbNotice::tableName().'.send_time' , $this->send_time])
            ->andFilterWhere(['<=', QfbNotice::tableName().'.send_time' , $this->send_time_end]);

        return $dataProvider;
    }
}
