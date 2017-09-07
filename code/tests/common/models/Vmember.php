<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vmember".
 *
 * @property integer $vid
 * @property integer $vlevel
 * @property integer $vr_member_id
 * @property string $vmobile
 * @property integer $vchannel_id
 * @property integer $vlast_access_time
 * @property string $vlast_ip
 * @property string $vaccount
 * @property integer $vstatus
 * @property integer $vsource
 */
class Vmember extends \yii\db\ActiveRecord
{

    public $vrmember_mobile;
    public $vrmember_realname;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vmember';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vid', 'vlevel', 'vr_member_id', 'vchannel_id', 'vlast_access_time', 'vstatus', 'vsource', 'vis_dredge', 'vis_dredge', 'vmember_type'], 'integer'],
            [['vmobile', 'vlast_ip'], 'string', 'max' => 20],
            [['vaccount'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'vid' => Yii::t('app', '用户id'),
            'vlevel' => Yii::t('app', '用户级别'),
            'vr_member_id' => Yii::t('app', '推荐人ID'),
            'vmobile' => Yii::t('app', '手机号'),
            'vrealname' => Yii::t('app', '姓名'),
            'vchannel_id' => Yii::t('app', '设备'),
            'vlast_access_time' => Yii::t('app', '最后访问时间'),
            'vlast_ip' => Yii::t('app', '最后登录ip'),
            'vaccount' => Yii::t('app', '帐号'),
            'vstatus' => Yii::t('app', '状态'),
            'vsource' => Yii::t('app', '来源'),
            'vcreate_time' => Yii::t('app', '注册时间'),
            'vlive_money' => Yii::t('app', '活期投资'),
            'vfix_money' => Yii::t('app', '定期投资'),
            'vrmember_mobile' => Yii::t('app', '推荐人手机号'),
            'vrmember_realname' => Yii::t('app', '推荐人姓名'),
            'vis_verify' => Yii::t('app', '是否认证'),
            'vis_dredge' => Yii::t('app', '是否开通'),
            'vmember_type' => Yii::t('app', '用户类型'),
        ];
    }
}
