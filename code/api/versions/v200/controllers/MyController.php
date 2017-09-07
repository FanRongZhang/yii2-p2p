<?php
namespace api\versions\v200\controllers;

use yii;
use yii\data\ActiveDataProvider;
use api\common\BaseController;
use common\service\OrderService;
use common\enum\OrderEnum;
use common\enum\ChannelEnum;
use common\enum\ProductEnum;
use api\common\helpers\ReseponseCode as Code;
use common\models\QfbOrder;
use common\models\QfbOrderFix;
use common\models\money\Type;
use common\models\QfbProduct;
/**
 * @author wang
 * @since 2.0
 */
class MyController extends BaseController
{
    /**
     * @return array
     * @author wsf
     */
    //我的资产记录
    public function actionRecord() {
        //用户id
        $uid = $this->member_id;
        $params = $this->getParams();
        $page = !empty($params['page']) ? $params['page'] : 1;
        $limit = !empty($params['limit']) ? $params['limit'] : 10;
        $type = $params['type'];
        $data = array();
        //1表示充值记录，2表示提现记录，3表示投资理财记录（定期理财），4表示活期赎回记录
        if ($type == 1) {
            //充值
            $orderservice = new OrderService();
            $model = $orderservice->getRechargeList($uid,$type,$page,$limit);
            if (!empty($model)) {
                foreach ($model as $k => $m) {
                    $data[$k]['type'] = $type;
                    $data[$k]['id'] = $m->id;
                    $data[$k]['title'] = "充值";
                    $data[$k]['content'] = "";
                    $data[$k]['way'] = ChannelEnum::getChannelList($m->bank_type);
                    $data[$k]['way_tips'] = "充值方式";
                    $data[$k]['money'] = $m->money;
                    if ($m->is_check == 1) {
                        $data[$k]['status'] = 2;
                    }
                    $data[$k]['status_tips'] = OrderEnum::getIsCheck($m->is_check);
                    $data[$k]['time'] = (string) date("Y-m-d",$m->create_time);
                }
            }
        } elseif ($type == 2) {
            //提现
            $orderservice = new OrderService();
            $result = $orderservice->getRechargeList($uid,$type,$page,$limit);
            if (!empty($result)) {
                foreach ($result as $k => $res) {
                    $data[$k]['type'] = $type;
                    $data[$k]['id'] = $res->id;
                    $data[$k]['title'] = "提现";
                    $data[$k]['content'] = "";
                    $data[$k]['way'] = $res->out_type == 1 ? "当日到账" : "1-3工作日到账";
                    $data[$k]['way_tips'] = "提现方式";
                    $data[$k]['money'] = $res->money;
                    if ($res->is_check == 1) {
                        $data[$k]['status'] = 2;
                    } elseif ($res->is_check == 5) {
                        $data[$k]['status'] = 1;
                    } else {
                        $data[$k]['status'] = 1;
                    }
                    $data[$k]['status_tips'] = OrderEnum::getIsCheck($res->is_check);
                    $data[$k]['time'] = (string) date("Y-m-d",$res->create_time);
                }
            }
            
        } elseif ($type == 3) {
            //定期理财
            $orderservice = new OrderService();
            $list = $orderservice->getRegularList($uid,$page,$limit);
            if (!empty($list)) {
                foreach ($list as $k => $li) {
                    $data[$k]['type'] = $type;
                    $data[$k]['id'] = $li->id;
                    $data[$k]['title'] = "投资理财";
                    $data[$k]['content'] = (string) $li['product']->product_name;
                    $data[$k]['way'] = "";
                    $data[$k]['way_tips'] = "投资金额";
                    $data[$k]['money'] = $li->money;
                    if ($li->status == 1) {
                        $data[$k]['status'] = 2;
                    } elseif($li->status == 2) {
                        $data[$k]['status'] = 2;
                    } else {
                        $data[$k]['status'] = 3;
                    }
                    if ($li->status == 1) {
                        $data[$k]['status_tips'] = "投资中";
                    } elseif($li->status == 2) {
                        $data[$k]['status_tips'] = "收益中";
                    } else {
                        $data[$k]['status_tips'] = OrderEnum::getRegular($li->status);
                    }
                    $data[$k]['time'] = (string) date("Y-m-d",$li->create_time);

                }
            }

        } else {
            //活期理财
            $orderservice = new OrderService();
            $arr = $orderservice->getRechargeList($uid,$type,$page,$limit);
            if (!empty($arr)) {
                foreach ($arr as $k => $ar) {
                    $data[$k]['type'] = $type;
                    $data[$k]['id'] = $ar->id;
                    $data[$k]['title'] = "活期赎回";
                    $data[$k]['content'] = $ar->remark;
                    $data[$k]['way'] = "";
                    $data[$k]['way_tips'] = "赎回金额";
                    $data[$k]['money'] = $ar->money;
                    if ($ar->is_check == 1) {
                        $data[$k]['status'] = 3;
                    } 
                    $data[$k]['status'] = $ar->is_check;
                    $data[$k]['status_tips'] = "已赎回";
                    $data[$k]['time'] = (string) date("Y-m-d",$ar->create_time);  
                }
            }
        }
        return [
                    'code' => Code::HTTP_OK,
                    'msg' => Code::$statusTexts[Code::HTTP_OK],
                    'data' => $data
               ];
    }

    /**
     * @author lwj
     * 我的全部定期投资
     * return $data
     */
    public function actionAll(){
        $uid = $this->member_id;
        $params = $this->getParams();
        // private int type默认值为0表示全部项目，1表示持有中，2表示已退出
        $type = intval($params['type']);
        $page= $params['page'];
        $limit= $params['limit'];
        $orderservice = new OrderService();
        $list = $orderservice->getRegularList($uid,$page,$limit,$type);
        //$data[$k]['type'] 1表示收益中，绿色，2表示抢购中，红色，3表示已退款
        $data=[];
        foreach ($list as $k => $li) {
            $data[$k]['id'] = $li->product_id;
            $data[$k]['title'] = (string)$li['product']->product_name;
            $data[$k]['rate'] = $li['product']->year_rate;
            $data[$k]['rate_tips'] = "年化收益率";
            $data[$k]['money'] = $li->money;
            if($li['status'] == 1) {
                $data[$k]['type'] = 2;
                $data[$k]['status'] ="投资中";
            }else if($li['status']==2){
                $data[$k]['type'] = 1;
                $data[$k]['status'] ="收益中";
            }else{
                $data[$k]['type'] = 3;
                $data[$k]['status'] = "已到期";
            }
            $data[$k]['time'] = (string)date("Y-m-d", $li->create_time);

        }
        //sort($data);
        return [
            'code' => Code::HTTP_OK,
            'msg' => Code::$statusTexts[Code::HTTP_OK],
            'data' => $data
        ];

    }

    /**
     * @return array
     * @author wsf
     */
    //我的资产记录详情
    public function actionRecordDetail() {
        $params = $this->getParams();
        $id = $params['id'];
        $type = $params['type'];
        //1表示充值记录，2表示提现记录，3表示投资理财记录（定期理财），4表示活期赎回记录
        if ($type == 1) {
            $model = QfbOrder::find()->joinWith('bank')->where(['=','qfb_order.id',$id])->one();
            //拼接数据
            $arr = [
                        '资金往来类型' => '充值',
                        '充值订单号' => $model->sn,
                        '充值金额' => $model->price,
                        '充值方式' => ChannelEnum::getChannelList($model->bank_type),
                        '手续费' => $model->fee,
                        '实际充值' => $model->money,
                        '支付银行' => $model->bank->name,
                        '支付账户' => $model->bank->no,
                        '支付时间' => !empty($model->complete_time) ? date("Y-m-d H:i:s",$model->complete_time) : date("Y-m-d H:i:s",$model->create_time),                       
                    ];
            $bat = new Type();
            $one = $bat->getStatusType(1)->load($arr)->all();
            //状态的颜色 
            $ar = ['充值状态' => $model->is_check == 1 ? '成功' : OrderEnum::getIsCheck($model->is_check)];
            if ($model->is_check == 1) {
                $types = 1;
            } else {
                $types = 0;
            }
            $ba = new Type();
            $two = $ba->getStatusType($types)->load($ar)->all(); 
            //合并数组
            $all = array_merge($one,$two);                 
            if (!empty($model)) {
                $data['tips'] = !empty($model->fee) ? $model->fee : ""; 
                $data['list'] = $all;                                      
            } else {
                $data = [];
            }
        } elseif ($type == 2) {
            $model = QfbOrder::find()->joinWith('bank')->where(['=','qfb_order.id',$id])->one();
            //拼接数据
            $arr = [
                        '资金往来类型' => '提现',
                        '提现订单号' => $model->sn,
                        '提现金额' => $model->price,
                        '提现方式' => $model->out_type == 1 ? '工作日当天到账' : '1-3个工作日到账',
                        '手续费' => $model->fee,
                        '实际到账' => $model->money,
                        '收款银行' => $model->bank->name,
                        '收款卡号' => $model->bank->no,
                        '申请时间' => date("Y-m-d H:i:s",$model->create_time)
                   ];
            $bat = new Type();
            $one = $bat->getStatusType(1)->load($arr)->all();
            //状态的颜色
            $ar = ['处理状态' => $model->is_check == 1 ? '通过' :OrderEnum::getIsCheck($model->is_check)]; 
            if ($model->is_check == 1) {
                $types = 2;
            } else {
                $types = 0;
            }
            $ba = new Type();
            $two = $ba->getStatusType($types)->load($ar)->all();
            //合并数组
            $all = array_merge($one,$two); 
            if (!empty($model)) {
                $data['tips'] = $model->fee;
                $data['list'] = $all;
            } else {
                $data = [];
            }
        } elseif ($type == 3) {
            $model = QfbOrderFix::find()->joinWith('product')->where(['=','qfb_order_fix.id',$id])->one();
            //起息日
            if ($model->product->profit_day === 10) {
                $date_interest = date("Y-m-d",$model->create_time);
            } elseif ($model->product->profit_day === 11) {
                $date_interest = date("Y-m-d",$model->create_time + 24 * 60 * 60);
            } elseif ($model->product->profit_day === 20) {//满标起息
                $date_interest = $model->product->finish_time ? date("Y-m-d",$model->product->finish_time) : '满标日起息';
            } elseif ($model->product->profit_day === 21) {//满标+1起息
                $date_interest = $model->product->finish_time ? date("Y-m-d",$model->product->finish_time + 24 * 60 * 60) : '满标日+1起息';
            }
            //拼接数据
            $arr = [
                        '资金往来类型' => '投资理财',
                        '投资类型' => '定期理财',
                        '投资项目' => $model->product->product_name,
                        '投资周期' => $model->product->invest_day.'天',
                        '预期年化收益率' => $model->product->year_rate.'%',
                        '预期收益' => $model->profit_money.'元',
                        '起息方式' => ProductEnum::getProfitDay($model->product->profit_day),
                        '收益方式' => ProductEnum::getProfitType($model->product->profit_type),
                        '退出方式' => '到期自动赎回',
                        '起投时间' => date("Y-m-d H:i:s",$model->create_time),
                        '起息时间' => $model->product->status === 3 ? '已流标' : $date_interest,
                        '退出时间' => $model->end_time ? date("Y-m-d",$model->end_time) : '--'
                   ];
            $bat = new Type();
            $one = $bat->getStatusType(1)->load($arr)->all();
            //状态的颜色
            $ar = ['投资状态' => $model->product->status === 3 ? "流标" : OrderEnum::getRegular($model->status)];
            if ($model->status == 1) {
                $types = 3;
            } else {
                $types = 0;
            }
            $ba = new Type();
            $two = $ba->getStatusType($types)->load($ar)->all();
            //合并数组
            $all = array_merge($one,$two);
            if (!empty($model)) {
                $data['tips'] = "";
                $data['list'] = $all;
            } else {
                $data = [];
            }
        } elseif ($type == 4) {
            $model = QfbOrder::find()->joinWith('bank')->where(['=','qfb_order.id',$id])->one();
            $pro = QfbProduct::find()->where(['=','product_type',1])->one();
            //拼接数据
            $arr = [
                        '资金往来类型' => '活期赎回',
                        '赎回时间' => date("Y-m-d H:i:s",$model->complete_time),
                        '赎回金额' => $model->money
                        
                   ];
            $bat = new Type();
            $one = $bat->getStatusType(1)->load($arr)->all();
            //状态的颜色
            $ar = ['投资状态' => $model->is_check == 1 ? '已赎回' : OrderEnum::getIsCheck($model->is_check)];
            if ($model->is_check == 1) {
                $types = 1;
            } else {
                $types = 0;
            }
            $ba = new Type();
            $two = $ba->getStatusType($types)->load($ar)->all();
            //合并数组
            $all = array_merge($one,$two);
            if (!empty($model)) {
                $data['tips'] = $model->fee;
                $data['list'] = $all;
            } else {
                $data = [];
            }           
        }

        return [
            'code' => Code::HTTP_OK,
            'msg' => Code::$statusTexts[Code::HTTP_OK],
            'data' => $data
        ];
    }
}
