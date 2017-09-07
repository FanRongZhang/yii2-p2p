<?php
namespace api\versions\v200\controllers;

use common\models\QfbNotice;
use yii\data\ActiveDataProvider;
use api\common\BaseController;
use common\service\ProductService;
use common\service\MemberService;
use yii;
use common\models\QfbProfitSettings;
use api\common\helpers\ReseponseCode as Code;
use common\service\BannerService;
use common\service\MongoService\MessageService as MongoMessageService;
use common\service\MessageService;
/**
 * @author wang
 * @since 2.0
 */
class IndexController extends BaseController
{
    /**
     * @author wang
     * @since 2.0
     */
    //18所有理财列表
    public function actionAll() {
        $params = $this->getParams();
        $page = $params['page'] ? $params['page'] : 1;
        $limit = $params['limit'] ? $params['limit'] : 10;
        $type = !empty($params['type']) ? $params['type'] : 0 ;
        $sort = !empty($params['sort']) ? $params['sort'] : 1 ;
        $channelId = !empty($params['channelId']) ? $params['channelId'] : 1;
        $model = new ProductService();
        $result = $model->getAllList($type,$sort,$page,$limit,0,1,'',true);

        //今天的时间戳
        $today = strtotime(date("Y-m-d H:i:s", time()));
        $data = array();
        if (!empty($result)) {
            foreach ($result as $k => $res) {
                if ($res['product_type'] == 1) {
                    $data[$k]['type'] = (int) $res['product_type'];
                    $data[$k]['id'] = (int) $res['id'];
                    $data[$k]['name'] = $res['product_name'];
                    $data[$k]['pic'] = !empty($res['product_detail']['tips']) ? $res['product_detail']['tips'] : "";
                    $data[$k]['rate'] = $res['year_rate'] / 360 * 7 .'%';
                    $data[$k]['rate_tips'] = "近七日收益率";
                    //万份收益
                    $rate_money = 10000 * $res['year_rate'] / 365;
                    $p= stripos($rate_money , '.');
                    $data[$k]['profit'] = substr($rate_money ,0 , $p+5);
                    $data[$k]['profit_tips'] = "万份收益";
                    //不是募集中不能申购
                    if ($res['status'] == 1) {
                        $data[$k]['buy'] = true;
                        $data[$k]['buy_tips'] = "";
                    } else {
                        $data[$k]['buy'] = false;
                        $data[$k]['buy_tips'] = "还不可以购买";
                    }
                } else {
                    //过滤已满的情况
                    // if ($res['has_money'] / $res['stock_money'] < 1) {
                    $data[$k]['type'] = (int) $res['product_type'];
                    $data[$k]['id'] = (int) $res['id'];
                    $data[$k]['name'] = $res['product_name'];
                    $data[$k]['pic'] = "";
                    //安卓、ios新手图标
                    if ($res['is_newer'] == 1) {
                        $data[$k]['tips'] = !empty($res['product_detail']['tips']) ? $res['product_detail']['tips'] : "";
                    } else {
                        $data[$k]['tips'] = "";
                    }
                    $data[$k]['rate'] = $res['year_rate'].'%';
                    $data[$k]['rate_tips'] = "预期年化收益率";
                    $data[$k]['profit'] = "";
                    $data[$k]['profit_tips'] = "";
                    $data[$k]['date'] = $res['invest_day'].'天';
                    $data[$k]['date_tips'] = "期限";
                    //不在募集的时间之内和状态不是募集中不能申购
                    if ($today >= $res['start_time'] && $today <= $res['end_time'] && $res['status'] == 1) {
                        $data[$k]['buy'] = true;
                        $data[$k]['buy_tips'] = "";
                    } else {
                        // 不可购买的商品允许进入商品详情
                        $data[$k]['buy'] = true;
                        $data[$k]['buy_tips'] = "还不可以购买";
                    }
                    if ($res['has_money']/$res['stock_money'] == 1) {
                        $data[$k]['buy_text'] = "已满";
                    } else {
                        $data[$k]['buy_text'] = "抢";
                    }
                    //购买状态（百分比）
                    $buy_status = $res['has_money'] * 100 / $res['stock_money'];
                    $data[$k]['buy_status'] = (int) ($buy_status > 99 && $buy_status < 100) ? floor($buy_status): ceil($buy_status);
                    $data = array_values($data);
                    // }
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
     * 首页
     * @author lwj
     */
    public function actionIcon(){
        $params=$this->getParams();
        $productServ = new ProductService();
        $index_data = $productServ->getProCategoryList();

        $data=[];
        foreach($index_data as $key=>$val)
        {
            // 过滤活期理财
            if($val['id'] == '2')
                continue;

            /**新手尊享和活期时传入产品id*/
            if(in_array($val['id'],[1,2]))
            {
                //根据分类id获取产品id
                $product_id = $productServ->getProductByCid($val['id'])['id'];
            }else
                $product_id="";
            $data["index_data"][]=[
                'type'=>intval($val['id']),
                'id'=>$product_id?intval($product_id):0,
                'title'=>$val['category_name'],
                'content'=>$val['category_des'],
                'rate'=>$val['rate'],
                'rate_tips'=>$val['rate_tips'],
                'pic'=>$val['pic'],
                'tips'=>$val['icon'],
                'buy'=>$product_id ==0 ?false:true,
                'buy_tips'=>"暂无可投项目"
            ];
        }
        /**首页轮播图*/
        $banner = new BannerService();
        $ad_data = $banner->getData();
        foreach ($ad_data as &$value)
        {
            $value['type'] = (int)$value['type'];
            $value['share_type'] = (int)$value['share_type'];
            $value['imgurl'] = yii::$app->params['img_domain']."/".$value['imgurl'];
        }
        $data['ad_data'] = $ad_data;

        /**公告列表*/
        $notice = new QfbNotice();
        $data['msg_data']=$notice->getNotice();
        foreach($data['msg_data'] as $key=>$val)
        {
            $data['msg_data'][$key]['click']=true;
        }
        $data['msg_data'] = isset($data['msg_data'])?$data['msg_data']:[];
        $data['ad_data'] = isset($data['ad_data'])?$data['ad_data']:[];
        $data['index_data'] = isset($data['index_data'])?$data['index_data']:[];
        $data['recommend']="为您推荐";
        $data['message']="20";
        $data['assure']="账号资金安全由太平洋保险100%承保";
        $data['assure_pic']="https://api.qianfb.com/img/icon_safe.png";
        return  ["code"=>Code::HTTP_OK,"msg"=>"请求成功","data"=>$data];
        // return ApiService::success(200,'请求成功',$data);
    }
    /**首页推荐
     * @author lwj
     */
    public function actionRecommend()
    {
        $proServ = new ProductService();
        $list = $proServ->getAllList($type = 0, $sort = 1, $page = 1, $limit = 10, $is_index = 1, $stock_money = 1, '', true);
        $data = [];
        foreach ($list as $key => $val) {
            if ($val['product_type'] == 1) {
                $data[] = [
                    "type" => $val['product_type'],
                    "id" => $val['id'],
                    "name" => $val['product_name'],
                    "pic" => !empty($val['product_detail']['tips']) ? $val['product_detail']['tips'] : "",
                    "rate" => $val['year_rate']."%",
                    "rate_tips" => "预期年化收益率",
                    "profit" => sprintf("%.2f", $val['year_rate'] / 100 * 10000 / 365)."元",
                    "profit_tips" => "每万份收益",
                    "buy" => true,
                    "buy_tips" => "",

                ];
            } else {

                if ($val['has_money']/$val['stock_money'] == 1) {
                    $tatus_str = "已满";
                } else {
                    $tatus_str = "抢";
                }

                //购买状态(百分比)
                $buy_status = $val['has_money'] * 100 / $val['stock_money'];

                $data[] = [
                    "type" => $val['product_type'],
                    "id" => $val['id'],
                    "name" => $val['product_name'],
                    "tips" => !empty($val['product_detail']['tips']) ? $val['product_detail']['tips'] : "",
                    "rate" => $val['year_rate'] . "%",
                    "rate_tips" => "预期年化收益率",
                    "date" => $val['invest_day']."天",
                    "date_tips" => "期限",
                    "buy" => true,
                    "buy_tips" => "",
                    "buy_text" => $tatus_str,
                    "buy_status" => ($buy_status > 99 && $buy_status < 100) ? floor($buy_status): ceil($buy_status),
                ];
            }
        }
        return ["code" => Code::HTTP_OK, "msg" => "请求成功", "data" => $data];
    }

}


