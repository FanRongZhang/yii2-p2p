<?php

namespace backend\modules\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbBanner;
use common\widgets\datepicker\DatePicker;
/**
 * BannerSearch represents the model behind the search form about `common\models\QfbBanner`.
 */
class BannerSearch extends QfbBanner 
{

    public $display_time_end;

    public function attributeLabels()
    {
        $data = parent::attributeLabels();
        $data['display_time_end']= Yii::t('app', '至');
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'location_push', 'status', 'create_time', 'type', 'share_type', 'sortord'], 'integer'],
            [['name', 'imgurl', 'linkurl'], 'safe'],
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
        $query = QfbBanner::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        DatePicker::strToTime($this, $params, ['display_start_time','display_time_end']);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['=', 'location_push', $this->location_push])
            ->andFilterWhere(['>=', QfbBanner::tableName().'.display_start_time' , $this->display_start_time])
            ->andFilterWhere(['<=', QfbBanner::tableName().'.display_start_time' , $this->display_time_end]);

        return $dataProvider;
    }
}
