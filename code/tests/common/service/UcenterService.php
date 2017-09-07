<?php
namespace common\service;
use common\models\UMember;
use common\extension\middleware\EncryptService;
use yii;
class UcenterService{
    const NO = 0;
    const YES = 1;
    public function __construct(){
        //yii::$app->set('db',yii::$app->components['dbuc']);
        UMember::getDb();
    }

    /**
     * 增
     */
    public function createUcMember($data){
        
        $UmemberModel = new UMember();
        $UmemberModel->account = $data['account'];
        $UmemberModel->mobile = $data['mobile'];
        $UmemberModel->password = $data['password'];
        $UmemberModel->source = isset($data['source']) ? $data['source'] : 1;
        $UmemberModel->create_time = time();
        if($UmemberModel->save()){
            return $UmemberModel;
        }else{
            $UmemberModel->validate();
            return $UmemberModel->errors;
        }

    }
    /**
     * 删
     */
    public function  deleteUcMember(){
    }
    /**
     * 改
     */
    public function updateUcMember(){

    }
    /**
     * 查
     */
    public function findUcMember(){

    }

    /**
     *设置model模型
     */
    public function getModel(){ 
        return $this->model = new UMember();
    }
    /**
     * 根据手机密码查找
     * @param 手机 $mobile
     * @param 密码 $password
     * @return Ambigous <\yii\db\static, NULL>
     */
    public static function findBymobile($mobile,$password)
    {
        if(!empty($password) && strlen($password)!=32){
            // $password=md5($password);
            $password = EncryptService::twiceMd5($password);
        }
        return UMember::findOne(['mobile' => $mobile, 'password' => $password]);
    }

    /**
     * 判断用户手机号码是否存在
     */
    public function isMobile($post=null)
    {
        $member = $this->getModel();
        $user = $member->findOne(['mobile'=>$post['mobile']]);
        if(count($user) > 0)
        {
            return $user;
        }else{
            return false;
        }
    }
    /**
     * 根据用户手机号返回用户id
     * @param $mobile
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findUserByMobile($mobile){
        return UMember::find()->select('id')->where(['=','mobile',$mobile])->one();
    }

    /**
     * 修改用户状态
     * @param $member_id
     * @return bool
     */
    public function updateMemberActive($member_id,$status=0){
        $result = UMember::find()->where(['=','id',$member_id])->one();
        $result->status = $status;
        return $result->save();
    }
}