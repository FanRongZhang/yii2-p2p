<?php

namespace common\models;

use Yii;
use common\models\QfbMemberInfo;

/**
 * This is the model class for table "{{%member}}".
 *
 * @property integer $member_id
 * @property integer $id
 * @property integer $level
 * @property string $relations
 * @property integer $recommend_id
 * @property integer $layer
 * @property string $mobile
 * @property string $account
 * @property string $access_token
 * @property integer $channel_id
 * @property integer $last_access_time
 * @property string $imei
 *
 * @property MemberInfo $memberInfo
 * @property MemberMoney $memberMoney
 */
class QfbMember extends \yii\db\ActiveRecord
{
    public $old_password;
    /**
     * 重输密码
     * @var unknown
     */
    public $password_repeat;
    /**
     * 推荐人的手机号
     * @var unknown
     */
    public $r_mobile;
    public $code;
    public $index;
    public $query;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%member}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id','last_access_time'],'required','on'=>'api_version'],
            [['level', 'r_member_id', 'layer', 'mobile', 'account'  ], 'required'],
            [['level', 'r_member_id', 'layer', 'channel_id', 'last_access_time','is_newer','member_type','is_dredge'], 'integer'],
            [['relations'], 'string', 'max' => 10000],
            [['mobile'], 'string', 'max' => 20],
            [['account'], 'string', 'max' => 50],
            [['access_token'], 'string', 'max' => 32],
            [['imei'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'=>'id',
            'member_id' => '用户ID',
            'level' => '用户等级',
            'relations' => 'Relations',
            'r_member_id' => 'Recommend ID',
            'layer' => 'Layer',
            'mobile' => '手机号',
            'account' => '会号账号',
            'access_token' => 'Access Token',
            'channel_id' => 'Channel ID',
            'last_access_time' => 'Last Access Time',
            'imei' => 'Imei',
            'is_dredge' => '是否开通银行账户',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberInfo()
    {
        return $this->hasOne(QfbMemberInfo::className(), ['member_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMemberMoney()
    {
        return $this->hasOne(QfbMemberMoney::className(), ['member_id' => 'id']);
    }
    public function checkPassword($attribute,$params){
        $model = Member::findOne($this->id);
        // if ($model->password != md5($this->old_password)){
        if(!(EncryptService::twiceMd5Verify($this->old_password, $model->password))){
            $this->addError($attribute,'原登录密码错误');
        }

    }

    /**
     *   验证之前的默认设置
     */
    public function beforeValidate(){
        if($this->isNewRecord==1){ //新建数据

            $default=$this->getDefaultValue();
            foreach ($default as $key=>$value) {
                $this->$key=$this->$key!==null?$this->$key:$value;
            }
            if($this->account =='')$this->account=$this->mobile;
        }
        return true;
    }
//    /**
//    *   验证过后
//    */
    /* public function afterValidate(){
         echo 2;
         print_r($this->errors);
     }
    */

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }


    /**
     *设置密码
     */
    static function setCrypt($model,$key){
        if(!empty($model->$key) && strlen($model->$key)!=32){
            // $model->$key=md5($model->$key);
            $model->$key= EncryptService::twiceMd5($model->$key);
        }
        return $model->$key;
    }



    /**
     * @查找所有上级代理
     * r_member_id 上级代理
     * return r_member_list   所有上级代理
     */
    public function findAllRmember($r_member_id){
        $defalutValue=$this->getDefaultValue();
        if($r_member_id!= $defalutValue['r_member_id'] || $r_member_id==0){
            $parent=$this->find()->andwhere(['=', 'id', $r_member_id])->one();
            $r_member_list=self::findAllRmember($parent->r_member_id);
            $r_member_list[]=$parent->id;
        }else{
            $r_member_list[]=$r_member_id;
        }

        return $r_member_list;
    }

    /**
     *   @ 获取真实的层级位置
     *
     */
    public function findMyLayer($relations){
        if($relations=='') return 1;
        $arr=explode(',',$relations);
        array_shift($arr); //去掉第一个逗号
        array_pop($arr); //去掉后的逗号
        return count($arr)+1;
    }


    /**
     *   @获取推荐人和推荐关系
     *   mobile 推荐人的手机号
     *
     */
    public function setRMember($mobile){
        $query=$this->find()->andWhere(['=', 'mobile', $mobile])->one();  //查找推荐人
        if(count($query)>0){
            if($this->isNewRecord==0 ){  //修改时用户表时的操作
                if($this->attributes['r_member_id']==$query->id && $this->relations==$query->relations.$query->id.','){  //修改时推荐关系未变
                    $relations=$this->relations;
                    if($this->layer==($query->layer+1)){
                        $layer=$this->layer;
                    }else{
                        $layer=self::findMyLayer($relations);
                    }
                    return [
                        'relations'=>$relations,
                        'r_member_id'=>$this->r_member_id,
                        'layer'=>$layer
                    ];
                }else{ //推荐关系有变动
                    $updateRMember=self::updateRMember($query);
                    $myDown=$this->find()->andFilterWhere(['like', 'relations', ",{$this->id},"])->orderBy('layer asc ,r_member_id asc')->all(); //我的所有下级
                    if(count($myDown)>0){ //如果有下级
                        $list=[];   //保存处理过的下级信息，留着下次查询
                        foreach ($myDown as $key => $value) {
                            //1.知道 自己的评级和此次处理人是否是直属推荐关系
                            if($this->layer +1 == $value->layer){
                                //1)是直属推荐关系
                                $data=[
                                    'relations'=>$updateRMember['relations'].$this->id.',',
                                    'layer'=>$updateRMember['layer']+1,
                                    'r_member_id'=>$value->r_member_id
                                ];
                                $value->relations=$data['relations'];
                                $value->layer=$data['layer'];
                                $value->password_repeat=$value->password;
                            }else{
                                //2)不是直属推荐关系
                                if(isset($list[$value->r_member_id]['relations']) && !empty($list[$value->r_member_id]['relations'])){
                                    $data=[
                                        'relations'=>$list[$value->r_member_id]['relations'].$value->r_member_id.',',
                                        'layer'=>$list[$value->r_member_id]['layer']+1,
                                        'r_member_id'=>$value->r_member_id
                                    ];
                                }else{
                                    $query=$this->find()->andWhere(['=', 'id', $value->r_member_id])->one();
                                    $data=self::updateRMember($query);
                                }
                                $value->relations=$data['relations'];
                                $value->layer=$data['layer'];
                                $value->password_repeat=$value->password;
                            }
                            $value->index=1;
                            if($value->update()==0)
                            {
                                $this->addError('r_mobile',self::attributeLabels()['r_member'].'下级会员关系出错');
                                return false;
                            }
                            $list[$value->id]=$data;
                        }
                    }
                    return $updateRMember;
                }
            }
            return self::updateRMember($query); //更新当前用户关系
        }else{
            return false;
        }
    }

    /**
     *   创建或修改推荐人关系
     */
    private function updateRMember($query){
        $defalutValue=$this->getDefaultValue();
        if($query->id==$defalutValue['r_member_id']){ //如果代理人是默认平台
            $relations=",{$query->id},";
            if($this->layer==($query->layer+1)){
                $layer=$this->layer;
            }else{
                $layer=self::findMyLayer($relations);
            }
            return [
                'relations'=>$relations,
                'r_member_id'=>$query->id,
                'layer'=>$layer
            ];
        }



        if($query->relations){  //如果有关系信息
            $arr=explode(',',$query->relations);
            array_shift($arr); //去掉第一个逗号
            array_pop($arr); //去掉后的逗号
            array_push($arr,$query->id);//插入上级
            $relations=','.implode(',',$arr).',';

            if($this->layer==($query->layer+1)){
                $layer=$this->layer;
            }else{
                $layer=self::findMyLayer($relations);
            }
            return [
                'relations'=>$relations,
                'r_member_id'=>$query->id,
                'layer'=>$layer
            ];
        }else{   //找不到关系的时候//查找上级

            return self::higtFindAllRmember($query->id);
        }
    }

    private function higtFindAllRmember($r_member_id){
        $r_member_list=$this->findAllRmember($r_member_id);
        $relations=','.implode(',',$r_member_list).',';
        if($this->layer==($query->layer+1)){
            $layer=$this->layer;
        }else{
            $layer=self::findMyLayer($relations);
        }
        return [
            'relations'=>$relations,
            'r_member_id'=>$r_member_id,
            'layer'=>$layer
        ];
    }

    /**
     */

    public function beforeSave($insert){
        //为了判断修改做次数标记
        $this->index=$this->index?$this->index:0;
        if($this->index){
            return true;
        }
        $params=[];
        $request=Yii::$app->request;
        $post=array_merge($request->post(),$request->get());

        // if($params){
        //     foreach ($params as $key => $value) {
        //         $this->$key=$value;
        //     }
        // }
        if (parent::beforeSave($insert)) {
            //$this->password=$this->setCrypt($this,'password');  //编辑密码
            $this->zf_pwd=$this->setCrypt($this,'zf_pwd');//编辑支付密码
            if($insert) {//新增
                //$this->operator = isset(yii::$app->user->identity->id)?yii::$app->user->identity->id:0;
               // $this->create_time = time();
                //如果没有来源，从post里拿来源
               /* if(!(isset($this->source) && $this->source)){
                    $this->source= isset($post['source'])?$post['source']:\yii::$app->params['source'];
                }*/

                $r_mobile_s='';
                if(isset($post['Member']['r_mobile'])  &&  $post['Member']['r_mobile']){
                    $r_mobile_s=$post['Member']['r_mobile'];
                }elseif(isset($post['r_mobile']) && $post['r_mobile']){
                    $r_mobile_s=$post['r_mobile'];
                }else{
                    $user =$this->findOne(['id'=>1]);
                    $r_mobile_s=$user->mobile;
                }

                if(isset($this->r_mobile) && $this->r_mobile){
                    $r_mobile_s = $this->r_mobile;
                }

                // file_put_contents("/home/wwwroot/dmall.dm188.cn/api/runtime/logs/aa.log",$this->scenario);
                if($r_mobile_s){
                    $params=$this->setRMember($r_mobile_s);
                }
                if($params){
                    foreach ($params as $key => $value) {
                        $this->$key=$value;
                    }
                }
            } else{
                $r_mobile_s='';
                if(isset($post['Member']['r_mobile'])  &&  $post['Member']['r_mobile']){
                    $r_mobile_s=$post['Member']['r_mobile'];
                }elseif(isset($post['r_mobile']) && $post['r_mobile']){
                    $r_mobile_s=$post['r_mobile'];
                }
                // file_put_contents("/home/wwwroot/dmall.dm188.cn/api/runtime/logs/aa.log",$this->scenario);
                if($r_mobile_s){
                    $params=$this->setRMember($r_mobile_s);
                }
                if($params){
                    foreach ($params as $key => $value) {
                        $this->$key=$value;
                    }
                }
            }

           // self::setExperience();//等级，成长设置修改

            return true;
        } else {
            return false;
        }
    }
    public function setExperience(){
        if(isset( $this->oldAttributes['experience']) && $this->experience > $this->oldAttributes['experience']){ //如果成长值有增加
            $memberModel = Member::findOne($this->id);
            $levelModel=Level::find()->andWhere(['=', 'id', $this->level])->one();
            if($levelModel->top_experience <= $this->experience){//如果成长值到了升级的时候，自动升级
                $nextLevelModel=Level::find()->andWhere(['=','type',0])
                    ->andWhere(['=','experience',$levelModel->top_experience])->one();//查找下一等级
                $this->level=$nextLevelModel->id;//等级提升
                if(isset($this->oldAttributes['level'])&&$this->attributes['level'] != $this->oldAttributes['level']){  //判断等级是否有变动
                    $newLevelModel=Level::find()->andWhere(['=', 'id', $this->level])->one(); //升级后的等级
                    $this->experience =  $this->experience?$this->experience:$this->oldAttributes['experience']; //长成值变化为（原来的值与增加的值的总和）
                }

                $r_memberModel=$this->find()->andWhere(['=','id',$this->r_member_id])->one(); //推荐人
                if(count($r_memberModel)){//如果存在推荐人
                    if(yii::$app->params['r_member_experience']){ //且有奖励处理
                        $r_member_experience=yii::$app->params['r_member_experience'];
                        $r_memberModel->experience=$r_memberModel->experience + $r_member_experience;
                        if($r_memberModel->save()==0){
                            print_r($r_memberModel->errors);
                            exit;
                        }
                    }
                }
            }else{//如果所增加的成长值达不到当前升级的要求，则成长值在当前累加
                $this->level = $this->oldAttributes['level'];
                $this->experience = $this->experience?$this->experience:$this->oldAttributes['experience'];


                $r_memberModel=$this->find()->andWhere(['=','id',$this->r_member_id])->one(); //推荐人
                if(count($r_memberModel)){//如果存在推荐人
                    if(yii::$app->params['r_member_experience']){ //且有奖励处理
                        $r_member_experience=yii::$app->params['r_member_experience'];
                        $r_memberModel->experience=$r_memberModel->experience + $r_member_experience;
                        if($r_memberModel->save()==0){
                            print_r($r_memberModel->errors);
                            exit;
                        }
                    }
                }
            }

        }


        return [
            'level'=>$this->level,
            'experience'=>$this->experience
        ];
    }
    /**
     *   返回模型的默认值
     */
    public function getDefaultValue(){
        return [
            'zf_pwd'=>'',
            'level'=>15,
            'r_member_id'=>1,
            //'password'=>'123456',
            'layer'=>1,
            //'active'=>'1',
            'experience'=>0,
        ];
    }
}
