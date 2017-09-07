<?php

namespace backend\modules\feedback\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbFeedback;
use common\models\QfbMember;
use common\widgets\datepicker\DatePicker;
/**
 * FeedbackSearch represents the model behind the search form about `common\models\QfbFeedback`.
 */
class FeedbackSearch extends QfbFeedback
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
            [['id', 'member_id', 'reply', 'pid', 'is_read'], 'integer'],
            [['title', 'content','create_time','create_time_end'], 'safe'],
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
        $query = QfbFeedback::find();

        $query = $query->where([QfbFeedback::tableName().'.pid'=>0]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->joinWith('member');
        $query->orderBy('create_time desc');

        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['=', QfbMember::tableName().'.mobile', $this->member_id])
            ->andFilterWhere(['=', 'reply', $this->reply])
            ->andFilterWhere(['>=', 'create_time' , $this->create_time])
            ->andFilterWhere(['<=', 'create_time' , $this->create_time_end]);

        return $dataProvider;
    }
}
