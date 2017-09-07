<?php

namespace common\helpers;

use Yii;

class AdCommon 
{
    /**
     * @desc 生成加密TOKEN
     * @param type $key 标识码
     * @return type
     */
    public static function createToken($key)
    {
        return Yii::$app->encrypt->encode($key).'@@'.uniqid();
    }

    /**
     * @desc 解开用户Token
     * @param type $token
     * @return type
     */
    public static function undoToken($token)
    {
        $token_array = explode("@@", $token);
        $id = Yii::$app->encrypt->decode($token_array[0]);
        //return intval($id);
        return $id;
    }

    /**
	 * 自动补全算数方法
	 * @access public   
	 * @return void 
	 */
    public static function tail($num, $fen) {
	    $avg  = bcdiv($num, $fen, 2); //除
	    $tail = bcsub($num, $avg*($fen-1), 2); //减
	    return array($avg, $tail);
	}

	public static function cut_str($string, $sublen, $start = 0, $code = 'UTF-8') {
	    if($code == 'UTF-8') {
	        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
	        preg_match_all($pa, $string, $t_string);
	        if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen));
	        return join('', array_slice($t_string[0], $start, $sublen));
	    } else {
	        $start = $start*2;
	        $sublen = $sublen*2;
	        $strlen = strlen($string);
	        $tmpstr = '';
	        for($i=0; $i< $strlen; $i++) {
	            if($i>=$start && $i< ($start+$sublen)) {
	                if(ord(substr($string, $i, 1))>129) {
	                    $tmpstr.= substr($string, $i, 2);
	                } else {
	                    $tmpstr.= substr($string, $i, 1);
	                }
	            }
	            if(ord(substr($string, $i, 1))>129) $i++;
	        }
	        //if(strlen($tmpstr)< $strlen ) $tmpstr.= "...";
	        return $tmpstr;
	    }
	}

	/**
	 * 获取随机数方法
	 * @access public   
	 * @return void 
	 */
	public static function randomkeys($length = 8)
	{
		$key ="";
		$pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		for($i=0;$i<$length;$i++)
		{
		  $key .= $pattern{mt_rand(0,62)};    //生成php随机数
		}
		return $key;
	}
	
	/**
	 * @desc 生成唯一字符串
	 * @return string
	 */
	public static function uniqueGuid()
	{
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
		$uuid = substr($charid, 0, 8).substr($charid, 8, 4).substr($charid, 12, 4).substr($charid, 16, 4).substr($charid, 20, 12);
		return $uuid;
	}
	
	/**
	* @desc    订单号
	* @access  public
	* @param   void
	* @return  string
	*/
	public static function orders()
	{
		return date('YmdHis').rand(0000, 9999);
	}
	
	/**
	 * @desc 用户加密
	 * @param type $keys 字符串
	 * @return type
	 */
	public static function encryption($keys)
	{
		return md5($keys . ENCRYPTION_KEY);
	}
	
	/**
	 * @desc 非四舍五入取取小数点
	 * @param type $number
	 * @return type
	 */
	public static function floor_money_format($number, $num = 2)
	{
		$number = floor($number*100)/100;
		return number_format($number, $num, '.', '');
	}

	/**
	 * @desc 格式化金额
	 * @param type $number
	 * @param type $type
	 * @return type
	 */
	public static function formatMoney($number, $type = 4)
	{
		return number_format($number, $type, ".", "");
	}

	/**
	 * @desc 屏蔽电话号码中间四位
	 * @param int $phone 数据
	 * @return string
	 */
	public static function hidtel($phone){
	    $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i',$phone); //固定电话
	    if($IsWhat == 1){
	        return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i','$1****$2',$phone);
	    }else{
	        return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
	    }
	}

	/**
	 * @desc 返回错误信息
	 * @param type $array
	 * @return type
	 */
	public static function modelMessage($array)
	{
		
		if (count($array) <= 0)
		{
			return;
		}
		$msg = '';
		
		foreach ($array as $val)
		{
			$msg .= isset($val[0]) ? $val[0] : $val;
		}
		return $msg;
	}
	
	/**
	* @desc   检查输入的是否为数字
	* @access public
	* @param  $val 
	* @return bool true false
	*/
	public static function isNumber($val)
	{
		if(preg_match("/^[0-9]+$/", $val))
		{
			return true;
		}
		return false;
	}

	/**
	 * @desc 获取时间错
	 */
	public static function get_microtime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec+(float) $sec);
	}
	
	/**
	* @desc    更新当前在线人数
	* @access  public
	* @param   int $sid 商家ID
	* @param   int $uid 登录ID
	* @param   int $type 类型  0退出   1登录
	* @return  总在线人数
	*/
	public static function onlineFile($sid = null,$uid = null, $type = 1)
	{
		$reids   = new RedisComponent();
	
		if (is_null($sid)) $sid = 0;
		if (is_null($uid)) $uid = 0;
		$onlinetime = Yii::app()->params['online']['time'];
		$key        = Yii::app()->params['online']['name'];
		$keyId      = Yii::app()->params['online']['prefix'].$sid;
		  
		@$online = $reids->hget($key, $keyId);	
		$online = explode('&', $online);
		
		$nowtime = time(); 
		$nowonline = array();
		$writeOnline = '';
		
		if(!empty($online))
		{
			foreach($online as $line) 
			{				
				if (trim($line) != '' )
				{
					$row = explode('|',$line);
					$sesstime = trim($row[1]);
					if(($nowtime - $sesstime) <= $onlinetime)   
					{ 
						if ($type == 0 && $uid == $row[0])
						{
							#退出
						}
						else
						{
							$nowonline[$row[0]] = $sesstime;    
							$writeOnline .= $row[0] . '|' . $row[1] . '&';
						}	 
					}
				}
			}
		}
		
		#登录
		if ($uid >0 && $sid > 0 && $type == 1)
		{
			$writeOnline .= $uid . '|' . $nowtime . '&'; 
		}
		
		$reids->hset($key, $keyId, $writeOnline);
		
		$total_online = count($nowonline);
		
		return $total_online;
	}
	
	
	/**
	* @desc   检查输入的是否为手机号
	* @access public
	* @param  $val 
	* @return bool true false
	*/
	public static function isMobile($val)
	{
		
		//该表达式可以验证那些不小心把连接符“-”写出“－”的或者下划线“_”的等等
		if(preg_match("/^13[0-9]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/",$val))
		{
			return true;
		}
		return false;
	}

	/**
	* @desc   检查输入的是否为邮编
	* @access public
	* @param  $val 
	* @return bool true false
	*/
	public static function isPostcode($val)
	{
		if(preg_match("/^[0-9]{4,6}$/",$val))
		{
			return true;
		}
		return false;
	}
	
	/**
	* @desc   邮箱地址合法性检查
	* @access public
	* @param  $val 
	* @param  $domain
	* @return bool true false
	*/
	public static function isEmail($val,$domain="")
	{
		if(!$domain)
		{
			if( preg_match("/^[a-z0-9-_.]+@[\da-z][\.\w-]+\.[a-z]{2,4}$/i", $val) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if( preg_match("/^[a-z0-9-_.]+@".$domain."$/i", $val) )
			{
				return true;
			}
			else
			{
				return false;
			}
				
		}
	}
	
	/**
	 * 验证身份证号
	 * @param $vStr
	 * @return bool
	 */
	public static function isCreditNo($vStr)
	{
	    $vCity = array(
	        '11','12','13','14','15','21','22',
	        '23','31','32','33','34','35','36',
	        '37','41','42','43','44','45','46',
	        '50','51','52','53','54','61','62',
	        '63','64','65','71','81','82','91'
	    );
	
	    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr))
		{
			return false;
		}
	
	    if (!in_array(substr($vStr, 0, 2), $vCity))
		{
			return false;
		}
	
	    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
	    $vLength = strlen($vStr);
	
	    if ($vLength == 18)
	    {
	        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
	    } else {
	        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
	    }
	
	    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday)
		{
			return false;
		}
	    if ($vLength == 18)
	    {
	        $vSum = 0;
	
	        for ($i = 17 ; $i >= 0 ; $i--)
	        {
	            $vSubStr = substr($vStr, 17 - $i, 1);
	            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
	        }
	
	        if($vSum % 11 != 1)
			{
				return false;
			}
	    }
	
	    return true;
	}
	
	/**
	* @desc   姓名昵称合法性检查，只能输入中文英文
	* @access public
	* @param  $val 被检查内容
	* @return bool true false
	*/
	public static function isName($val)
	{
		if(preg_match("/^[\x80-\xffa-zA-Z0-9]{3,60}$/", $val) )
		{
			return true;
		}
		return false;
	}
	
	
	/**
	* @desc   检查字符串长度是否符合要求(仅限于数字)
	* @access public
	* @param  int $val 
	* @param  int $min 最小长度
	* @param  int $max 最大长度
	* @return bool true false
	*/
	public static function isNumLength($val, $min, $max)
	{
		$theelement= trim($val);
		if(preg_match("/^[0-9]{".$min.",".$max."}$/",$val))
		{
			return true;
		}
		return false;
	}
	
	/**
	* @desc   检查字符串长度是否符合要求(仅限于阿拉伯字母)
	* @access public
	* @param  string $val 
	* @param  int $min 最小长度
	* @param  int $max 最大长度
	* @return bool true false
	*/
	public static function isEngLength($val, $min, $max)
	{
		$theelement= trim($val);
		if(preg_match("/^[a-zA-Z]{".$min.",".$max."}$/",$val))
		{
			return true;
		}
		return false;
	}
	
	/**
	* @desc   检查输入是否为英文
	* @access public
	* @param  string $theelement 
	* @return bool true false
	*/
	public static function isEnglish($theelement)
	{
		if(preg_match("/^[a-zA-Z\s]+$/",$theelement))
		{
			return true;
		}
		return false;
	}
	
	/**
	* @desc   检查是否输入为汉字
	* @access public
	* @param  string $sInBuf 
	* @return bool true false
	*/
	public static function isChinese($val)
	{
		if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $val) )
		{
			return true;
		}
		return false;
	}
	
	/**
	* @desc   检查输入值是否为合法人民币格式
	* @access public
	* @param  float $val  
	* @return bool true false
	*/
	public static function isMoney($val)
	{
		if(preg_match("/^(-?\d+)(\.\d+)?/", $val))
		{
			return true;
		}
		if( preg_match("/^[0-9]{1,}\.[0-9]{1,4}$/", $val) )
		{
			return true;
		}
		return false;
	}
	
	
	/**
	* @desc   检查输入IP是否符合要求
	* @access public
	* @param  string $val  
	* @return bool true false
	*/
	public static function isIp($val)
	{
		return(bool) ip2long($val);
	}
	
	/**
	* @desc   如果元素值中包含除字母和数字以外的其他字符，则返回FALSE
	* @access public
	* @param  string $str 被验证内容
	* @return bool
	* @author Alisa
	*/
	public static function isAlpha_numeric($str)
	{
		return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
	}
	
	/**
	* @desc   如果元素值中包含除字母/数字/下划线/破折号以外的其他字符，则返回FALSE
	* @access public
	* @param  string $str
	* @return bool true false
	*/
	public static function alpha_dash($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
	}

	/**
    * @desc   汇率兑换
    * @access public
    * @param  int $type 1人民币兑换美元    2美元兑换人民币
    * @param  float $money 金额
    * @param  float $rate   汇率
    * @return float
    */
    public static  function changeRates($type = 1, $money, $rate)
    {
    	if ($rate == 0)
    	{
    		return $money;
    	}
    	
    	if ($type == 1)
    	{
    		return self::format_money($money/$rate, 4);
    	}
    	else if ($type == 2)
    	{
    		return self::format_money($money*$rate, 4);
    	}
    	else
    	{
    		return 0;
    	}
    }


    /**
    *   功能：中英文混合字符串截取(指定开始位置,截取长度,一个汉字相对于一个字符)
    *   参数：传入一个字符串
    *   返回值: 字符串
    */
    public static function cc_msubstr($str, $length, $start=0, $charset="utf-8", $suffix=true){
        if(function_exists("mb_substr")){
            return mb_substr($str, $start, $length, $charset);
        }elseif(function_exists('iconv_substr')){
            return iconv_substr($str,$start,$length,$charset);
        }
        $re['utf-8']  = "/[/x01-/x7f]|[/xc2-/xdf][/x80-/xbf]|[/xe0-/xef][/x80-/xbf]{2}|[/xf0-/xff][/x80-/xbf]{3}/";
        $re['gb2312'] = "/[/x01-/x7f]|[/xb0-/xf7][/xa0-/xfe]/";
        $re['gbk']    = "/[/x01-/x7f]|[/x81-/xfe][/x40-/xfe]/";
        $re['big5']   = "/[/x01-/x7f]|[/x81-/xfe]([/x40-/x7e]|/xa1-/xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
        if($suffix){
            return $slice."..";
        }else{
            return $slice;
        }
   }

	
	/**
	 * @desc 非四舍五入取取小数点
	 * @param type $number
	 * @return type
	 */
	public static function format_money($number, $num = 2)
	{
	    $number = floor($number * 100) / 100;
	    return number_format($number, $num, '.', '');
	}
	
	/**
	 * 输出银行卡
	 * @param type $card_code
	 * @return type
	 */
	public static function getCardCode($card_code = '')
	{
		if (strlen($card_code) <= 4)
		{
			return $card_code;
		}
		$max = (strlen($card_code)-4);
		$string = '';
		for ($i = 0; $i < $max; $i ++)
		{
			$string .= "*";
		}
		return $string.substr($card_code, -4, 4);
	}

	public static function dotran($str)
	{
        return urlencode($str);
    }

    public static function dedotran($str)
	{
        return urldecode($str);
    }
    function getDistance($lat1, $lng1, $lat2, $lng2)
    {
    	$earthRadius = 6367000; //approximate radius of earth in meters
    		
    	/*
    	 Convert these degrees to radians
    	to work with the formula
    	*/
    		
    	$lat1 = ($lat1 * pi() ) / 180;
    	$lng1 = ($lng1 * pi() ) / 180;
    		
    	$lat2 = ($lat2 * pi() ) / 180;
    	$lng2 = ($lng2 * pi() ) / 180;
    		
    	/*
    	 Using the
    	Haversine formula
    
    	http://en.wikipedia.org/wiki/Haversine_formula
    
    	calculate the distance
    	*/
    		
    	$calcLongitude = $lng2 - $lng1;
    	$calcLatitude = $lat2 - $lat1;
    	$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    	$stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    	$calculatedDistance = $earthRadius * $stepTwo;
    		
    	return round($calculatedDistance);
    }
    /**
     * 用私钥解析数据
     * @param unknown $encrypted
     * @param unknown $private_key
     * @return string
     */
    public static function dewoshou($encrypted){
    	$private_key = '-----BEGIN RSA PRIVATE KEY-----
				MIICXQIBAAKBgQDtFrCGY7R7sKMwvMK1Fb1K/lz/37SV9QWZKw6tIm5OCUABZinw
				T+JWSdCU+I01SH+KdQgY0IQUlyv2oTtS15SFGcBs1Uby9YwNBi9/US9z2THyaSWA
				dbQFSRdV4XI8Qgye1s4nx9Vg2g9Tjp9dS55ou+CNCcW/EK9Gh0vWdu+ApwIDAQAB
				AoGAJNacsRT26y0j/iOmQUrScb+aJavVvGMo7oaxLhemefuX9V+xboSLD4tCnJMO
				JdRQ9OuASZLEowpmK1kcBaA3lDY5Yo1ZtTbideS6bHL/UoCtFOWvSuEQL1Hwf/4G
				n2tnYb769wtoRotfBgicEVVImSpB+/N/8H6EUMjvR6uyr8kCQQD8vsG487044FPM
				zf3zrU2SXQCImo8E4QGIFZJE5nYuSBRY9XrpaO72g8FebiDNTJDHfidSydynWJkU
				bzshyh8dAkEA8CRRHcbQdVthwe7c3TMsImOszGagey4plh9n40Y5uGOY/Px/wiiE
				mhJvx+jI0NSpGO8gHYHhZPgRf9UTH/2/kwJBAIRgEKyjFGc2rw1kkm7PRQK0rTPe
				56thgeDZk3t8zUceP3H8WHzplccNaPjha1K7mFS0ETp+OZB4iey2+VyQNU0CQBgy
				0Km7ew4YY0VzmHYBzhS5DpSaUtmW0UH7cDCKxw45mxUDLKyYAKS17uWqI3JHu7Jz
				hjzy9Y+DH+BNzFuQ6lkCQQCTgl8kbmukuyLNvm4gU10FYMWR/wMy4BRATDetgYO2
				QZfu0GQuABTI7yZ2FUmZIkaAnyg6S7e9qPJKmzG6FGEy
				-----END RSA PRIVATE KEY-----';
    	$decrypted = "";
    	$pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
    	openssl_private_decrypt(base64_decode($encrypted),$decrypted,$pi_key);//私钥解密
    	return  $decrypted;
    }
    /**
     * 用私钥加密数据
     * @param unknown $data
     * @param unknown $private_key
     * @return string
     */
    public static function enbwoshou($data){
    	$private_key = '-----BEGIN RSA PRIVATE KEY-----
				MIICXQIBAAKBgQDtFrCGY7R7sKMwvMK1Fb1K/lz/37SV9QWZKw6tIm5OCUABZinw
				T+JWSdCU+I01SH+KdQgY0IQUlyv2oTtS15SFGcBs1Uby9YwNBi9/US9z2THyaSWA
				dbQFSRdV4XI8Qgye1s4nx9Vg2g9Tjp9dS55ou+CNCcW/EK9Gh0vWdu+ApwIDAQAB
				AoGAJNacsRT26y0j/iOmQUrScb+aJavVvGMo7oaxLhemefuX9V+xboSLD4tCnJMO
				JdRQ9OuASZLEowpmK1kcBaA3lDY5Yo1ZtTbideS6bHL/UoCtFOWvSuEQL1Hwf/4G
				n2tnYb769wtoRotfBgicEVVImSpB+/N/8H6EUMjvR6uyr8kCQQD8vsG487044FPM
				zf3zrU2SXQCImo8E4QGIFZJE5nYuSBRY9XrpaO72g8FebiDNTJDHfidSydynWJkU
				bzshyh8dAkEA8CRRHcbQdVthwe7c3TMsImOszGagey4plh9n40Y5uGOY/Px/wiiE
				mhJvx+jI0NSpGO8gHYHhZPgRf9UTH/2/kwJBAIRgEKyjFGc2rw1kkm7PRQK0rTPe
				56thgeDZk3t8zUceP3H8WHzplccNaPjha1K7mFS0ETp+OZB4iey2+VyQNU0CQBgy
				0Km7ew4YY0VzmHYBzhS5DpSaUtmW0UH7cDCKxw45mxUDLKyYAKS17uWqI3JHu7Jz
				hjzy9Y+DH+BNzFuQ6lkCQQCTgl8kbmukuyLNvm4gU10FYMWR/wMy4BRATDetgYO2
				QZfu0GQuABTI7yZ2FUmZIkaAnyg6S7e9qPJKmzG6FGEy
				-----END RSA PRIVATE KEY-----';
    	$encrypted = "";
    	$pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
    	openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密
		$encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
    	return  $encrypted;
    }
    public static function get_rand($proArr) {
    	$result = '';
    	//概率数组的总概率精度
    	$proSum = array_sum($proArr);

    	//概率数组循环
    	foreach ($proArr as $key => $proCur) {
    		$randNum = mt_rand(1, $proSum);
    		if ($randNum <= $proCur) {
    			$result = $key;
    			break;
    		} else {
    			$proSum -= $proCur;
    		}
    	}
    	unset ($proArr);
    	return $result;
    }


    public function checkLogin($login_user,$session_save_path=NULL)
	{
		if (empty($session_save_path)) {
			$session_save_path = ini_get("session.save_path");   
		}
		$sessionID = session_id();   //存取目前SESSION ID号
		$sessionFile = "sess_".$sessionID;
		//echo $session_save_path; 
		//打开目录，将sessions目录下的session文件放入数组
		$fp = @opendir($session_save_path);
		while ($file = @readdir($fp)) {
			if($file!='.' && $file!='..') {
				$file = "$file";
				$arr_file[] = $file; 
			}
		}
		if (is_array($arr_file)) {
			while(list($key,$value) = each($arr_file)) {
				$path = $session_save_path.'/'.$value;
				// echo $path;die;
				$fpp = @fopen($path, "r");
				if ($fpp = @fopen($path, "r")) {
					if(filesize($path) > 0) {
					$sess_data = fread($fpp, filesize($path));
					session_decode($sess_data);
						var_dump($_SESSION);die;
						if($_SESSION['LOGIN']){
							$login_id = $_SESSION['LOGIN']["id"];
							var_dump($login_id);die;
							//判断此用户是否已存在session 文件&& $login_pass == $pass
							if ($login_user == $login_id) {
								//echo "在线";
								echo $path;
								@unlink($path);//删除session文件
							}
						}
						
					}
				}
			}
		}
		return true;
	}


	/**
     * 人民币金额小写转换为大写
     */
    public static function get_amount($num){  
        $c1 = "零壹贰叁肆伍陆柒捌玖";  
        $c2 = "分角元拾佰仟万拾佰仟亿";  
        $num = round($num, 2);  
        $num = $num * 100;  
        if (strlen($num) > 10) {  
            return "数据太长，没有这么大的钱吧，检查下";  
        }   
        $i = 0;  
        $c = "";  
        while (1) {  
            if ($i == 0) {  
                $n = substr($num, strlen($num)-1, 1);  
            } else {  
                $n = $num % 10;  
            }   
            $p1 = substr($c1, 3 * $n, 3);  
            $p2 = substr($c2, 3 * $i, 3);  
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {  
                $c = $p1 . $p2 . $c;  
            } else {  
                $c = $p1 . $c;  
            }   
            $i = $i + 1;  
            $num = $num / 10;  
            $num = (int)$num;  
            if ($num == 0) {  
                break;  
            }   
        }  
        $j = 0;  
        $slen = strlen($c);  
        while ($j < $slen) {  
            $m = substr($c, $j, 6);  
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {  
                $left = substr($c, 0, $j);  
                $right = substr($c, $j + 3);  
                $c = $left . $right;  
                $j = $j-3;  
                $slen = $slen-3;  
            }   
            $j = $j + 3;  
        }   
  
        if (substr($c, strlen($c)-3, 3) == '零') {  
            $c = substr($c, 0, strlen($c)-3);  
        }  
        if (empty($c)) {  
            return "零元整";  
        }else{  
            return $c . "整";  
        }  
    }

}