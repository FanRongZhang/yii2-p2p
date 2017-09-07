<?php

namespace backend\modules\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbAgreement;
use common\widgets\datepicker\DatePicker;

/**
 * AgreementSearch represents the model behind the search form about `common\models\QfbAgreement`.
 */
class AgreementSearch extends QfbAgreement
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
            [['type'], 'integer'],
            [['title', 'content', 'pic_url'], 'safe'],
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
        $query = QfbAgreement::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);
        $this->load($params);
        if (!$this->validate()) {

            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['=', 'type' , $this->type])
            ->andFilterWhere(['>=', QfbAgreement::tableName().'.create_time' , $this->create_time])
            ->andFilterWhere(['<=', QfbAgreement::tableName().'.create_time' , $this->create_time_end]);

        return $dataProvider;
    }
}
