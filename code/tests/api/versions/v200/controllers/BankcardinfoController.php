<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/5
 * Time: 15:35
 */
namespace api\versions\v200\controllers;
use yii;
use api\common\BaseController;
use common\models\QfbBankCardInfo;
use common\service\BankCardInfoService;
use common\enum\BankEnum;
use api\common\helpers\ReseponseCode as Code;

    /**
        * Area Controller API
        *
        * @author xiaoma <xiaomalover@gmail.com>
    */
class BankcardinfoController extends BaseController
{
    /**
     * 获取银行卡详细信息
     * @return array
     */
    public function actionGetInfo()
    {
        $data = [];
        $service = new BankCardInfoService();
        $params = $this->getParams();
        //提交过来的支付通道
        if (!isset($params['payment'])) {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失!'
            ];
        }
        $payment = $params['payment'];

        if (!$params['card'])
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失!'
            ];
        $info = $service->findModelByCard($params['card']);
        //104 308 收集开户行地址
        if ($info)
        {
            $is_support = $service->getLimitChannel($info->iss_users);                                   //是否支持此通道
            foreach ($is_support as $k=> $v)
            {
                $support[] = $v-> pt_type;
            }
            if (count($support) <= 0)
            {
                return [
                    'code' => Code::COMMON_ERROR_CODE,
                    'msg' => '暂无支付通道支持该银行卡，请换卡重试'
                ];
            }
            $channel = [];
            if ($payment == 0) {
                $channel = $service->getBindChannel($support);                                               //返回优先级最高得通道
                if (count($channel) <= 0) {
                    return [
                        'code' => Code::COMMON_ERROR_CODE,
                        'msg' => '暂无支付通道支持该银行卡，请换卡重试'
                    ];
                }
            }
            if ($channel)
            {
                if ($channel[0]['id'] == 5) {
                    if ($info->iss_users == 104 || $info->iss_users == 308) {
                        $collect = true;
                    } else {
                        $collect = false;
                    }
                } else {
                    $collect = false;
                }
                $data = [
                    'type' => $channel[0]['id'],
                    'id' => $info->id,
                    'bank_id' => $info->bank_id,
                    'iss_users' => $info->iss_users,
                    'card_n' => $info->card_no,
                    'card_len' => $info->card_len,
                    'card_bin' => $info->card_bin,
                    'card_name' => $info->card_name,
                    'bank_name' => $info->bank_name,
                    'branch_id' => $info->branch_id,
                    'branch_id2' => $info->branch_id2,
                    'card_org' => $info->card_org,
                    'card_type' => $info->card_type,
                    'card_tag' => $info->card_tag,
                    'card_tag2' => $info->card_tag2,
                    'collect_address' => $collect,
                ];
            } else {
                if ($payment == 5) {
                    if ($info->iss_users == 104 || $info->iss_users == 308) {
                        $collect = true;
                    } else {
                        $collect = false;
                    }
                } else {
                    $collect = false;
                }
                $data = [
                    'type' => $payment,
                    'id' => $info->id,
                    'bank_id' => $info->bank_id,
                    'iss_users' => $info->iss_users,
                    'card_n' => $info->card_no,
                    'card_len' => $info->card_len,
                    'card_bin' => $info->card_bin,
                    'card_name' => $info->card_name,
                    'bank_name' => $info->bank_name,
                    'branch_id' => $info->branch_id,
                    'branch_id2' => $info->branch_id2,
                    'card_org' => $info->card_org,
                    'card_type' => $info->card_type,
                    'card_tag' => $info->card_tag,
                    'card_tag2' => $info->card_tag2,
                    'collect_address' => $collect,
                ];
            }
            return [
                "code" => Code::HTTP_OK,
                "msg" => "请求成功",
                "data" => $data
            ];
        } else {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '未找到！'
            ];
        }
    }
}