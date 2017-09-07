<?php 
namespace common\service;
use common\models\QfbProduct;
use api\models\ProductDetail;
use common\models\QfbProCategory;
use common\enum\ProductEnum;
use yii;
use Exception;
use common\models\QfbProduct as Product;
use common\models\orderFix\ProfitTime;
class ProductService extends BaseService{
	protected $_className="common\models\QfbProduct";

	/*
	 * 取产品
	 * jin
	 */
	public static function getProduct($id = 0){
	    return QfbProduct::findOne(['id'=>$id]);
	}
	
	/*
	 * 取活期产品
	 * jin
	 */
	public static function getLiveProduct(){
	    return QfbProduct::findOne(['product_type'=>1]);
	}
	
	public function buyProductList($id,$page = 1,$limit = 10){
		$this->model = $this->findModel($id);
		//print_r(yii::$app->user->identity->id);
		if($this->model ==null) {
			$this->addMessage('id','产品不存在');
			return false;
		}

		switch ($this->model->product_type) {
			case ProductEnum::LIVE ://活期
				# code...
				break;
			case ProductEnum::FIX ://定期
				$service = new OrderFixService();
				$data=$service->buyProductList($id,$page,$limit);
				# code...
				break;
			default:
				# code...
				break;
		}

		return $data;

	}



	/**
	 * 产品详情
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function detail($id){
		//$model = new $this->_className;
		$model=QfbProduct::find()->joinWith('product_agreement')->joinWith('product_detail')
		->select("qfb_product.id,qfb_product.category_id,qfb_product.end_time,qfb_product.product_name,qfb_product.min_money,qfb_product.max_money,qfb_product.has_money,qfb_product.stock_money,qfb_product.step_money,qfb_product.profit_type,qfb_product.profit_day,
			qfb_product.year_rate,qfb_product.status,qfb_product.end_time,qfb_product.invest_day,qfb_product.is_newer,
			qfb_product_detail.detail,
			qfb_agreement.title,qfb_agreement.id agreement_id,qfb_agreement.pic_url")
		->where(['=','qfb_product.id',$id])
		->asArray()
		->one();
		//echo '<pre/>';var_dump($model);exit;
		return $model;
	}

	public function getDetail($id){
		$model = $this->detail($id);
		$modelDetail = new ProductDetail($model,new OrderFixService());
		return $modelDetail->run();
	}

	public function validStockMoney($model){
		if($model['stock_money']==0) return true;
		return $model['stock_money']<=$model['has_money']?false:true;
	}
	public function checkCanBuy($model,$orderService){
		if($model['is_newer']!=1)return true;
		return $orderService->isBuy()?false:true;
	}

    /**
     * 查询产品的协议
     * @param  [type] $id [description]
     * @author wsf
     */
    public function getAgreement($id){
        $model = Product::find()->joinWith('product_agreement')->where(['=','qfb_product.id',$id])->asArray()->all();
        return $model;
    }

	/**
	 * 增加产品金额
	 * [addHasMoney description]
	 * @param [type] $id    [description]
	 * @param [type] $money [description]
	 */
	public function addHasMoney($id,$money){
		try{
            $command = yii::$app->db;
            $arr=$command->createCommand(sprintf("select has_money,stock_money from qfb_product where id = %d for update",$id))->queryOne(); 

            if($arr['stock_money'] > ($money+$arr['has_money'])){
               $row = $command->createCommand(sprintf("UPDATE qfb_product SET has_money = has_money + %.2f WHERE id = %d;",$money,$id))->execute();
            }else if($arr['stock_money']== ($money+$arr['has_money'])){

            	switch ($this->model->profit_day) {
            		//起息日:(1)10:投资日(2)11:投资日+1(3)20:满标日(4)21:满标日+1
					case 10:
						$row = $command->createCommand(sprintf("UPDATE qfb_product SET has_money = has_money + %.2f,finish_time = %d,status = %d WHERE id = %d;",$money,time(),ProductEnum::STATUS_FINISH,$id))->execute();
						break;
					case 11:
						$row = $command->createCommand(sprintf("UPDATE qfb_product SET has_money = has_money + %.2f,finish_time = %d,status = %d WHERE id = %d;",
							$money,time(),ProductEnum::STATUS_FINISH,$id))->execute();
						break;
					case 20:
						$start_time = strtotime(date("Ymd"));
						$orderFix = self::setProductProfitType($start_time);
						$row = $command->createCommand(
							sprintf("
								UPDATE qfb_product SET has_money = has_money + %.2f,finish_time = %d,status = %d WHERE id = %d;
								UPDATE qfb_order_fix SET status = 2 , next_profit_time = %d ,end_time = %d where product_id = %d AND status=1;
								",$money,time(),ProductEnum::STATUS_FINISH,$id,$orderFix->next_profit_time,$orderFix->end_time,$id)
							)->execute();
						break;
					case 21:
						$start_time = strtotime(date("Ymd"))+24*3600;
						$orderFix = self::setProductProfitType($start_time);

						$row = $command->createCommand(sprintf("
							UPDATE qfb_product SET has_money = has_money + %.2f,finish_time = %d,status = %d WHERE id = %d;
							UPDATE qfb_order_fix SET status = 2 , next_profit_time = %d ,end_time = %d where product_id = %d AND status=1;
							",$money,time(),ProductEnum::STATUS_FINISH,$id,$orderFix->next_profit_time,$orderFix->end_time,$id))->execute();
						break;
				}
            }else{
            	throw new Exception("产品剩余不足");
            }
        }catch(Exception $e){
            $this->addMessage('error',$e->getMessage());
            return false;
        }
        return true;
	}

	/**
	 * 计算下次分润时间
	 * [setProductProfitType description]
	 * @param [type] $start_time [description]
	 */
	public function setProductProfitType($start_time){
		$OrderFix = new ProfitTime();
		if($start_time==0){
			return $OrderFix;
		}
		switch ($this->model->profit_type)
		{
			//收益方式:(1)到期还本付息(2)按月等额付息，到期还本(3)按日等额付息，到期还本(4)按月等额本息(5)按日等额本息(6)按月等额还本，到期付息(7)按日等额还本，到期付息
			case 1:$OrderFix->next_profit_time = $start_time+$this->model->invest_day*24*3600;break;
			case 2:$OrderFix->next_profit_time = $start_time+30*24*3600;break;
			case 3:$OrderFix->next_profit_time = $start_time+24*3600;break;
			case 4:$OrderFix->next_profit_time = $start_time+30*24*3600;break;
			case 5:$OrderFix->next_profit_time = $start_time+24*3600;break;
			case 6:$OrderFix->next_profit_time = $start_time+30*24*3600;break;
			case 7:$OrderFix->next_profit_time = $start_time+24*3600;break;
			default:break;
		}
		$OrderFix->end_time= $start_time + $this->model->invest_day*24*3600 ;
		return $OrderFix;
	}



	/**
	 * 判断金额合理性
	 * [validMoney description]
	 * @param  [type] $money [description]
	 * @return [type]        [description]
	 */
	public function validMoney($money){
		if($this->model==null) return false;
		if($money<=0 || $this->model->min_money>$money){
			$this->addMessage('min_money','低于最小金额');
			return false;
		}else if($this->model->max_money<$money){
			$this->addMessage('max_money','超过最大金额');
			return false;
		}else if((bcsub($money,$this->model->min_money,2)*100)%($this->model->step_money*100)!=0) {
			$this->addMessage('step_money',"本项目{$this->model->step_money}元整数倍起投");
			return false;
		}
		return true;
	}
	/**
	 * 判断是否支持代金券
	 * [validMoneyTicket description]
	 * @param  integer $moneyTicket_id [description]
	 * @return [type]                  [description]
	 */
	public function validMoneyTicket($moneyTicket_id=0){
		if($moneyTicket_id==0) return true;
		if($this->model->can_money_ticket){
			return true;
		}else{
			$this->addMessage('can_money_ticket','不支持代金券');
			return false;
		}
	}

	/**
	 * 判断是否支持加息券
	 * [validRateTicket description]
	 * @param  integer $rateTicket_id [description]
	 * @return [type]                 [description]
	 */
	public function validRateTicket($rateTicket_id=0){
		if($rateTicket_id==0) return true;
		return $this->model->can_rate_ticket?true:false;
	}

	/**
	 * 判断是否是活期产品
	 * [validLive description]
	 * @return [type] [description]
	 */
	public function validLive(){
		if($this->model==null) return false;

		if($this->model->product_type==ProductEnum::LIVE){
			return true;
		}else{
			$this->addMessage('product_type','不是活期产品');
			return false;
		}
	}
    /**
     * 判断是否是活期产品
     * [validLive description]
     * @return [type] [description]
     */
    public function validIsNewer(){
        if($this->model==null) return false;
        if($this->model->is_newer==1){
            return true;
        }else{
            $this->addMessage('product_type','不是新手产品');
            return false;
        }
    }
    /**
     * 设置产品的下次分润时间以及结束时间
     * [setProductDay description]
     */
    public function setProfitDay(){
        switch($this->model->profit_day){
            //起息日:(1)10:投资日(2)11:投资日+1(3)20:满标日(4)21:满标日+1
            case 10:
                $start_time = strtotime(date("Ymd"));
                $orderfix = $this->setProductProfitType($start_time);
                $orderfix->status = 2;
                break;
            case 11:
                $start_time = strtotime(date("Ymd"))+24*3600;
                $orderfix = $this->setProductProfitType($start_time);
                $orderfix->status = 2;
                break;
            case 20:
                $orderfix = $this->setProductProfitType(0);
				$orderfix->status = 1;
                break;
            case 21:
                $orderfix = $this->setProductProfitType(0);
				$orderfix->status = 1;
                break;
            default :
                $this->addMessage('profit_day','产品配置错误');
                return false;
                break;
        }
        return $orderfix;
    }
	/**
	 * 判断是否是定期产品
	 * [validFix description]
	 * @return [type] [description]
	 */
	public function validFix(){
		if($this->model==null) return false;

		if($this->model->product_type==ProductEnum::FIX){
			return true;
		}else{
			$this->addMessage('product_type','不是定期产品');
			return false;
		}
	}

	/**判断产品状态
	 * [validStatus description]
	 * @param  [type] $status [description]
	 * @return [type]         [description]
	 */
	public function validStatus($status){
		if($this->model==null) return false;
		if($this->model->status == $status){
			return true;
		}else{
			switch ($status) {
				case ProductEnum::STATUS_CREATE:
					$this->addMessage('status','产品未'.ProductEnum::getStatus(ProductEnum::STATUS_CREATE));
					break;
				case ProductEnum::STATUS_BUY:
					$this->addMessage('status','产品不在'.ProductEnum::getStatus(ProductEnum::STATUS_BUY).'期');
					break;
				case ProductEnum::STATUS_FINISH:
					$this->addMessage('status','产品未'.ProductEnum::getStatus(ProductEnum::STATUS_BUY));
					break;
				case ProductEnum::STATUS_OVER:
					$this->addMessage('status','产品未'.ProductEnum::getStatus(ProductEnum::STATUS_OVER));
					break;
				case ProductEnum::STATUS_CLOSE:
					$this->addMessage('status','产品未'.ProductEnum::getStatus(ProductEnum::STATUS_CLOSE));
					break;
				default:
					# code...
					break;
			}
			return false;
		}
	}
	/**
	 * 验证时间是否在有效期内
	 * [validTime description]
	 * @return [type] [description]
	 */
	public function validTime(){
		if($this->model==null) return false;
		if($this->model->start_time>0 && $this->model->start_time > $this->getTime()){
			$this->addMessage('start_time','产品未开始');
			return false;
		}else if($this->model->end_time>0 && $this->model->end_time < $this->getTime()){
			$this->addMessage('start_time','产品已结束');
			return false;
		}
		return true;
	}

	/**
	 *	所有理财产品
	 *  @author wsf
	 */
	public function getAllList($type,$sort,$page,$limit,$is_index="",$stock_money="",$category_id="", $is_app=false) {
		$query = Product::find();
		$query->select("qfb_product.id,qfb_product.product_type,qfb_product.product_name,qfb_product.year_rate,qfb_product.is_newer,qfb_product.invest_day,qfb_product.status,qfb_product.has_money,qfb_product.stock_money,qfb_product.start_time,qfb_product.end_time,qfb_product.min_money,qfb_product.max_money,qfb_product.profit_type,qfb_product.category_id");
		$query->offset(($page-1)*$limit);
		$query->limit($limit);
		$query->joinWith('product_detail');

		// 首页推荐只显示筹集中且不过期 产品
		if($is_app){
			//
			$query->where('qfb_product.status=1 and qfb_product.stock_money > qfb_product.has_money');
			$query->andWhere(['>','qfb_product.end_time',time()]);
		}else{
			$query->where(['in','qfb_product.status',array(1,2,6,7,8)]);
		}

		$query->andWhere(['=','qfb_product.is_hidden',0]);
		// $query->andWhere(['>','qfb_product.end_time',time()]);
		$type=2;

		if ($type == 0) {
			$query->andWhere(['in','qfb_product.product_type',array(1,2)]);
		} else {
			$query->andWhere(['=','qfb_product.product_type',$type]);
		}

		if ($category_id == 1) {
			$query->andWhere(['=','qfb_product.category_id',1]);
		} else {
			$query->andWhere(['!=','qfb_product.category_id',1]);
		}

		if($is_index) $query->andwhere(['=','is_index',$is_index]);
		//投资金额不为0时显示
		if ($stock_money) $query->andwhere(['>','stock_money',0]);
		//根据sort排序
		if ($sort == 1) {
			$query->orderBy('qfb_product.create_time desc');
		} elseif ($sort == 2) {
			$query->orderBy('qfb_product.year_rate desc');
		} elseif ($sort == 3) {
			$query->orderBy('qfb_product.year_rate asc');
		} elseif ($sort == 4) {
			$query->orderBy('qfb_product.invest_day desc');
		} elseif ($sort == 5) {
			$query->orderBy('qfb_product.invest_day asc');
		}

		$query->asArray();
		$result = $query->all();
		return $result ;
	}


	/**
     * 返回产品列表
     * @author lwj
     */
    public function getList($list){
    	$data = [];
        foreach ($list as $key => $val) {
            if($val['end_time'] < time()){
                $time = 0;
                $status = '已满额';
                $type = 2;
            }else if($val['has_money'] >= $val['stock_money']){
                $buy_tips = '已满额';
                $type = 2;
                $time = 0;
                $status = "已满额";
            }else if($val['status'] == 2){
                $type = 2;
                $time = 0;
                $status = "已满额";
            }else{
                $type = 1;
                $status = "抢购中";
                $time = ($val['end_time'] - time())*1000;
            }


            if ($val['product_type'] == 1) { //产品类型:1为活期,暂时不用
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
                    "buy_tips" => isset($buy_tips) ? $buy_tips : " ",

                ];
            } else {
                //购买状态(百分比)
                $buy_status = $val['has_money'] * 100 / $val['stock_money'];
                $data[] = [
                    "type" => $type,
                    "id" => $val['id'],
                    "name" => $val['product_name'],
                    "tips" => !empty($val['product_detail']['tips']) ? $val['product_detail']['tips'] : "",
                    "rate" => $val['year_rate'] . "%",
                    "rate_tips" => "预期年化收益率",
                    "invest_day" => $val['invest_day'],
                    "date_tips" => "期限",
                    "buy" => true,
                    "buy_tips" => isset($buy_tips) ? $buy_tips : " ",
                    "buy_text" => "抢",
                    "buy_status" => ($buy_status > 99 && $buy_status < 100) ? floor($buy_status): ceil($buy_status),
                    "end_time" => $time,
                    "status" => $status,
                    "min_money" => $val['min_money'],
                    "max_money" => $val['max_money'],
                    "profit_type" => $val['profit_type'],
                    "stock_money" => $val['stock_money'],
                    "has_money" => $val['has_money'],
                    "category_id" => $val['category_id'],
                ];
            }
        }
        return $data;
    }


    /**
     * 产品分类
     * @author lwj
     */
    public function getProCategoryList(){
        $data = QfbProCategory::find()->asArray()->all();
        return $data;
    }
    /**
     * 根据分类id获取产品
     * @authro lwj
     */
    public function  getProductByCid($cid){
        return QfbProCategory::find()->select("qfb_product.id")->asArray()->joinWith("product")
            ->andWhere(['=','qfb_product.category_id',$cid])
            ->andWhere(['=','qfb_product.status',1])
            ->one();
    }


    /**
     * 根据借款用户id获取借款金额
     * @authro lwj
     */
    public function  getProductByMoney($member_id){
        $result = Product::find()->asArray()
            ->andWhere(['=','member_id',$member_id])
            ->andWhere(['=','credit_status',1])
            ->sum("stock_money");
        if (isset($result) && !empty($result)) {
        	return $result;
        } else {
        	return 0;
        }          
    }


    /**
     * 根据借款用户id获取该借款人对平台的收益
     * @authro lwj
     */
    public function  getPlatform($member_id){
        $result = Product::find()->asArray()
            ->andWhere(['=','member_id',$member_id])
            ->andWhere(['=','credit_status',1])
            ->sum("platform_income");
        if (isset($result) && !empty($result)) {
        	return $result;
        } else {
        	return 0;
        }          
    }
}

