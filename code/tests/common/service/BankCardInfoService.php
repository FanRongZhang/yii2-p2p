<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/5
 * Time: 15:24
 */
namespace common\service;
use common\models\QfbChannel;
use Yii;
use common\models\QfbBankCardInfo;
use common\models\QfbBankLimit;

class BankCardInfoService extends BaseService{

    /**
     * 返回用户卡信息
     * @param $card
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findModelByCard($card){
        return (QfbBankCardInfo::findBySql("SELECT * FROM ".QfbBankCardInfo::tableName()." WHERE  '{$card}' LIKE CONCAT(card_bin,'%')")->One());
    }

    /**
     * 返回 QfbBankCardInfo model
     * @return QfbBankCardInfo
     */
    public function getModel(){
        return $this->model=new QfbBankCardInfo();
    }

    /**
     * 获取通道对应银行的充值限额
     * @param  Int $bankCode 银行代号
     * @return ActiveRecorder 限额对象
     */
    public static function getLimitChannel($bankCode)
    {
        return QfbBankLimit::findBySql("SELECT `pt_type` FROM "
            .QfbBankLimit::tableName()
            ." WHERE  FIND_IN_SET('{$bankCode}', iss_users)")
            ->all();
    }

    /**
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getBindChannel($array)
    {
        return QfbChannel::find()->select('id')->where(['in','id',$array])->andWhere(['=','in_status',1])->andWhere(['=','is_default',1])->orderBy('sort')->all();
    }
}