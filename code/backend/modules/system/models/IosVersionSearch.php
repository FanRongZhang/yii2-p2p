<?php

namespace backend\modules\system\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\widgets\datepicker\DatePicker;
use common\models\QfbVersion;

/**
 * IosVersionSearch represents the model behind the search form about `common\models\QfbVersion`.
 */
class IosVersionSearch extends QfbVersion
{
    public $create_time_end;
    /**
     * 继承方法
     * (non-PHPdoc)
     */
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
            [['id', 'ver_code', 'type', 'is_force', 'channel'], 'integer'],
            [['ver_name', 'content', 'url', 'imprint'], 'safe'],
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
        $query = QfbVersion::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        //ios
        $query->where(['=','type',2]);
        //排序
        $query->orderBy('create_time desc');

        DatePicker::strToTime($this, $params, ['create_time','create_time_end']);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['>=', 'create_time', $this->create_time])
            ->andFilterWhere(['<=', 'create_time', $this->create_time_end]);
            
        return $dataProvider;
    }
}
