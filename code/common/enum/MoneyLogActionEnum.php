<?php 
namespace common\enum;
class MoneyLogActionEnum{
	const GLJ= 1;
	const CZ= 2;
	const FR= 3;
	const TJJ= 5;
	const ZZ= 6;
	const TX = 7;
	const SY= 8;
    const TK= 9;
    const DH= 10;
    const ZR= 12;
    const TDJ= 13;
//1管理奖,2充值,3分润,5推荐奖,6转账,7提现,8收益,9退款,10兑换,12转入,13推代奖
//,13店铺押金,14财富计划购买失败返回及收益,16定期收益,17定期管理奖
	public static function getName($index=null){
		$arr= [
            ""=>"全部",
			self::GLJ => '管理奖',
			self::CZ => '充值',
			self::FR => '分润',
			self::TJJ => '推荐奖',
			self::ZZ => '转账',
			self::TX => '提现',
			self::SY => '收益',
			self::TK => '退款',
			self::DH => '兑换',
			self::ZR => '转入',
			self::TDJ => '推代奖',
		];
		return ($index === null)?$arr:$arr[$index];
	}
	
}