<?php

namespace backend\modules\member\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\QfbMemberVouchers;

/**
 * Meminfosearch represents the model behind the search form about `common\models\QfbMember`.
 */
class MemberVoucherSearch extends QfbMemberVouchers
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'vouchers_id', 'member_id', 'status', 'receive_time', 'invalid_time', 'product_id'], 'integer'],
            [['remark', 'sn'], 'safe'],
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
        $query = QfbMemberVouchers::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        //关联
        $query->joinWith('vouchers');

        //排序
        $query->orderBy('receive_time desc');

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['=', 'member_id', $params]);
        
        return $dataProvider;
    }
}
