<?php
namespace frontend\models;

use common\service\MemberService;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $mobile;
    public $password;
    public $member_type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['mobile', 'filter', 'filter' => 'trim'],
            ['mobile', 'required'],
            ['mobile', 'unique', 'targetClass' => '\frontend\models\User', 'message' => '该手机号码已被注册.'],
            ['mobile', 'string', 'min' => 2, 'max' => 11],

            ['member_type', 'required'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6, 'max' => 18],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $status = true;

        $trans = Yii::$app->db->beginTransaction();
        
        $user = new User();
        $user->account = $this->mobile;
        $user->mobile = $this->mobile;
        $user->setPassword($this->password);
        $user->source = 1;
        $user->create_time = time();

        if(!$user->save()){
            $status = false;
        }
        $memberService = new MemberService();
        /**验证推荐人是否存在*/

        $data = [
            'id' => $user->id,
            'account' => $this->mobile,
            'mobile'  => $this->mobile,
            'member_type' => $this->member_type
        ];
        /**创建用户基本信息*/
        $member = $memberService->createMember($data);

        if (!empty($member['errors'])) {
            $status = false;
        }

        /**创建用户钱包信息*/
        $data['member_id'] = $member->id;
        $memberMoney = $memberService->createMemberMoney($data);
        if (!empty($memberMoney['errors'])) {
            $status = false;
        }

        /**创建用户详细信息*/
        $data['member_id'] = $memberMoney->member_id;
        $memberInfo = $memberService->createMemberInfo($data);
        if(!empty($memberInfo['error'])){
            $status = false;
        }

        if($status == true){
            $trans->commit();
            return $user;
        }else{
            $trans->rollBack();
            return null;
        }
    }
}
