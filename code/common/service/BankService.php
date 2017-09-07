<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/3
 * Time: 16:11
 */
namespace common\service;

use api\common\helpers\ReseponseCode as Code;
use common\enum\ChannelEnum;
use common\models\QfbBank;
use common\models\QfbBankCardInfo;
use common\models\QfbBankExtend;
use common\models\QfbBankLimit;
use common\models\QfbBindingBank;
use common\models\QfbChannel;
use common\models\QfbErrorMsg;
use common\models\QfbMemberInfo;
use common\models\QfbOrder;
use common\models\QfbOrderFix;
use common\service\MemberMoneyService;
use yii;


// blake
use common\models\QfbMember;


class BankService extends BaseService
{

    /**
     * 实名认证成功后更改其银行卡状态
     */
    public static function updateByMemberId($member_id = 0)
    {
        $conn = \Yii::$app->db;
        $sql = 'update ' . QfbBankExtend::tableName() . ' set is_del=1,is_default=0 where bank_id in ( select id from ' . QfbBank::tableName() . ' where member_id=' . $member_id . ')';
        $command = $conn->createCommand($sql);
        return $command->execute();
    }

    /**
     * 银行卡支持列表
     * @param null $post
     * @return array|bool
     */
    public function findSupportBankList($post = null)
    {

        $bank_type = QfbBankLimit::find()->select('id,pt_type')->groupBy('pt_type')->all();
        foreach ($bank_type as $v) {
            $arr[] = $v->pt_type;
        }
        $default = QfbChannel::find()->select('id')->where(['=', 'in_status', 1])->andWhere(['=', 'out_status', 1])->andWhere(['=', 'is_default', 1])->orderBy('sort')->one();
        if (!$default) {
            return false; //设置支付通道
        }
        if (isset($post['type'])) {
            if (in_array($post['type'], $arr)) {
                $query = QfbBankLimit::find()->where(['=', 'is_support', 1])->andWhere(['=', 'pt_type', $post['type']]);
            } else {
                $query = QfbBankLimit::find()->where(['=', 'is_support', 1])->andWhere(['=', 'pt_type', $default->id]);
            }
        } else {
            $query = QfbBankLimit::find()->where(['=', 'is_support', 1])->andWhere(['=', 'pt_type', $default->id]);
        }

        $page = $post['page'] ? $post['page'] : 1;
        $pageSize = $post['limit'];
        $offset = ($page - 1) * $pageSize;
        $query = $query->offset($offset)->limit($post['limit'])->all();
        $result = array();
        if (count($query) > 0) {
            foreach ($query as $k => $v) {
                $result[$k] = [
                    'bank_code' => substr(trim($v->iss_users, ','), 0, 3),
                    'name' => $v->name,
                    'limit' => '单笔' . ($v->one_trade ? ($v->one_trade % 10000 == 0 ? ($v->one_trade / 10000) . '万' : (substr($v->one_trade, 0, strpos($v->one_trade, '.')))) : '不限') . ' 日限额' . ($v->day_trade ? ($v->day_trade % 10000 == 0 ? ($v->day_trade / 10000) . '万' : (substr($v->day_trade, 0, strpos($v->day_trade, '.')))) : '不限'),
                ];
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 用户已经绑定的银行卡列表
     * @param $member_id
     * @param $params
     * @return array
     */
    public function getList($member_id, $params)
    {
        if (!isset($params)) {
            $params['type'] = 1;
        }
        $is_lock = false;

        $query = QfbBank::find();
        $query->where(['=', 'member_id', $member_id])
            ->andWhere(['=', 'is_del', 0]);
        $query->orderBy('id desc');
        $model = $query->all();
        $result = array();
        if ($model->channel) {
            $limitModel = QfbBankLimit::findBySql("SELECT bank_name,trade_num,one_trade,day_trade,month_trade FROM " . QfbBankLimit::tableName() . " WHERE iss_users LIKE '%,{$model->bank_code},%' and pt_type = {$model->channel} and is_support=1")->one();
            $result[] = [
                'bank_code' => $model->bank_code,
                'card_type' => (int) $model->card_type,
                'create_time' => $model->create_time,
                'id' => (int) $model->id,
                'is_default' => (int) $model->is_default,
                'is_del' => (int) $model->is_del,
                'member_id' => (int) $model->member_id,
                'mobile' => $model->mobile,
                'name' => $model->name,
                'no' => $model->no,
                'org_code' => '',
                'username' => isset($model->username) ? $model->username : '',
                'valid_date' => isset($model->valid_date) ? $model->valid_date : '',
                'limit_in_each' => (int) $limitModel->one_trade ? $limitModel->one_trade : '',
                'limit_in_day' => (int) $limitModel->day_trade ? $limitModel->day_trade : '',
                'limit_out_each' => Yii::$app->params['limit_out_each'],
                'limit_out_day' => Yii::$app->params['limit_out_day'],
                'limit' => isset($limitModel->trade_num) ? (int) $limitModel->trade_num : 0,
                'unlock' => $is_lock,
            ];
        }
        return $result;
    }

    /**
     * 用户银行卡数量
     * @param $member_id
     * @return mixed
     */
    public function getCount($member_id)
    {
        $query = QfbBank::find();
        $query->andWhere(['=', 'member_id', $member_id])
            ->andWhere(['=', 'is_del', 0])
            ->groupBy('no');
        $query->orderBy('id desc');
        $model = $query->count();
        return $model;
    }
    public function checkParams($params, $member_id)
    {
        if (isset($params['id_card'])) {
            $idcards = MemberInfoService::getByIdcard($member_id, $params['id_card']);
            if ($idcards) {
                $this->message = '该证件已被绑定';
                return false;

            }
        }
        if (isset($params['no'])) {
            //查询用户在其它通道绑卡卡号（同一用户多通道只能绑定一张卡）
            $exist_no = $this->getBindNo($member_id);
            if ($exist_no && $params['no'] != $exist_no) {
                $this->message = '该卡号与其它通道绑定的卡号不一致';
                return false;
            }

            //查找用户在快钱绑卡情况
            $card = $this->getCard($member_id, ChannelEnum::ZHENGLIAN);
            if ($card) {
                if ($card->no == $params['no']) {
                    $this->message = "您的银行卡已被绑定，如有疑问请联系客服。";
                    return false;
                } else {
                    $this->message = "不能绑定多张卡";
                    return false;
                }
            }

            //判断卡是不是已被绑到快钱
            $bdo = $this->hasBeenBinded($params['no'], ChannelEnum::ZHENGLIAN);
            if ($bdo) {
                $this->message = "您的银行卡已被绑定，如有疑问请联系客服。";
                return false;
            }
        }
        return $params;
    }

    /**
     * 快钱创建绑卡订单
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  String $no 卡号
     * @param  Int $member_id 用户id
     * @param  String  $mobile 手机号
     * @param  String  $province 省份
     * @param  String  $city 城市
     * @return Boolean 结果
     */
    public function kqCreate($no, $member_id, $mobile, $province, $city)
    {

        //查询用户在其它通道绑卡卡号（同一用户多通道只能绑定一张卡）
        $exist_no = $this->getBindNo($member_id);
        if ($exist_no && $no != $exist_no) {
            $this->message = '该卡号与其它通道绑定的卡号不一致';
            return false;
        }

        //查找用户在快钱绑卡情况
        $card = $this->getCard($member_id, ChannelEnum::KUAIQIAN);
        if ($card) {
            if ($card->no == $no) {
                $this->message = "您的银行卡已被绑定，如有疑问请联系客服。";
                return false;
            } else {
                $this->message = "不能绑定多张卡";
                return false;
            }
        }

        //判断卡是不是已被绑到快钱
        $bdo = $this->hasBeenBinded($no, ChannelEnum::KUAIQIAN);
        if ($bdo) {
            $this->message = "您的银行卡已被绑定，如有疑问请联系客服。";
            return false;
        }

        //查询用户的身份信息
        $realInfo = $this->getRealInfo($member_id);

        //通过银行卡卡号查询卡信息
        $cardInfo = $this->getCardInfoByNo($no);

        if (!$cardInfo) {
            $this->message = "不支持此类银行卡";
            return false;
        } else {
            $bn = $cardInfo['bank_name'];
            //中国银行，招商银行必传省市
            if (($bn == '中国银行' || $bn == '招商银行') && !($province && $city)) {
                $this->message = "请填写开卡银行所在省市信息";
                return false;
            }
            //查看快钱通道是否支持次卡种
            $blt = $this->isSupport($bn, ChannelEnum::KUAIQIAN);
            if (!$blt) {
                $this->message = "不支持此类银行卡";
                return false;
            }
        }

        //获取银行缩写
        $bank_abbr = $this->getBankAbbr($cardInfo['bank_name']);

        //创建绑卡
        $res = $this->createCommonBindOrder($member_id, $no, $realInfo->realname,
            $cardInfo['bank_name'], $cardInfo['iss_users'],
            0, ChannelEnum::KUAIQIAN, $bank_abbr, $province, $city);

        if ($res) {
            //创建绑卡订单成功，调用快钱绑卡请求验证码
            $kq = Yii::$app->kuaiQian;
            $beforebind = $kq->getVerifyCodeBeforBind($member_id,
                $this->info['order']['sn'], $no, $realInfo['realname'],
                "0", $realInfo['card_no'], $mobile);
            if (isset($beforebind['indAuthContent'])
                && isset($beforebind['indAuthContent']['responseCode'])
            ) {
                if ($beforebind['indAuthContent']['responseCode'] == "00") {
                    //请求验证码成功，存token, storablePan，于订单表，以便后续继续绑卡
                    $order = $this->info['order'];
                    $order['token'] = $beforebind['indAuthContent']['token'];
                    $order['storable_pan'] = $beforebind['indAuthContent']['storablePan'];
                    $order['mobile'] = $mobile;
                    if ($order->save()) {
                        return true;
                    }
                } else {
                    $code = $beforebind['indAuthContent']['responseCode'];
                    $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                        ?: $beforebind['indAuthContent']['responseTextMessage'];
                    $this->message = $code . '<>' . $msg;
                    return false;
                }
            }
            if (isset($beforebind['ErrorMsgContent'])) {
                $code = $beforebind['ErrorMsgContent']['errorCode'];
                $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                    ?: $beforebind['ErrorMsgContent']['errorMessage'];
                $this->message = $code . '<>' . $msg;
                return false;
            }
        }
        //如果没有成功
        return false;
    }
    public function ZlCreateBinding($member_id, $params)
    {
        $memberInfoParams['QfbMemberInfo'] = array(
            'card_no' => $params['id_card'],
            'realname' => $params['name'],
            'is_verify' => 0,
        );
        $saveMemberInfo = MemberInfoService::saveMemberInfo($member_id, $memberInfoParams);
        //查看快钱通道是否支持次卡种

        if ($saveMemberInfo) {
            //通过银行卡卡号查询卡信息
            $cardInfo = $this->getCardInfoByNo($params['no']);
            if (!$cardInfo) {
                $this->message = "不支持此类银行卡";
                return false;
            }
            $blt = $this->isSupport($cardInfo['bank_name'], ChannelEnum::ZHENGLIAN);
            if (!$blt) {
                $this->message = "不支持此类银行卡";
                return false;
            }
            //获取银行缩写
            $bank_abbr = $this->getBankAbbr($cardInfo['bank_name']);
            //创建绑卡订单
            $res = $this->createCommonBindOrder(
                $member_id, $params['no'], $params['name'], $cardInfo['bank_name'], $cardInfo['iss_users'],
                0, ChannelEnum::ZHENGLIAN, $bank_abbr
            );
            if ($res) {
                $service = new CommonService();
                $result = $service->sendMobileVerifyCode($params['mobile'], CommonService::VERIFY_TYPE_BANK);
                //暂时不能发验证码
                if ($result) {
                    $order = $this->info['order'];
                    $order['token'] = \Yii::$app->session[$service::getType(CommonService::VERIFY_TYPE_BANK) . $params['mobile']];
                    $order['mobile'] = $params['mobile'];
                    if ($order->save()) {
                        return true;
                    } else {
                        $this->message = "绑定预留手机号失败";
                    }

                } else {
                    $this->message = "短信发送失败";
                }

            }
        } else {
            $this->message = "保存用户身份信息失败";
        }

        return false;
    }

    /**
     * 创建绑卡订单
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  Int $member_id 用户ID
     * @param  String $no 银行卡号
     * @param  String $realname 真实姓名
     * @param  String $bank_name 银行名称
     * @param  String $bank_code 银行代码
     * @param  String $money 绑卡时要充值的金额，
     * 为0则不需要充钱（快钱不要，到时京东绑卡可能用）
     * @param  Int $channel 绑卡的通道
     * @param  Int $bank_abbr 银行缩写
     * @param  String  $province 省份
     * @param  String  $city 城市
     * @return Boolean 结果
     */
    private function createCommonBindOrder($member_id, $no, $realname, $bank_name, $bank_code, $money, $channel, $bank_abbr = '', $province = '', $city = '',$id_card = '')
    {
        //失败时记录日志
        //LogService::log('yl_error.log', $member_id.'|'.$no.'|'.$realname.'|'.$bank_name.'|'.$channel.'|'.$id_card);

        //创建订单记录，和支付记录
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model = new QfbBindingBank;
            $model->sn = $this->getBindSn($member_id);
            $model->member_id = $member_id;
            $model->no = $no;
            $model->name = $bank_name;
            $model->username = $realname;
            $model->check_status = 0;
            $model->province = $province;
            $model->city = $city;
            $remark = '';

            if ($channel == ChannelEnum::KUAIQIAN) {
                $remark = "快钱绑卡";
            } elseif ($channel == ChannelEnum::YILIAN) {
                $remark = "易联绑卡";
            } elseif ($channel == ChannelEnum::HUARONG) {
                $remark = "华融绑卡";
            } elseif ($channel == ChannelEnum::HKYH) {
                $remark = "海口银行绑卡";
            }

            $model->channel_id = $channel;
            $model->remark = $remark;
            $model->bank_abbr = $bank_abbr;
            $model->id_card = $id_card;
            if ($model->save()) {

                $this->info['order'] = $model;
                $trans->commit();
                return true;
            } else {
                throw new \Exception('model');
            }
        } catch (\Exception $e) {
            $trans->rollback();

            switch ($e->getMessage()) {
                case 'model':
                    $this->message = '创建绑卡订单失败';
                    break;
                default:
                    $this->message = $e->getMessage();
                    break;
            }
            return false;
        }
    }

    /**
     * 查找当前用户当前支付平台绑卡
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  Int  $member_id 用户id
     * @param  Int  $channel 通道id
     * @return ActiveRecorder 结果对象
     */
    public function getCard($member_id, $channel)
    {
        return QfbBank::find()
            ->joinWith('bankExtend')
            ->where([
                'member_id' => $member_id,
                QfbBank::tableName() . '.is_del' => 0,
                QfbBankExtend::tableName() . '.channel_id' => $channel,
                QfbBankExtend::tableName() . '.is_del' => 0,
            ])->one();
    }

    /**
     * 查询卡有没有已被绑
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  String $no  卡号
     * @param  Int $channel 支付通道id
     * @return Boolean 结果
     */
    private function hasBeenBinded($no, $channel)
    {
        return QfbBank::find()
            ->joinWith('bankExtend')
            ->where([
                'no' => $no,
                QfbBank::tableName() . '.is_del' => 0,
                QfbBankExtend::tableName() . '.channel_id' => $channel,
                QfbBankExtend::tableName() . '.is_del' => 0,
            ])->one();
    }

    /**
     * 通过用户id获取用户真实信息
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  Int $member_id 用户id
     * @return ActiveRecorder 数据对象
     */
    private function getRealInfo($member_id)
    {
        $memberInfoService = new MemberInfoService();
        return $memberInfoService->getRealnameByMemberId($member_id);
    }

    /**
     * 通过银行卡卡号获取银行卡信息
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  String $no 银行卡卡号
     * @return ActiveRecorder 银行卡信息记录
     */
    public function getCardInfoByNo($no)
    {
        return (QfbBankCardInfo::findBySql("SELECT * FROM "
            . QfbBankCardInfo::tableName()
            . " WHERE  '{$no}' LIKE CONCAT(card_bin,'%')")
            ->asArray()->One());
    }

    /**
     * 查看通道是否支付该类银行的银行卡
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  String  $bn  银行名称
     * @param  Int  $channel 支付通道id
     * @return boolean 是否支持的标识
     */
    public function isSupport($bn, $channel)
    {
        $blt = QfbBankLimit::find()->where(['name' => $bn, 'pt_type' => $channel,
            'is_support' => 1])->one();
        return $blt ? true : false;
    }

    /**
     * 根据银行名字获取银行缩写
     * @param  String $name 银行名字
     * @return String 银行缩写(如CCB)
     */
    private function getBankAbbr($name)
    {
        $bkl = QfbBankLimit::find()->where(['like', 'name', trim($name)])->one();
        return $bkl ? $bkl->bank_abbr : "";
    }

    /**
     * 生成绑卡订单号
     * @return String 订单号
     */
    public function getBindSn()
    {
        //生成随机字母+数字
        $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";
        $len = strlen($str);
        for ($i = 0; $i < 6; $i++) {
            $code .= $str{rand(0, $len - 1)};
        }
        return 'BK' . date('YmdHis') . $code;
    }

    /**
     * 查找当前用户通道
     * @param  Int  $member_id 用户id
     * @param  Int  $bank_id   银行卡id
     * @param  Int  $channel_id 通道id
     * @return ActiveRecorder 结果对象
     */
    public static function getBank($member_id, $bank_id, $channel_id)
    {
        return QfbBank::find()
            ->joinWith('bankExtend')
            ->where([
                'member_id' => $member_id,
                QfbBank::tableName() . '.id' => $bank_id,
                QfbBank::tableName() . '.is_del' => 0,
                QfbBankExtend::tableName() . '.is_del' => 0,
                QfbBankExtend::tableName() . '.channel_id' => $channel_id,
            ])->one();
    }

    /**
     * 快钱再次获取验证码（分绑卡和充值两种情况）
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  String $sn 订单号
     * @return Boolean 结果
     */
    public function kqReGetCode($sn)
    {
        //查出要再次发送验证码的订单信息
        $result = $this->getOrderBySn($sn);
        $order = $result['order'];
        //查出订单的实名信息
        $realInfo = $this->getRealInfo($order->member_id);
        //调出快钱组件
        $kq = Yii::$app->kuaiQian;
        if ($result['type'] == 'bind') { //绑卡场景
            $beforebind = $kq->getVerifyCodeBeforBind($order->member_id,
                $order->sn, $order->no, $order->username,
                "0", $realInfo['card_no'], $order->mobile);
            if (isset($beforebind['indAuthContent']) && isset($beforebind['indAuthContent']['responseCode'])) {
                if ($beforebind['indAuthContent']['responseCode'] == "00") {
                    //请求验证码成功，修改token，以便后续继续绑卡
                    $order['token'] = $beforebind['indAuthContent']['token'];
                    if ($order->save()) {
                        return true;
                    } else {
                        $this->message = '保存token出错';
                        return false;
                    }
                } else {
                    $code = $beforebind['indAuthContent']['responseCode'];
                    $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                        ?: $beforebind['indAuthContent']['responseTextMessage'];
                    $this->message = $code . '<>' . $msg;
                    return false;
                }
            }
            if (isset($beforebind['ErrorMsgContent'])) {
                $code = $beforebind['ErrorMsgContent']['errorCode'];
                $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                    ?: $beforebind['ErrorMsgContent']['errorMessage'];
                $this->message = $code . '<>' . $msg;
                return false;
            }
        } else if ($result['type'] == 'charge') { //充值场景
            //查询到银行卡
            $bankExtend = getCardByBankIdAndChannel($order->bank_id, ChannelEnum::KUAIQIAN);
            if ($bankExtend) {
                $beforepay = $kq->getVerifyCodeBeforPay($order->member_id,
                    $order->sn, $bankExtend->storable_pan,
                    $bankExtend->bank->bank_abbr, $order->price);
                if (isset($beforepay['GetDynNumContent']) && isset($beforepay['GetDynNumContent']['responseCode'])) {
                    if ($beforepay['GetDynNumContent']['responseCode'] == "00") {
                        //请求验证码成功，修改token，以便后续继续绑卡
                        $bankExtend['token'] = $beforepay['GetDynNumContent']['token'];
                        if ($bankExtend->save()) {
                            return true;
                        } else {
                            $this->message = '保存token出错';
                            return false;
                        }
                    } else {
                        $code = $beforepay['GetDynNumContent']['responseCode'];
                        $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                            ?: $beforepay['GetDynNumContent']['responseTextMessage'];
                        $this->message = $code . '<>' . $msg;
                        return false;
                    }
                }
                if (isset($beforebind['ErrorMsgContent'])) {
                    $code = $beforebind['ErrorMsgContent']['errorCode'];
                    $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                        ?: $beforebind['ErrorMsgContent']['errorMessage'];
                    $this->message = $code . '<>' . $msg;
                    return false;
                }
            } else {
                $this->message = "无效卡";
                return false;
            }
        }
    }

    /**
     * 通过订单id获取相应的订单
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param Int $order_id 订单id
     * @return 包含类型和数据的数组 array['order'=>'...','type'=>'...']
     */
    public function getOrderBySn($sn)
    {
        //带有BK前辍的订单是绑卡订单
        if (!strncmp($sn, 'BK', 2)) {
            $order = QfbBindingBank::find()->where(['sn' => $sn])->one();
            $res['order'] = $order;
            $res['type'] = "bind";
        } else if (!strncmp($sn, 'CZ', 2)) {
            $order = QfbOrder::find()->where(['sn' => $sn])->one();
            $res['order'] = $order;
            $res['type'] = "charge";
        } elseif (!strncmp($sn, 'DQ', 2)) {
            $order = QfbOrderFix::find()->where(['sn' => $sn])->one();
            $res['order'] = $order;
            $res['type'] = "fixorder";

        } elseif (!strncmp($sn, 'HQ', 2)) {
            $order = QfbOrder::find()->where(['sn' => $sn])->one();
            $res['order'] = $order;
            $res['type'] = "liveorder";

        }

        return $res;
    }


    /**
     * 易联绑卡提交
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param String $sn 绑卡订单sn
     * @param  String $verify_code 验证码
     * @return Boolean 结果
     */
    public function YlCommit($sn, $verify_code){
        $result = $this->getOrderBySn($sn);
        $order = $result['order'];
        //调出快钱组件
        $ylPay = Yii::$app->YiLian;
        if ($result['type'] == 'bind') { //绑卡提交场景
            //创建绑卡订单成功，调用易联绑卡（同时实名接口）
            $data   = [
                'SN'=>'SN'.date('YmdHis'),//流水号6-14位
                'BANK_CODE'=>'',
                'ACC_NO'=> $order->no,//帐号
                'ACC_NAME'=>$order->username,//开户姓名
                'AMOUNT'=>'1.08',
                'ID_NO'=>$order->id_card, //身份证
                'MOBILE_NO'=>$order->mobile,
                'CNY'=>'CNY',
                'RETURN_URL'=>'',//异步回调地址
                'PAY_STATE'=>'',//状态码
                'MER_ORDER_NO'=>$order->sn,
                'TRANS_DESC'=>'快捷支付认证扣款，稍后将返还到原卡',//订单描述,外呼语音播报内容,由商户自定义
                'SMS_CODE'  => $verify_code//短信验证码
            ];
            $bind_res = $ylPay->verify($data);
            //echo $data['SN'];
            //Yii::getLogger()->log($data['SN'], 3, $category = 'application');
            if ($bind_res['TRANS_STATE']=='0000' && $bind_res['PAY_STATE']=='0000') {
                //存实名认证信息
                $member_info = QfbMemberInfo::findOne(['member_id' => $order->member_id]);
                $member_info->realname = $order->username;
                $member_info->is_verify = 1;
                $member_info->card_no = $order->id_card;
                if ($member_info->save()) {
                    //存绑卡通道信息
                    $sbk = $this->saveCard($order, ChannelEnum::YILIAN);
                    if ($sbk) {
                        $this->message = "银行卡绑定成功!";
                        return true;
                    } else {
                        $this->message = "保存银行卡数据出错";
                        return false;
                    }
                } else {
                    $this->message = "保存实名信息出错";
                    return false;
                }
            } else {
                $code = $bind_res['PAY_STATE'];
                $msg = $this->getErrorMsg($code, ChannelEnum::YILIAN)
                    ?: $bind_res['REMARK'];
                $this->message = $code . "<>" . $msg;
                return false;
            }
        }elseif ($result['type'] == 'liveorder' || $result['type'] == 'fixorder'){//活期定期购买
            //根据订单类型获取产品名
            if ($result['type'] == 'liveorder') {
                $product_name = $order->remark;
                $money = $order->money;
            } else if ($result['type'] == 'fixorder') {
                $product_name =  $order->product->product_name;
                $money = $order->pay_money;
            }

            //查出实名信息
            $member_info = MemberInfoService::getMemberInfo($order->member_id);

            //查出银行卡信息
            $bank = QfbBank::findOne($order->bank_id);

            //调取易联支付
            $res = $ylPay->gather(
                [
                    'ACC_NO'=>$bank->no,//卡号
                    'ACC_NAME'=>$bank->username,//姓名
                    'ID_NO'=>$member_info->card_no,//身份证
                    'MOBILE_NO'=>$bank->mobile,
                    'AMOUNT'=>$money,
                    'CNY'=>'CNY',
                    'PAY_STATE'=>'',
                    'RETURN_URL'=>$ylPay->RETURN_URL,
                    'MER_ORDER_NO'=>$order->sn,
                    'TRANS_DESC'=>$product_name,//代收订单描述内容
                    'SMS_CODE'=>$verify_code
                ]
            );

            if ($res['TRANS_STATE']=='0000' && $res['PAY_STATE']=='00A4') {
                $this->message = "购买成功<>".$order->money;
                return true;
            } else {
                $code = $res['PAY_STATE'];
                $msg = $this->getErrorMsg($code, ChannelEnum::YILIAN)
                    ?: $res['REMARK'];
                $this->message = $code . "<>" . $msg;
                return false;
            }
        }
    }

    /**
     * 快钱绑卡提交
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param String $sn 绑卡订单sn
     * @param  String $verify_code 验证码
     * @return Boolean 结果
     */
    public function kqCommit($sn, $verify_code)
    {
        $result = $this->getOrderBySn($sn);
        $order = $result['order'];
        //调出快钱组件
        $kq = Yii::$app->kuaiQian;
        if ($result['type'] == 'bind') { //绑上提交场景
            $bind = $kq->bind($order->member_id, $order->sn,
                $order->no, $order->mobile,
                $verify_code, $order->token);
            if (isset($bind['indAuthDynVerifyContent']) && isset($bind['indAuthDynVerifyContent']['responseCode'])) {
                if ($bind['indAuthDynVerifyContent']['responseCode'] == "00") {
                    $sbk = $this->saveCard($order, ChannelEnum::KUAIQIAN);
                    if ($sbk) {
                        $this->message = "银行卡绑定成功!";
                        return true;
                    } else {
                        $this->message = "保存银行卡数据出错";
                        return false;
                    }
                } else {
                    $code = $bind['indAuthDynVerifyContent']['responseCode'];
                    $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                        ?: $bind['indAuthDynVerifyContent']['responseTextMessage'];
                    $this->message = $code . "<>" . $msg;
                    return false;
                }
            }

            if (isset($bind['ErrorMsgContent'])) {
                $code = $bind['ErrorMsgContent']['errorCode'];
                $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                    ?: $bind['ErrorMsgContent']['errorMessage'];
                $this->message = $code . '<>' . $msg;
                return false;
            }
        } elseif ($result['type'] == 'charge') {
            //查出银行卡信息
            $bankExtend = $this->getCardByBankIdAndChannel($order->bank_id, ChannelEnum::KUAIQIAN);
            $pay = $kq->pay("TR1", "PUR", date("YmdHis"),
                $bankExtend->storable_pan, $order->price, $order->sn,
                $order->member_id, "QuickPay", $bankExtend->bank->mobile,
                $verify_code, "0", $bankExtend->token, "2");

            if (isset($pay['TxnMsgContent']) && isset($pay['TxnMsgContent']['responseCode'])) {
                if ($pay['TxnMsgContent']['responseCode'] == "00") {
                    //串金额
                    $money = $this->getMemberMoney($order->member_id) + $order->price;
                    $this->message = "充值成功!<>" . $money;
                    return true;
                } else {
                    $code = $pay['TxnMsgContent']['responseCode'];
                    $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                        ?: $pay['TxnMsgContent']['responseTextMessage'];
                    $this->message = $code . "<>" . $msg;
                    return false;
                }
            }

            if (isset($pay['ErrorMsgContent'])) {
                $code = $pay['ErrorMsgContent']['errorCode'];
                $msg = $this->getErrorMsg($code, ChannelEnum::KUAIQIAN)
                    ?: $pay['ErrorMsgContent']['errorMessage'];
                $this->message = $code . '<>' . $msg;
                return false;
            }
        }
    }

    /**
     * 华融支付提交
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param String $sn 充值订单sn
     * @param  String $verify_code 验证码
     * @return Boolean 结果
     */
    public function hrCommit($sn, $verify_code)
    {
        $result = $this->getOrderBySn($sn);
        $order = $result['order'];
        //调出华融组件
        $hr = Yii::$app->hrpay;

        //根据订单类型获取产品名
        if ($result['type'] == 'liveorder') {
            $product_name = $order->remark;
            $money = $order->money;
        } else if ($result['type'] == 'fixorder') {
            $product_name =  $order->product->product_name;
            $money = $order->pay_money;
        } else {
            $product_name = $money = '';
        }

        //查出实名信息
        $member_info = MemberInfoService::getMemberInfo($order->member_id);

        //查出银行卡信息
        $bank = QfbBank::findOne($order->bank_id);

        //调取华融支付
        $res = $hr->pay(
            $order->hr_sn,
            $verify_code,
            $money,
            $product_name,
            time(),
            $bank->no,
            $bank->username,
            $member_info->card_no,
            $bank->mobile
        );

        //本来可以直接返回结果的，但是为了兼容以前的
        //鸡肋处理一下
        if ($res['code'] == Code::HTTP_OK) {
            $this->message = "购买成功<>".$order->money;
            return true;
        } else {
            //重新生成支付订单号以便再次提交
            $order->hr_sn = ToolService::SetSn('HRC');
            $order->save();
            $this->message = $res['msg'];
            return false;
        }
    }

    public function ZlCommit($sn, $code){
        $result = $this->getOrderBySn($sn);
        $order = $result['order'];
        if ($result['type'] == 'bind') {
            if ($order->token != $code) {
                $this->message = "您输入的验证码错误";
                return false;
            }
            $memInfo = MemberInfoService::getMemberInfo($order->member_id);
            if($memInfo->is_verify == 0) {
                $data = [
                    'cardNo' => $order->no,
                    'customerNm' => $order->username,
                    'certifId' => $memInfo->card_no,
                    'phoneNo' => $order->mobile
                ];
                $trans = Yii::$app->db->beginTransaction();
                try {
                    $result = \Yii::$app->ZlPay->certify($data);
                    if ($result->respCode == "00") {
                        $res = \Yii::$app->ZlPay->addWriteListing($data);
                        if($res->respCode == "00" || $res->respCode == "90") {
                            $memInfo->is_verify = 1;
                            $memInfo->bindId = intval($result->bindId);
                            if ($memInfo->save()) {
                                $sbk = $this->saveCard($order, ChannelEnum::ZHENGLIAN);
                                if ($sbk) {
                                    $this->message = "绑卡成功";
                                    $trans->commit();
                                    return true;
                                } else
                                    $this->message = "绑卡失败";
                            } else
                                $this->message = "绑定用户身份信息失败";
                            return false;

                        }else
                            $this->message = $result->respCode . "<>" . $result->respMsg;
                    } else
                        $this->message = $result->respCode . "<>" . $result->respMsg;
                } catch (\Exception $e) {
                    $this->message = $e->getMessage();
                    $trans->rollBack();
                }
            }else
                $this->message = "重复实名认证";
            return false;
        }elseif($result['type'] == 'fixorder'){
            $payData['bank_sn'] = $order->bank_sn;
            $payData['pay_money'] = strval(100*$order->pay_money);
            $memInfoModel = QfbMemberInfo::findOne(['member_id'=>$order->member_id]);
            $payData['bindId'] = strval($memInfoModel->bindId);
            $payData['code'] = $code;
            $payRes = \Yii::$app->ZlPay->payOrder($payData);
            if($payRes->respCode =="00"){
                $this->message = "支付成功!<>".$order->pay_money;
                return true;
            }else
                $this->message = $payRes->respCode . "<>" . $payRes->respMsg;

        }elseif($result['type'] == 'liveorder'){
            $payData['bank_sn'] = $order->bank_sn;
            $payData['pay_money'] = strval(100*$order->price);
            $memInfoModel = QfbMemberInfo::findOne(['member_id'=>$order->member_id]);
            $payData['bindId'] = strval($memInfoModel->bindId);
            $payData['code'] = $code;
            $payRes = \Yii::$app->ZlPay->payOrder($payData);
            if($payRes->respCode =="00"){
                $this->message = "支付成功!<>".$order->price;
                return true;
            }else
                $this->message = $payRes->respCode . "<>" . $payRes->respMsg;
        }
        return false;
    }

    /**
     * 从绑卡订单表保存到银行卡列表
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  ActiveRecorder $order 绑卡订单表
     * @param  Int $channel 通道类型
     * @return Boolean 结果
     */
    private function saveCard($order, $channel)
    {
        //先查询银行卡列表
        $bank = QfbBank::find()->where(['no' => $order->no
            , 'member_id' => $order->member_id])->one();

        if ($bank) {
            $bank->is_del = 0;
            if ($bank->save()) {
                $res = $this->saveCardExt($bank, $order, $channel);
                if ($res) {
                    return true;
                } else {
                    $this->message = '保存通道绑卡信息时出错';
                    return false;
                }
            } else {
                $this->message = '保存银行卡数据时出错';
                return false;
            }
        } else {
            $bank = new QfbBank();
            $bank->member_id = $order->member_id;
            $bank->name = $order->name;
            $bank->username = $order->username;
            $bank->no = $order->no;
            $bank->mobile = $order->mobile;
            $bank->create_time = time();
            $bank->is_del = 0;
            $bank->bank_abbr = $order->bank_abbr;
            $bank->province = $order->province;
            $bank->city = $order->city;
            if ($bank->save()) {
                $res = $this->saveCardExt($bank, $order, $channel);
                if ($res) {
                    return true;
                } else {
                    $this->message = '保存通道绑卡信息时出错';
                    return false;
                }
            } else {
                $this->message = '保存银行卡数据时出错';
                return false;
            }
        }
    }

    /**
     * 保存通道银行卡绑定信息
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param ActiveRecoder $bank 银行卡记录
     * @param ActiveRecoder $order 绑卡订单记录
     * @param Int $channel 支付通道id
     */
    public function saveCardExt($bank, $order, $channel)
    {
        //查询通道绑卡信息
        $bkExt = QfbBankExtend::find()->where(['bank_id' => $bank->id,
            'channel_id' => $channel])->one();
        if ($bkExt) {
            $bkExt->is_del = 0;
            return $bkExt->save() ? true : false;
        } else {
            $bkExt = new QfbBankExtend;
            $bkExt->bank_id = $bank->id;
            $bkExt->channel_id = $channel;
            $bkExt->is_del = 0;
            $bkExt->create_time = time();
            return $bkExt->save() ? true : false;
        }
    }

    /**
     * 获取绑卡错误信息
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param  String $code 错误码
     * @param Int $channel 支付通道id
     * @return String $msg 错误详细信息
     */
    public function getErrorMsg($code, $channel)
    {
        $em = QfbErrorMsg::find()->where(['code' => $code, 'channel_id' => $channel])->one();
        return $em ? $em->msg : false;
    }

    /**
     * 查询用户已绑定的卡号
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param Int $member_id
     * @return String $no 已绑卡号
     */
    public function getBindNo($member_id)
    {
        $be = QfbBank::find()
            ->joinWith('bankExtend')
            ->where([
                'member_id' => $member_id,
                QfbBank::tableName() . '.is_del' => 0,
                QfbBankExtend::tableName() . '.is_del' => 0,
            ])->one();
        return $be ? $be->no : '';
    }

    /**
     * 根据银行卡id和支付通道找卡
     * @author xiaomalover <xiaomalover@gmail.com>
     * @param $bank_id 银行卡id
     * @param $channel支付通道
     */
    public function getCardByBankIdAndChannel($bank_id, $channel)
    {
        return QfbBankExtend::find()
            ->joinWith('bank')
            ->where([
                QfbBankExtend::tableName() . '.bank_id' => $bank_id,
                QfbBankExtend::tableName() . '.channel_id' => $channel,
                QfbBank::tableName() . '.is_del' => 0,
                QfbBankExtend::tableName() . '.is_del' => 0,
            ])->one();
    }

    /*
     *解绑银行卡
     */
    public function delete($mobile, $no)
    {
        $model = QfbBank::find()->where(['mobile' => $mobile, 'no' => $no])->one();
        if (count($model) == 0) {
            return false;
        }

        $model->is_del = 1;

        if ($model->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 查用户的可用余额
     * @param Int $member_id 用户id
     * @return Deciaml 用户余额
     */
    public function getMemberMoney($member_id)
    {
        $money = MemberMoneyService::getByMemberMoney($member_id);
        return $money ? $money->money : 0;
    }

    /**
     * 华融支付绑上
     * 同时实名认证
     * @param string $no 银行卡号
     * @param string $mobile 预留手机号
     * @param string $name 开户者姓名
     * @param string $id_card 身份证号
     * @param string $card_name 银行名称
     */
    public function hrBind(
        $member_id,
        $no,
        $mobile,
        $name,
        $id_card,
        $card_name
    ) {
        //通过银行卡卡号查询卡信息
        $cardInfo = $this->getCardInfoByNo($no);

        if (!$cardInfo) {
            $this->message = "不支持此类银行卡";
            return false;
        } else {
            $bn = $cardInfo['bank_name'];
            //查看通道是否支持次卡种
            $blt = $this->isSupport($bn, ChannelEnum::HUARONG);
            if (!$blt) {
                $this->message = "不支持此类银行卡";
                return false;
            }
        }

        //查询用户在其它通道绑卡卡号（同一用户多通道只能绑定一张卡）
        $exist_no = $this->getBindNo($member_id);
        if ($exist_no && $no != $exist_no) {
            $this->message = '该卡号与其它通道绑定的卡号不一致';
            return false;
        }

        //查找该用户在华融绑卡情况
        $card = $this->getCard($member_id, ChannelEnum::HUARONG);
        if ($card) {
            if ($card->no == $no) {
                $this->message = "您的银行卡已被绑定，如有疑问请联系客服。";
                return false;
            } else {
                $this->message = "不能绑定多张卡";
                return false;
            }
        }

        //判断该卡是不是已被绑到快钱(一个用户可能有多个帐号，在一个帐号绑了，另一个就不能再绑了)
        $bdo = $this->hasBeenBinded($no, ChannelEnum::HUARONG);
        if ($bdo) {
            $this->message = "您的银行卡已被绑定，如有疑问请联系客服。";
            return false;
        }

        //先查询有没有绑卡订单，有的话，修改华融订单流水号就好
        //防止产生多个垃圾数据

        $bind_order = QfbBindingBank::find()->where([
            'member_id' => $member_id,
            'no' => $no,
            'channel_id' => ChannelEnum::HUARONG,
        ])->one();

        if ($bind_order) {
            //由于华融支付每次绑卡都需要不同的流水号
            $bind_order->sn = $this->getBindSn();
            $bind_order->save();
            $this->info['order'] = $bind_order;
        } else {
            //创建绑卡订单
            $res = $this->createCommonBindOrder($member_id, $no, $name,
                $card_name, '', '', ChannelEnum::HUARONG);
            if (!$res) {
                $this->message = "创建绑卡订单失败。";
                return false;
            }
        }

        $order = $this->info['order'];
        //存必须要的信息
        $order->mobile = $mobile;
        $order->remark = '华融绑卡';
        $order->save();
        //创建绑卡订单成功，调用华融绑卡（同时实名接口）
        $hr = Yii::$app->hrpay;
        $bind_res = $hr->auth(
            $order->sn,
            $mobile,
            $card_name,
            $no,
            $name,
            $id_card
        );
        if ($bind_res['code'] == 200) {
            //存实名认证信息
            $member_info = QfbMemberInfo::findOne(['member_id' => $member_id]);
            $member_info->realname = $name;
            $member_info->is_verify = 1;
            $member_info->card_no = $id_card;
            if ($member_info->save()) {
                //存绑卡通道信息
                $sbk = $this->saveCard($order, ChannelEnum::HUARONG);
                if ($sbk) {
                    $this->message = "银行卡绑定成功!";
                    return true;
                } else {
                    $this->message = "保存银行卡数据出错";
                    return false;
                }
            } else {
                $this->message = "保存实名信息出错";
                return false;
            }
        } else {
            $this->message = $bind_res['msg'];
            return false;
        }
    }

    /**
     * 易联支付绑卡
     * 同时实名认证
     * @param string $no 银行卡号
     * @param string $mobile 预留手机号
     * @param string $name 开户者姓名
     * @param string $id_card 身份证号
     * @param string $card_name 银行名称
     */
    public function ylBind(
        $member_id,
        $no,
        $mobile,
        $name,
        $id_card,
        $card_name
    ) {
        //通过银行卡卡号查询卡信息
        $cardInfo = $this->getCardInfoByNo($no);
        if (!$cardInfo) {
            $this->message = "不支持此类银行卡";
            return false;
        } else {
            $bn = $cardInfo['bank_name'];
            //查看通道是否支持次卡种
            $blt = $this->isSupport($bn, ChannelEnum::YILIAN);
            if (!$blt) {
                $this->message = "不支持此类银行卡";
                return false;
            }
        }

        //查询用户在其它通道绑卡卡号（同一用户多通道只能绑定一张卡）
        $exist_no = $this->getBindNo($member_id);
        if ($exist_no && $no != $exist_no) {
            $this->message = '该卡号与其它通道绑定的卡号不一致';
            return false;
        }

        //查找该用户在易联绑卡情况
        $card = $this->getCard($member_id, ChannelEnum::YILIAN);
        if ($card) {
            if ($card->no == $no) {
                $this->message = "您的银行卡已被绑定，如有疑问请联系客服。";
                return false;
            } else {
                $this->message = "不能绑定多张卡";
                return false;
            }
        }

        //判断该卡是不是已被绑到易联(一个用户可能有多个帐号，在一个帐号绑了，另一个就不能再绑了)
        $bdo = $this->hasBeenBinded($no, ChannelEnum::YILIAN);
        if ($bdo) {
            $this->message = "您的银行卡已被绑定，如有疑问请联系客服。";
            return false;
        }

        //先查询有没有绑卡订单，有的话，修改华融订单流水号就好
        //防止产生多个垃圾数据
        $bind_order = QfbBindingBank::find()->where([
            'member_id' => $member_id,
            'no' => $no,
            'channel_id' => ChannelEnum::YILIAN,
        ])->one();

        if ($bind_order) {
            //由于华融支付每次绑卡都需要不同的流水号
            $bind_order->sn = $this->getBindSn();
            $bind_order->save();
            $this->info['order'] = $bind_order;
        } else {
            //创建绑卡订单
            $res = $this->createCommonBindOrder($member_id, $no, $name,
                $card_name, '', '', ChannelEnum::YILIAN,'','','',$id_card);
            if (!$res) {
                $this->message = "创建绑卡订单失败。";
                return false;
            }
        }

        $order = $this->info['order'];
        //存必须要的信息
        $order->mobile = $mobile;
        $order->remark = '易联绑卡';
        $order->save();

        $ylPay = Yii::$app->YiLian;
        //易联绑卡必须先发送短信
        $res_code = $ylPay->send_message(
            [
                'ACC_NO'=>$no,//帐号
                'ACC_NAME'=>$name,//开户姓名
                'ID_NO'=>$id_card,
                'MOBILE_NO'=>$mobile,
                //'AMOUNT'=>'',
                //'CNY'=>'CNY',
                'PAY_STATE'=>'',
                'MER_ORDER_NO'=>$order->sn,
                'TRANS_DESC'=>'钱富宝易联绑卡认证'
            ]
        );

        if($res_code['TRANS_STATE']=='0000' && $res_code['PAY_STATE']=='0000'){//验证码成功
            $this->message = "绑卡短信发送成功!";
            return true;
        }else{
            $this->message = $res_code['REMARK'];
            return false;
        }

    }

    /**
     * 创建绑卡 --新建，用户海口银行绑卡注册的绑卡
     * @$params array
     * @$params['bankcardNo'] 银行卡号
     * @$params['name'] 开户人
     * @$params['idCardNo'] 身份证号
     * @$params['mobile'] 手机号
     *
     */
    public function bindingCard($params, $member_id){

        $no = $params['bankcardNo'];
        $name = $params['realName'];
        $id_card = $params['idCardNo'];
        $mobile = $params['mobile'];
        $bank_code = $params['bankcode'];

        if(empty($no) || empty($name) || empty($id_card) || empty($mobile))
            $this->message = '参数有误';

        //查询银行 归属名称 // 银行名称
        $cardInfo = $this->getCardInfoByNo($no);
        $bank_name = $cardInfo['bank_name'];

        //查询用户在其它通道绑卡卡号（同一用户多通道只能绑定一张卡）
        $exist_no = $this->getBindNo($member_id);
        if ($exist_no && $no != $exist_no )
            $this->message = '该卡号与其他通道绑定的卡号不一致';

        if(empty($this->message)){

            //查找该用户在海口银行绑卡信息
            $card = $this->getCard($member_id, ChannelEnum::HKYH);
            if ($card) {
                if (!$card->no == $no)
                    $this->message = '您的银行卡已被绑定，如有疑问请联系客服。';
                else
                    $this->message = '不能绑定多张卡';
            }
        }

        if(empty($this->message)){

            //判断该卡是不是已被绑卡(一个用户可能有多个帐号，在一个帐号绑了，另一个就不能再绑了)
            $bdo = $this->hasBeenBinded($no, ChannelEnum::HKYH);
            if ($bdo)
                $this->message = '您的银行卡已被绑定，如有疑问请联系客服。';
        }

        if(empty($this->message)) {

            $bind_order = QfbBindingBank::find()->where([
                'member_id' => $member_id,
                'no' => $no,
                'channel_id' => ChannelEnum::HKYH,
            ])->one();

            //创建绑卡订单-开始
            if ($bind_order) {

                //更新必要信息
                $bind_order->sn = $this->getBindSn();
                $bind_order->username = $name;
                $bind_order->id_card = $id_card;
                $bind_order->no = $no;
                $bind_order->mobile = $mobile;
                $bind_order->remark = '海口银行绑卡';

                if (!$bind_order->save())
                    $this->message = '创建绑卡订单失败';

                $this->info['order'] = $bind_order;
                $order = $this->info['order'];

            } else {

                //创建绑卡订单
                $res = $this->createBindOrder($member_id, $no, $name, $bank_name, '', '', ChannelEnum::HKYH, '', '', '', $id_card);
                $order = $this->info['order'];
                $order->mobile = $mobile;
                $order->save();
                if (!$res)
                    $this->message = '创建绑卡订单失败';

            }//创建绑卡订单-结束

            if(!empty($bank_code)) $order->bank_abbr = $bank_code;

            if(empty($this->message)){

                //存绑卡通道信息 --添加bank，bank_extend
                $sbk = $this->saveCard($order, ChannelEnum::HKYH);

                if(!empty($this->message)){
                    $this->message = '绑定银行卡失败';
                }
            }
        }

        if(empty($this->message))
            return ['code' => Code::HTTP_OK, 'msg' => '创建绑卡成功', 'data'=>$params];

        return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $this->message, 'data'=>$params];
    }

    /**
     *新建 创建海口银行绑卡订单
     *
     */
    private function createBindOrder($member_id, $no, $realname, $bank_name, $bank_code, $money, $channel, $bank_abbr = '', $province = '', $city = '',$id_card = '')
    {
        //失败时记录日志
        //LogService::log('yl_error.log', $member_id.'|'.$no.'|'.$realname.'|'.$bank_name.'|'.$channel.'|'.$id_card);

        //创建订单记录，和支付记录
        $model = new QfbBindingBank;

        $model->sn = $this->getBindSn($member_id);
        $model->member_id = $member_id;
        $model->no = $no;
        $model->name = $bank_name;
        $model->username = $realname;
        $model->check_status = 0;
        $model->province = $province;
        $model->city = $city;
        $remark = '';

        if ($channel == ChannelEnum::KUAIQIAN) {
            $remark = "快钱绑卡";
        } elseif ($channel == ChannelEnum::YILIAN) {
            $remark = "易联绑卡";
        } elseif ($channel == ChannelEnum::HUARONG) {
            $remark = "华融绑卡";
        } elseif ($channel == ChannelEnum::HKYH) {
            $remark = "海口银行绑卡";
        }

        $model->channel_id = $channel;
        $model->remark = $remark;
        $model->bank_abbr = $bank_abbr;
        $model->id_card = $id_card;

        if ($model->save()) {
            $this->info['order'] = $model;
            return true;
        } else {
            throw new \Exception('创建绑卡订单有误');
            return false;
        }
    }




    /**
     *  查询银行存管用户
     **/
    public function getHkyhUser($member_id){

        $hkyh = \Yii::$app->Hkyh;

        // 校验是否已经开户
        $serviceName = 'QUERY_USER_INFORMATION';

        //平台用户编号
        $reqData['platformUserNo'] = '2';

        $result = $hkyh->createPostParam($serviceName,$reqData);
        var_dump($result);
        exit;
        if(!empty($result)){

            $data = json_decode($result['data'], true);

            if($data['code'] == 1 || $data['status'] == 'INIT'){

                return ['code' => Code::COMMON_ERROR_CODE, 'msg' => $this->message, 'data'=>$data];
            }
        }
    }


    //生成随机字母+数字
    public function random_numbers($size = 6)
    {
        $str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";

        $len = strlen($str) - 1;
        for ($i = 0; $i < $size; $i++) {
            $code .= $str{rand(0, $len)};
        }

        return date('YmdHis') . $code;
    }
}
