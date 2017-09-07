<?php

namespace backend\modules\member\models;

use common\models\Vmember;
use common\widgets\datepicker\DatePicker;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbMember;

/**
 * Membersearch represents the model behind the search form about `common\models\QfbMember`.
 */
class Membersearch extends Vmember
{
    public $create_time_end;

    public function attributeLabels()
    {
        $data = parent::attributeLabels();
        $data['create_time_end']= '至';
        return $data;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vid', 'vlevel', 'vr_member_id', 'vchannel_id', 'vlast_access_time', 'vstatus', 'vsource', 'vis_dredge', 'vmember_type'], 'integer'],
            [['vmobile', 'vlast_ip'], 'string', 'max' => 20],
            [['vaccount'], 'string', 'max' => 50],
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
        // var_dump($params);
        // exit;
        $query = Vmember::find()->orderBy(["vcreate_time"=>SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        DatePicker::strToTime($this, $params, ['vcreate_time','create_time_end']);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query ->andFilterWhere(['>=', Vmember::tableName().'.vcreate_time', $this->vcreate_time])
        ->andFilterWhere(['<=', Vmember::tableName().'.vcreate_time', $this->create_time_end])
        ->andFilterWhere(['=', Vmember::tableName().'.vsource', $this->vsource])
        ->andFilterWhere(['=', Vmember::tableName().'.vstatus', $this->vstatus])
        ->andFilterWhere(['=', Vmember::tableName().'.vlevel', $this->vlevel])
        ->andFilterWhere(['=', Vmember::tableName().'.vis_dredge', $this->vis_dredge])

        ->andFilterWhere(['like', 'vmobile', $this->vmobile])
            ->andFilterWhere(['like', 'vaccount', $this->vaccount]);

        return $dataProvider;
    }

}
