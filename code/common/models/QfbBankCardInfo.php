<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%bank_card_info}}".
 *
 * @property double $id
 * @property string $bank_id
 * @property string $iss_users
 * @property string $card_no
 * @property double $card_len
 * @property string $card_bin
 * @property string $card_name
 * @property string $bank_name
 * @property string $branch_id
 * @property string $branch_id2
 * @property integer $card_type
 * @property string $card_org
 * @property integer $card_tag
 * @property integer $card_tag2
 */
class QfbBankCardInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_card_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'card_len'], 'number'],
            [['card_type', 'card_tag', 'card_tag2'], 'integer'],
            [['bank_id', 'card_bin'], 'string', 'max' => 135],
            [['iss_users'], 'string', 'max' => 90],
            [['card_no'], 'string', 'max' => 270],
            [['card_name', 'bank_name'], 'string', 'max' => 450],
            [['branch_id', 'branch_id2'], 'string', 'max' => 108],
            [['card_org'], 'string', 'max' => 36],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_id' => 'Bank ID',
            'iss_users' => 'Iss Users',
            'card_no' => 'Card No',
            'card_len' => 'Card Len',
            'card_bin' => 'Card Bin',
            'card_name' => 'Card Name',
            'bank_name' => 'Bank Name',
            'branch_id' => 'Branch ID',
            'branch_id2' => 'Branch Id2',
            'card_type' => 'Card Type',
            'card_org' => 'Card Org',
            'card_tag' => 'Card Tag',
            'card_tag2' => 'Card Tag2',
        ];
    }
}
