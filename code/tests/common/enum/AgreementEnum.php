<?
namespace common\enum;

/**
 * 控件类型枚举
 * @author Ben
 *
 */
class AgreementEnum
{

	const REGISTER = 1;
    const BANK = 2 ;
    const RECHARGE = 3;
    const TRANSFER = 4;
	public static function getType( $type=null )
    {
		$data=[
			self::REGISTER=>'register',
            self::BANK=>'bank',
            self::RECHARGE=>'recharge',
            self::TRANSFER=>'transfer',
		];
		return $type===null?$data:$data[$type];
	}

	public static function getName($type=null)
    {
		$data=[
            self::REGISTER=>'注册协议',
            self::BANK=>'银行卡服务协议',
            self::RECHARGE=>'充值服务协议',
            self::TRANSFER=>'用户转移协议',
		];
		return $type===null?$data:$data[$type];
	}

}