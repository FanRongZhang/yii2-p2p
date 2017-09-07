<?php 
namespace common\enum;
class LevelEnum{
	const NORMAL = 15;
	const VIP1 = 1;
	const VIP2 = 2;
	const VIP3 = 3;
	const VIP4 = 4;
	const VIP5 = 5;
	const VIP6 = 6;

	public static function getName($index=null){
		$arr= [
            ""=>"全部",
			self::NORMAL => '普通会员',
			self::VIP1 => 'VIP1',
			self::VIP2 => 'VIP2',
			self::VIP3 => 'VIP3',
			self::VIP4 => 'VIP4',
			self::VIP5 => 'VIP5',
			self::VIP6 => 'VIP6'
		];
		return ($index === null)?$arr:$arr[$index];
	}
	
}