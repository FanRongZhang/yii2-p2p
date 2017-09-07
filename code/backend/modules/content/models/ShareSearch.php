<?php

namespace backend\modules\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbShare;
use common\widgets\datepicker\DatePicker;
/**
 * ShareSearch represents the model behind the search form about `common\models\Qfbshare`.
 */
class ShareSearch extends QfbShare
{
    public $create_time_end;

    public function attributeLabels()
    {
        $data = parent::attributeLabels();
        $data['create_time_end']= Yii::t('app', '至');
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'pic_url', 'url'], 'safe'],
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
        $query = Qfbshare::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);
        $this->load($params);

        if (!$this->validate()) {

            return $dataProvider;
        }


        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['>=', Qfbshare::tableName().'.create_time' , $this->create_time])
            ->andFilterWhere(['<=', Qfbshare::tableName().'.create_time' , $this->create_time_end]);

        return $dataProvider;
    }
}
