<?php
namespace common\enum; 

class ProductEnum{
	/**status状态**/
	const ALL = ''; //全部
	const STATUS_CREATE = 0;//创建
	const STATUS_BUY = 1;	//筹集
	const STATUS_FINISH = 2;  	//售罄
	const STATUS_OVER = 3;	//流标
	const STATUS_CLOSE= 4;	//关闭
	const STATUS_PROMISING= 5;	//放款中
	const STATUS_PAYING= 6;	//已放款,还款中
	const STATUS_AFFIRM= 7;	//已还款,待确认
	const STATUS_PAYMANT= 8; //确认已还款

	public static function getStatus($index = null){
		$arr = [
			self::ALL =>'全部',
			self::STATUS_CREATE=>'已创建',
			self::STATUS_BUY => '筹集中',
			self::STATUS_FINISH => '售罄',  	//售罄
			self::STATUS_OVER => '流标',	//流标
			self::STATUS_CLOSE=> '关闭',	//关闭
			self::STATUS_PROMISING=> '放款中',	//放款中
			self::STATUS_PAYING=> '已放款',	//已放款
			self::STATUS_AFFIRM=> '已还款,待确认',	//已还款
			self::STATUS_PAYMANT=> '确认已还款',	//确认已还款
		];
		return ($index === null)?$arr:$arr[$index];
	}


	/**产品类型1活期，2定期**/
	const TYPE_ALL = '';
	const LIVE = 1;
	const FIX=2;
	public static function getType($index=null){
		$arr = [
			self::TYPE_ALL =>'全部',
			self::LIVE => '活期',
			self::FIX => '定期'
		];
		return ($index === null)?$arr:$arr[$index];
	}

	/**交易名称**/
	const RECHARGE=3;
	public static function getTip($index=null){
		$arr = [
			self::LIVE => '活期理财',
			self::FIX => '定期理财',
			self::RECHARGE=>'零钱充值'
		];
		return ($index === null)?$arr:$arr[$index];
	}

	/**收益方式: (1)到期还本付息(2)按月等额付息，到期还本
				(3)按日等额付息，到期还本(4)按月等额本息
				(5)按日等额本息(6)按月等额还本，到期付息
				(7)按日等额还本，到期付息**/
	const DEBT_MATUTITY = 1;
	const MONTHLY_MATCHING_SERVICE_MATURITY = 2;
	//const BY_MATCHING_INTEREST_DEBT_MATURITY = 3;
	const MONTHLY_INSTALLMENTS_PRINCIPAL_INTEREST = 4;
	//const DAILY_MATCHING_SERVICE = 5;
	//const MONTHLY_MATCHING_PRINCIPAL_MATURITY = 6;
	//const BY_MATCHING_DEBT_MATURITY = 7;
	public static function getProfitType($index=null){
		$arr = [
			self::ALL =>'全部',
			self::DEBT_MATUTITY => '到期还本付息',
			 self::MONTHLY_MATCHING_SERVICE_MATURITY => '按月等额付息，到期还本',
			//self::BY_MATCHING_INTEREST_DEBT_MATURITY => '按日等额付息，到期还本',
			//self::MONTHLY_INSTALLMENTS_PRINCIPAL_INTEREST => '按月等额本息',
			//self::DAILY_MATCHING_SERVICE => '按日等额本息',
			//self::MONTHLY_MATCHING_PRINCIPAL_MATURITY => '按月等额还本，到期付息',
			//self::BY_MATCHING_DEBT_MATURITY => '按日等额还本，到期付息'
		];
		return ($index === null) ? $arr : $arr[$index];
	}

	/**起息日: 10、投资日, 11、投资日+1, 20、满标日, 21、满标日+1,**/
	const INVEST = 10;
	const INVEST_ONE = 11;
	const FULL_SCALE = 20;
	const FULL_SCALE_ONE = 21; 
	public static function getProfitDay($index=null) {
		$arr = [
			self::ALL =>'全部',
			/*self::INVEST => '投资日起息',
			self::INVEST_ONE => '投资日+1起息',*/
			self::FULL_SCALE => '满标日起息',
			self::FULL_SCALE_ONE => '满标日+1起息'
		];
		return ($index === null) ? $arr : $arr[$index];
	}

	/**
	 * 获取结束时间
	 * @author xiaomalover <xiaomalover@gmail.com>
	 * @param Int $profitDay 启息日
	 * @param Int $invest_day 投资期限
	 * @return String 结束时间字符串
	 */
	public static function getEndTime($profitDay, $invest_day)
	{
		$type_arr = [
			self::INVEST => '投资日+' . $invest_day,
			self::INVEST_ONE => '投资日+' . ($invest_day + 1),
			self::FULL_SCALE => '满标日+' . $invest_day,
			self::FULL_SCALE_ONE => '满标日+' . ($invest_day + 1),
		];
		return isset($type_arr[$profitDay]) ? $type_arr[$profitDay] : "";
	}
}
