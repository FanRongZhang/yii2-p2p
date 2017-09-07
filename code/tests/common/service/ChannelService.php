<?php

namespace common\service;

use common\enum\ChannelEnum;
use common\models\QfbBank;
use common\models\QfbBankExtend;
use common\models\QfbBankLimit;
use common\models\QfbChannel;
use common\models\QfbMember;
use common\service\BankService;

/**
 * 支付通道服务类
 * @author xiaoma <xiaomalover@gmail.com>
 * @since 2.0
 */
class ChannelService extends BaseService
{

    /**
     * 组合通道列表信息
     * @author xiaoma <xiaomalover@gmail.com>
     * @param  Int $member_id 用户id
     * @param Int $type 支付类型，（1充值，2提现）
     * @return Array $list 列表信息
     */
    public static function getChannelList($member_id, $type)
    {
        //首先找出已绑卡通道及卡信息
        $bcd = self::getBindChannel($member_id, $type);
        if ($bcd) {
            //已绑卡通道列表
            $bindChannel = $bcd['list'];
            //已绑银行卡的卡编号，用于后续查询支持的未绑卡通道
            $bankCode = $bcd['bankCode'];
            //已绑定的银行卡信息
            $bank = $bcd['bank'];
            //存已绑卡的通道id 供获取未绑卡通道排除用
            $bindIds = $bcd['bindIds'];
            //目前已排除易联绑卡
            $bindIds[] = ChannelEnum::HUARONG;
            $bindIds[] = ChannelEnum::KUAIQIAN;
            $bindIds[] = ChannelEnum::ZHENGLIAN;
            //找出用户未绑卡通道列表
            $unBindChannel = self::getUnbindChannel($bankCode, $bindIds, $bank);

            //如果是购买产品，加上零钱支付
            if ($type == ChannelEnum::BUY_CURRENT || $type == ChannelEnum::BUY_FIXED) {
                $money = self::getMoneyStruct($bank->id);
                $bindChannel = array_merge($bindChannel, $money);
            }
            //合并已绑卡和未绑卡的通道信息
            $list = array_merge($bindChannel, $unBindChannel);

            return $list;
        } else {
            //如果是购买产品，加上零钱支付, 证联支付
            if ($type == ChannelEnum::BUY_CURRENT || $type == ChannelEnum::BUY_FIXED) {
                $money = self::getMoneyStruct();
                $canBind = self::getCanBindChannel($member_id);
                return array_merge($canBind, $money);
            } else {
                return [];
            }
        }
    }

    /**
     * 找出已绑卡通道及卡信息
     * @author xiaoma <xiaomalover@gmail.com>
     * @param  Int $member_id 用户id
     * @return Array 已绑卡的通道
     */
    public static function getBindChannel($member_id, $type)
    {
        //查出所有通道绑定的卡信息（这里所有通道只能绑同一张卡）
        //虽然可能有多个通道绑卡记录，但卡号是同一个
        $bindList = QfbBankExtend::find()
            ->joinWith('bank')
            ->where([
                QfbBank::tableName() . '.member_id' => $member_id,
                QfbBank::tableName() . '.is_del' => 0,
                QfbBankExtend::tableName() . '.is_del' => 0,
            ])->all();

        if ($bindList) {
            $item = $bindIds = [];
            $bankInfo = $bankCode = $bank = '';
            foreach ($bindList as $v) {
                //去除不再支持的通道
                if (
                    $v->channel_id == ChannelEnum::ZHENGLIAN ||
                    $v->channel_id == ChannelEnum::KUAIQIAN
                ) {
                    continue;
                }
                $data['type'] = $v->channel_id;
                $data['bank_id'] = $v->bank->id;
                $data['name'] = $v->bank->name;
                $data['bank_no'] = $v->bank->no;
                $data['mobile'] = $v->bank->mobile;

                //查询卡信息,由于多通道同卡，所以卡信息只需要查一次
                if (!$bankInfo) {
                    $bsc = new BankService;
                    $bankInfo = $bsc->getCardInfoByNo($v->bank->no);
                }
                $data['bank_code'] = $bankCode = $bankInfo ? $bankInfo['iss_users'] : '';

                //存已绑卡的卡信息
                if (!$bank) {
                    $bank = $v->bank;
                }

                //查询限额信息（只有购买产品时才查询）
                if (
                    ($type == ChannelEnum::BUY_CURRENT ||
                    $type == ChannelEnum::BUY_FIXED) &&
                    $data['bank_code']
                ) {
                    $blt = self::getLimit($data['bank_code'], $data['type']);
                    $data['limit'] = $blt ? $blt->one_trade : '';
                    $day_trade = $blt ? $blt->day_trade : '';
                } else {
                    $data['limit'] = '';
                    $day_trade = '';
                }

                //转单位
                if ($data['limit'] >= 10000) {
                    $data['limit'] = $data['limit'] / 10000 . "万";
                }
                if ($day_trade >= 10000) {
                    $day_trade = $day_trade / 10000 . "万";
                }

                $data['tips'] = "注：单笔限额" . $data['limit'] . "元，每日限额" . $day_trade . "元";

                //存已绑卡的通道id 供获取未绑卡通道排除用
                $bindIds[] = $v->channel_id;

                //存已绑卡通道列表
                $item[] = $data;
            }
            return ['list' => $item, 'bankCode' => $bankCode,
                'bank' => $bank, 'bindIds' => $bindIds];
        } else {
            return [];
        }
    }

    /**
     * 获取通道对应银行的充值限额
     * @author xiaoma <xiaomalover@gmail.com>
     * @param  Int $bankCode 银行代号
     * @param  Int $channel 通道id
     * @return ActiveRecorder 限额对象
     */
    public static function getLimit($bankCode, $channel)
    {
        return QfbBankLimit::findBySql("SELECT `one_trade`, `day_trade` FROM "
            . QfbBankLimit::tableName()
            . " WHERE  FIND_IN_SET('{$bankCode}', iss_users) and pt_type = {$channel}")
            ->one();
    }

    /**
     * 根据用户已绑卡号，查出支持的未绑卡通道
     * @author xiaoma <xiaomalover@gmail.com>
     * @param  Int $bankCode 银行代号
     * @param  Int $bindIds 用户已绑定的通道id
     * @return Array 未绑卡通道列表
     */
    public static function getUnbindChannel($bankCode, $bindIds, $bank)
    {
        if ($bankCode) {
            //排除已绑卡的通道
            $bindIdsStr = implode(",", $bindIds);
            $bkl = QfbBankLimit::findBySql("SELECT * FROM "
                . QfbBankLimit::tableName()
                . " WHERE  FIND_IN_SET('{$bankCode}', iss_users) and is_support=1 and pt_type not in ({$bindIdsStr})")
                ->all();
            if ($bkl) {
                $item = [];
                foreach ($bkl as $v) {
                    $data['type'] = $v->pt_type;
                    $data['bank_id'] = 0;
                    $data['name'] = '未绑卡';
                    $data['bank_no'] = $bank->no;
                    $data['mobile'] = $bank->mobile;
                    $data['bank_code'] = $bankCode;
                    $data['limit'] = '';
                    $data['tips'] = '';
                    $item[] = $data;
                }
                return $item;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * 获取零钱支付的数据结构
     * 在购买产品时要用到
     * 即使用户没有绑卡，在购买产品时都要返回
     * 如果有绑上，要填bank_id，零钱有bank_id 有些怪异
     * @return Array
     */
    private static function getMoneyStruct($bank_id = 0)
    {
        return [
            [
                'type' => ChannelEnum::MONEY,
                'bank_id' => $bank_id,
                'name' => '零钱支付',
                'bank_code' => '',
                'bank_no' => '',
                'limit' => '',
                'mobile' => '',
                'tips' => '',
            ],
        ];
    }

    /**
     * 未绑卡情况下，查出支持的卡通道
     * @author xiaoma <xiaomalover@gmail.com>
     * @return Array 支持的卡通道列表
     */
    public static function getCanBindChannel($member_id)
    {
        $member = QfbMember::findOne($member_id);
        $mobile = $member->mobile;

        //判断用户是否有实名
        $is_verify = $member->memberInfo->is_verify;
        $filter_verify = $is_verify ? "" : " and need_certification <> 1";

        //目前只展示代收代付，无需单独实名的通道
        $bkl = QfbChannel::findBySql("SELECT * FROM "
            . QfbChannel::tableName()
            . " WHERE in_status=1 and out_status=1".$filter_verify." order by sort asc")
            ->all();
        if ($bkl) {
            $item = [];
            foreach ($bkl as $v) {
                //去除不再支持的通道
                if (
                    $v->id == ChannelEnum::ZHENGLIAN ||
                    $v->id == ChannelEnum::KUAIQIAN /*||
                    $v->id == ChannelEnum::YILIAN*/
                ) {
                    continue;
                }
                $data['type'] = $v->id;
                $data['bank_id'] = 0;
                $data['name'] = '未绑卡';
                $data['bank_no'] = '';
                $data['mobile'] = $mobile;
                $data['bank_code'] = '';
                $data['limit'] = '';
                $data['tips'] = '';
                $item[] = $data;
            }
            return $item;
        } else {
            return [];
        }
    }

}
