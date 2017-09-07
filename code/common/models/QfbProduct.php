<?php

namespace common\models;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "{{%product}}".
 *
 * @property integer $id
 * @property string $sn
 * @property integer $product_type
 * @property string $product_name
 * @property integer $category_id
 * @property string $min_money
 * @property string $max_money
 * @property string $step_money
 * @property string $has_money
 * @property string $stock_money
 * @property string $year_rate
 * @property string $admin_rate
 * @property string $share_rate
 * @property integer $can_rate_ticket
 * @property integer $can_money_ticket
 * @property integer $profit_type
 * @property integer $is_newer
 * @property integer $lock_day
 * @property integer $invest_day
 * @property integer $profit_day
 * @property integer $status
 * @property integer $create_time
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $finish_time
 * @property integer $is_index
 * @property integer $is_hidden
 *
 * @property ProductDetail $productDetail
 */
class QfbProduct extends \yii\db\ActiveRecord
{
    public $recommond_rate;
    public $manage_rate;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sn', 'product_type', 'product_name', 'min_money', 'max_money', 'step_money', 'has_money', 'stock_money', 'year_rate', 'lock_day', 'invest_day', 'profit_day', 'status','end_time','category_id', 'profit_type','is_newer','is_index','is_hidden','can_rate_ticket','can_money_ticket','member_id','platform_income', 'warranty_type', 'address'], 'required'],
            [['product_type', 'can_rate_ticket', 'can_money_ticket', 'is_newer', 'lock_day', 'invest_day', 'profit_day', 'status','is_index', 'is_hidden', 'member_id', 'credit_time', 'repayment_time', 'warranty_type'], 'integer'],
            [['min_money', 'max_money', 'step_money', 'has_money', 'stock_money', 'year_rate','recommond_rate','manage_rate', 'platform_income', 'platform_income_rate'], 'number'],
            [['sn', 'product_name'], 'string', 'max' => 30],
            [['address'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '产品id'),
            'sn' => Yii::t('app', '产品编号'),
            'product_type' => Yii::t('app', '产品类型'),
            'product_name' => Yii::t('app', '产品名称'),
            'category_id' => Yii::t('app','分类id'),
            'min_money' => Yii::t('app', '起投金额(元)'),
            'max_money' => Yii::t('app', '投资上限金额(元)'),
            'step_money' => Yii::t('app', '递增金额(元)'),
            'has_money' => Yii::t('app', '已投金额(元)'),
            'stock_money' => Yii::t('app', '项目总额(元)'),
            'year_rate' => Yii::t('app', '年化收益率(%)'),
            'can_rate_ticket' => Yii::t('app', '是否可用加息券'),
            'can_money_ticket' => Yii::t('app', '是否可用代金券'),
            'profit_type' => Yii::t('app', '收益方式'),
            'is_newer' => Yii::t('app', '是否新手'),
            'lock_day' => Yii::t('app', '锁定期(天)'),
            'invest_day' => Yii::t('app', '投资期限(天)'),
            'profit_day' => Yii::t('app', '起息日'),
            'status' => Yii::t('app', '状态'),
            'create_time' => Yii::t('app', '创建时间'),
            'start_time' => Yii::t('app', '开始时间'),
            'end_time' => Yii::t('app', '筹集到期时间'),
            'finish_time' => Yii::t('app', '筹集完成时间'),
            'is_index' => Yii::t('app','是否在首页显示'),
            'is_hidden' => Yii::t('app', '隐藏'),
            'member_id' => Yii::t('app', '借款人id(标的所属人)'),
            'credit_incomme' => Yii::t('app', '已发放平台收益(元)'),
            'platform_income_rate' => Yii::t('app', '平台收益率(%)'),
            'credit_time' => Yii::t('app', '放款时间'),
            'repayment_time' => Yii::t('app', '还款时间'),
            'option_status' => Yii::t('app', '放款状态'),
            'actual_credit_money' => Yii::t('app', '已放款金额'),
            'warranty_type' => Yii::t('app', '保证方式'),
            'address' => Yii::t('app', '借款人地址'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getproduct_detail()
    {
        return $this->hasOne(QfbProductDetail::className(), ['product_id' => 'id']);
    }

    public function getproduct_agreement(){
        return $this->hasMany(QfbAgreement::className(), ['id' => 'agreement_id'])
            ->viaTable('qfb_product_agreement', ['product_id' => 'id']);
    }

    public function getprofit_settings()
    {
        return $this->hasOne(QfbProfitSettings::className(), ['product_id' => 'id']);
    }

    public function getwarranty()
    {
        return $this->hasOne(QfbWarranty::className(), ['product_id' => 'id']);
    }

    public function getorder_repayment()
    {
        return $this->hasMany(QfbOrderRepayment::className(), ['product_id' => 'id']);
    }
}
