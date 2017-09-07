<?php 
namespace common\service;
use common\extension\middleware\EncryptService;
use common\models\QfbMember;
use common\models\QfbMemberInfo;
use common\models\QfbMemberMoney;
use common\models\QfbMemberVouchers;
use common\models\UMember;
use yii;
use common\service\ApiService;
use Exception;
use api\common\helpers\ReseponseCode as Code;

class MemberService extends BaseService{

    protected $_className = "common\models\QfbMember";

    




    public $errors =[]; //模型错误信息
    /**
     * [checkZfPwd 验证支付密码]
     * @param  int    $member_id [用户id]
     * @param  string $password  [支付密码]
     * @return [type]            [是否正确]
     * MD5(MD5(pwd) + "q!wse#r4t%yhu&i8o(p;")  钱富宝加密方式
     */
    public static function checkZfPwd($member_id,$password){
        //$zf_pwd = MD5(MD5(trim($password)).'q!wse#r4t%yhu&i8o(p;');
        $member = QfbMember::find()->where(['id'=>$member_id,'zf_pwd'=>$password])->one();
        return $member ? true : false;
    }
    /**根据推荐人手机号查找
     * return id
     * @author lwj
     */
    public function findUserByRmobile($r_mobile){
        return QfbMember::find()->select('id')->where(['=','mobile',$r_mobile])->one();
    }
    /**
     *设置model模型
     */
    public function getModel(){
        return $this->model = new QfbMember();
    }
    /**
     * 创建用户基本信息 order by lwj
     */
    public function createMember($data){
        $this->model=$this->getModel();
        $this->model->id=$data['id'];
        $this->model->load(["QfbMember"=>$data]);
        $this->model->validate();
        $this->errors = $this->model->errors;
        if(empty($this->errors)){
            $this->model->save();
            return $this->model;
        }else{
            return ['errors'=>$this->errors];
        }


    }
    /**
     * 创建用户money信息
     * @order by lwj
     * @$errors 错误信息
     * return $model
     */
    public function createMemberMoney($data){
        $model = new QfbMemberMoney();
        $model->load(['QfbMemberMoney'=>$data]);
        $model->validate();
        $this->errors = $model->errors;
        if(empty($this->errors)){
            $model->save();
            return $model;
        }else
            return ['errors'=>$model->errors];

    }
    /**
     * 创建用户详细信息
     * @order by lwj
     * @$errors 错误信息
     * return $model
     */
    public function createMemberInfo($data){
        $model = new QfbMemberInfo();
        $model->create_time = time();
        $model->load(['QfbMemberInfo'=>$data]) ;
        $model->validate();
        $this->errors = $model->errors;
        if(empty($this->errors)){
            $model->save();
            return $model;
        }else
            return ['errors'=>$model->errors];
    }

    /**
     *   @通过id查找用户信息
     *   @param id
     *   @param join array
     *   @return model
     */
    public static function findModelById($id,$join=null,$select=null){
        $query=QfbMember::find();

        $query->andWhere(['=', QfbMember::tableName().'.id', $id]);
        if(!empty($join)){
            foreach ($join as $key => $value) {
                $query->joinWith($value);
            }
        }
        if($select){
            $query->select($select);
        }

        return $query->one();
    }

    /**
     *
     * @param $member_id
     * @param null $models
     * @return mixed
     */
    public function setAccessToken($member_id,$models=null)
    {

        $model=$this->findModelById($member_id);
        if (!$model && $models !== null )
        {
            $params=[
                'id'=>$models['id'],
                'mobile'=>$models['mobile'],
                'access_token'=>ToolService::setAccessToken($member_id),
            ];
            return $this->createMember($params);
        }
        $model->access_token=ToolService::setAccessToken($member_id);
        $model->save();
        return $model;
    }


    /**
     * 判断用户是否更换设备
     * @param $member_id
     * @param $params
     * @return array
     */
    public function isChangeDevice($member_id,$params)
    {
        $imei = QfbMember::find()->where(['=','id',$member_id])->one();
        if ($imei->imei)
        {
            if ($imei->imei == $params['imei'])
            {
                //return ApiService::success(200,'');
                return false;
            }
            elseif ($imei->imei != $params['imei'])
            {
                if(isset($params['code'])) {
                    $imei->imei = $params['imei'];
                    $imei->save();
                }
                //return ApiService::success(202,'');
                return true;
            }
        }
        else
        {
            $imei->imei = $params['imei'];
            if ($imei->save())
            {
                return false;
                //return ApiService::success(201,'');
            }
        }
    }
    /**
     * 根据用户电话查询其等级
     */
    public function searchLevel($mobile)
    {
        $member = QfbMember::findOne(['mobile'=>$mobile]);
        if($member)
        {
            return $member->level;
        }
        else
        {
            return false;
        }

    }

    /**
     * 手机号查找用户
     * @param $post
     * @return bool
     */
    public function findMemberByMobile($mobile){
        $member = $this->getModel();
        $user = $member->findOne(['mobile'=>$mobile]);
        if (count($user) > 0 && $user->id != 1)
        {
            return $user;
        }
        else
        {
            return false;
        }
    }
    /**
     * 修改推荐人关系
     */
    public function updateRefer($member_id,$post=null)
    {
        $user = QfbMember::findOne($member_id);
        $r_mobile = $post['r_mobile'];

        $user_mobile = QfbMember::findOne(['mobile'=>$r_mobile]);
        if ($user_mobile == null)//商会无此手机号
        {
            return [
                'code' => 201,
                'message' => '无此推荐人',
            ];
        }
        //商会自己的推荐人不能被修改
        if ($user->id == 1)
        {
            return [
                'code' => 201,
                'message' => '此推荐人不能被修改',
            ];
        }
        if ($user->mobile == $r_mobile)
        {
            return [
                'code' => 201,
                'message' => '不能设置自己为推荐人',
            ];
        }
        if ($user->r_member_id == 1)
        {
            $r_relations = ','.$member_id.',';
            $array=array();
            $r_mobile_array = QfbMember::find()->select(['mobile'])->where(['like','relations',$r_relations])->asArray()->all();
            foreach ($r_mobile_array as $v)
            {
                $array[]=$v['mobile'];
            }
            if (in_array($r_mobile,$array))
            {
                return [
                    'code' => 201,
                    'message' => '该会员是您的下级会员，不能与您互相推荐',
                ];
            }
            if ($user_mobile->r_member_id == $member_id)
            {
                return [
                    'code' => 201,
                    'message' => '不能互推哦',
                ];
            }
            else
            {
                if ($user->save())
                {
                    return [
                        'code' => 200,
                        'message' => 'success',
                    ];
                }
                else
                {
                    return [
                        'code' => 201,
                        'message' => '修改失败',
                    ];
                }
            }
        }
        else//已有推荐人 不能修改
        {
            return [
                'code' => 201,
                'message' => '已设置推荐人',
            ];
        }
    }

    /**
     * 用户修改登录密码
     * @param $member_id
     * @param $post
     * @return bool
     */
    public function updateLoginPassword($member_id,$post){
//        \Yii::$app->set('db',yii::$app->components['dbuc']);
        $memberModel = UMember::find()->where("id=$member_id AND status !=0")->one();
        if ($memberModel)
        {
            if (!empty($post['password']) && strlen($post['password'])!=32)
            {
                $memberModel->password = EncryptService::twiceMd5($post['password']);
            }
            else
            {
                $memberModel->password = $post['password'];
            }

            if ($memberModel->save())
            {
//                \Yii::$app->set('db',yii::$app->components['dm_qfb']);
                return true;
            }
            else
            {
//                \Yii::$app->set('db',yii::$app->components['dm_qfb']);
                return false;
            }
        }
        else
        {
//            \Yii::$app->set('db',yii::$app->components['dm_qfb']);
            return [
                'code' => 203,
                'msg' => "用户账户被冻结",

            ];
        }
    }

    /**
     * 用户修改支付密码
     * @param $member_id
     * @param $post
     * @return bool
     */
    public function updateZfPassword($member_id,$post){
        $memberModel = QfbMember::find()->where(['=','id',$member_id])->one();
        if ($memberModel)
        {
            if (!empty($post['password']) && strlen($post['password'])!=32)
            {
                $memberModel->zf_pwd = EncryptService::twiceMd5($post['password']);
            }
            else
            {
                $memberModel->zf_pwd = $post['password'];
            }

            if ($memberModel->save())
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    /**
     * r_member_id推荐的人
     * int type;//0表示全部，1表示一度人脉，2表示2度人脉
     * @author jin
     */
    public static function getCountByRMemberId($r_member_id = 0, $type = -1){
        if ($type == -1){
            return false;
        }
        
        $result = array();
        $query = QfbMember::find();
        $query->select(['id']);
        $query->where(['r_member_id'=>$r_member_id]);
        $members = $query->asArray()->all();
        
        if ($type == 1){
            $result = $members;
        }else{
            $members2 = array();
            foreach ($members as $value){
                $members2 = array_merge($members2 , self::getCountByRMemberId($value['id'],1)) ;
            }
            if ($type == 2){
                $result = $members2;
            }elseif ($type == 0){
                $result = array_merge($members,$members2);
            }
        }
        
        return $result;
        
    }

    /**
     * 设置支付密码
     * @param $member_id
     * @param $post
     * @return bool
     */
    public function setZfPwd($member_id,$post)
    {
        if (strlen($post['zf_pwd']) != 32){
            $post['zf_pwd'] = EncryptService::twiceMd5($post['zf_pwd']);
        }
        $model = QfbMember::find()->where(['=','id',$member_id])->one();
        if ($model)
        {
            $model->zf_pwd = $post['zf_pwd'];
        }
        else
        {
            return false;
        }
        if ($model->save())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * token 寻找member_id
     * @param $token
     * @return array|null|yii\db\ActiveRecord
     */
    public function findMemberIdByToken($token)
    {
        return QfbMember::find()->where(['=','access_token',$token])->one();
    }

    /**
     * 根据用户级别查询所有用户
     * @param $level
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function findBylevel($level){
        $member = new QfbMember();
        $user = $member->find()->where(['=','level',$level])->all();
        if($user){
            return $user;
        }else{
            return false;
        }
    }
    /***/
    public function updateNew(){
        try{
            if($this->isNewer()){
                $this->model->is_newer=0;
                $this->model->save();
            }
            return true;
        }catch(Exception $e){
            $this->addMessage('is_newer',$e->getMessage());
            return false;
        }
    }
    /**
     * 判断是否新手
     * [isNewer description]
     * @return boolean [description]
     */
    public function isNewer(){

        if($this->model!== null){
            return $this->model['is_newer']?true:false;
        }
        $model = new $this->_className;
        $this->model=$model::find()->where(['=','id',$this->getMemberID()])->one();
        return $this->model['is_newer']?true:false;
    }

    /**
     * @param $member_id
     * @param int $level
     * @return bool
     * 修改用户等级
     * @author lwj
     */
    public function updateMemberLevel($member_id,$level=0){
        $result = QfbMember::find()->where(['=','id',$member_id])->one();
        $result->level = $level;
        return $result->save();
    }

    /**
     * 注册送红包
     * @order by steve
     * @$errors 错误信息
     * return $model
     */
    public function sendVouchers($data){
        $model = new QfbMemberVouchers();

        $model->load(['QfbMemberVouchers'=>$data]) ;
        $model->validate();
        $this->errors = $model->errors;
        if(empty($this->errors)){
            $model->save();
            return $model;
        }else
            return ['errors'=>$model->errors];
    }

    /**
     * API查询用户是否开通银行存管账户
     **/
    public static function getHkyhUser($member_id){

        if(empty($member_id)) ['code' => Code::COMMON_ERROR_CODE, 'msg' => '未登录', 'data' => ''];

        $hkyh = \Yii::$app->Hkyh;

        //调用接口名字，这里调用用户信息查询（直连接口），调用网关接口也一样
        $serviceName = 'QUERY_USER_INFORMATION';

        //平台用户编号   --测试
        $reqData = ['platformUserNo'=>$member_id];//$member.date("YmdHis", time())];/*1a13128829243*/

        $response = $hkyh->createPostParam($serviceName,$reqData);

        $data = json_decode($response['data'], true);

        // 用户不存在
        if($data['status'] != 'SUCCESS'){
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '用户不存在或查询有误', 'data' => $response];
        }

        return ['code' => Code::HTTP_OK, 'msg' => '请求成功', 'data' => $response];
    }

    /**
     * 根据member_id对表qfb_member、qfb_member_info、qfb_member_money进行三表联查
     * @param $member_id
     * @return array|false
     */
    public function getMemberInfo($member_id)
    {
        $sql = 'SELECT * FROM qfb_member AS a INNER JOIN qfb_member_info AS b ON a.id=b.member_id INNER JOIN qfb_member_money
         AS c ON a.id=c.member_id WHERE a.id='.$member_id;
        return $this->findBySql($sql, 1);
    }
}