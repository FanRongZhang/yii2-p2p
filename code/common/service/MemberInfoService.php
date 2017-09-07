<?php
namespace common\service;
use Yii;
use common\models\QfbMemberInfo;


/**
 * 
 * @author jin
 *
 */
class MemberInfoService extends BaseService
{

    /**
    *
    *保存用户头像url
    *
    *@author jin
    *
    */
    public function saveAvatar($member_id, $url=''){
        $model= QfbMemberInfo::findOne(['member_id'=>$member_id]);
        
        $params['QfbMemberInfo']=array(
            'avatar' => $url  
        );

        if($model->load($params) && $model->save()){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     *
     *保存用户MemberInfo
     *
     *@author jin
     *
     */
    public static function saveMemberInfo($member_id, $params)
    {
        if (empty($params))
        {
            return false;
        }
        
        $model= QfbMemberInfo::findOne(['member_id'=>$member_id]);
        
        if($model->load($params) && $model->save())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     *
     *保存用户头像url
     *
     *@author jin
     *
     */
    public function getRealnameByMemberId($member_id){
        return QfbMemberInfo::find()->where(['member_id' => $member_id])->one();
    }
    /**
     *
     *获取用户详细信息
     *
     */
    public static function getMemberInfo($member_id){
        return QfbMemberInfo::findOne(['member_id'=>$member_id]);
    }
    
    /**
     *
     *获取用户详细信息
     *
     */
    public static function getMembersInfo($members){
        return QfbMemberInfo::find()->select(['member_id','realname','create_time'])->where(['in','member_id',$members])->all();
    }

    /**
     * 获取用户分页信息
     */
    public static function getInfoByMemberId($arr = array(),$page =1,$limit =10){
        $query = QfbMemberInfo::find();
        $query->select(['member_id','realname','create_time']);
        $query->where(['in','member_id',$arr]);
        $query->limit($limit);
        $query->offset(($page-1)*$limit);
        $query->orderBy('create_time DESC');
        $members = $query->asArray()->all();

        return $members;
    }

    /**
     * 查询用户是否通过易联认证
     * @param $member_id
     * @return null|static
     */
    public static function isVerify($member_id){
        $memberInfo = new QfbMemberInfo();
        $result = $memberInfo->find()->select('member_id,realname,is_verify')->where(['=','member_id',$member_id])->one();
        return $result;
    }
    /**
     * 查找身份证
     */
    public static function getByIdcard($member_id=0,$idcard=''){
        return QfbMemberInfo::find()->where(['card_no'=>$idcard])->andFilterWhere(['!=','member_id',$member_id])->andWhere(['=','is_verify',1])->one();
    }
    
    /**
     * 查找同一个身份证存在的个数
     */
    public static function getNumByIdcard($idcard){
        return QfbMemberInfo::find()->select('card_no')->where(['card_no'=>$idcard])->count();
    }

    /**
     * 用户修改昵称
     * @return bool
     */
    public function updateNickName($member_id,$nickname)
    {
        $memberInfo = QfbMemberInfo::findOne(['member_id'=>$member_id]);
        if(!$memberInfo)
        {
            return false;
        }
        $memberInfo->nickname = $nickname;

        if($memberInfo->save())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    
}