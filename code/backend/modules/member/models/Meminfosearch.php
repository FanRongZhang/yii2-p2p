<?php

namespace backend\modules\member\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbMember;

/**
 * Meminfosearch represents the model behind the search form about `common\models\QfbMember`.
 */
class Meminfosearch extends QfbMember
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'level', 'r_member_id', 'layer', 'channel_id', 'last_access_time', 'experience', 'is_newer'], 'integer'],
            [['relations', 'mobile', 'account', 'access_token', 'imei', 'zf_pwd', 'last_ip', 'operator'], 'safe'],
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
        $query = QfbMember::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['in', 'r_member_id', $params]);
        
        return $dataProvider;
    }
}
