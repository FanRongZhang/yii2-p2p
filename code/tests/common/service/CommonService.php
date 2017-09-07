<?php
/**
 * 通用服务类
 * @author xiaoma
 */

namespace common\service;

use yii;
class CommonService {
    
    /**
     * 定义短信类型常量
     */
    const VERIFY_TYPE_PWD =     1; //设置密码的验证码
    const VERIFY_TYPE_PAYPWD =  2; //设置支付密码的验证码
    const VERIFY_TYPE_CASH=     3;//申请提现的验证码
    const VERIFY_TYPE_POINT=    4;//申请积分的验证码
    const VERIFY_TYPE_EGD=      5;//兑换积分的验证
    const VERIFY_TYPE_REG=      6;//用户注册的验证
    const VERIFY_TYPE_BANK=     7;//用户添加银行卡
    const VERIFY_TYPE_FORGET=   8;//用户忘记密码
    const VERIFY_TYPE_LOAN=     9;//用户贷款
    const VERIFY_TYPE_ASSURANCE=10;//用户担保

    const WITHDRAW=11; //提现的用户提醒

    const VERIFY_TYPE_FORGET_ZF = 13; //用户忘记支付密码

    const VERIFY_TYPE_UPDATE_BANKCARD = 14; //修改银行卡
	const VERIFY_TYPE_UPDATE_TELEPHONE=15; //卡


    public static function getType($type=null)
    {
        $data=[
            self::VERIFY_TYPE_PWD=>'pwd_verify',
            self::VERIFY_TYPE_PAYPWD=>'paypwd_verify',
            self::VERIFY_TYPE_CASH=>'cash_verify',
            self::VERIFY_TYPE_POINT=>'point_verify',
            self::VERIFY_TYPE_EGD=>'egd_verify',
            self::VERIFY_TYPE_REG=>'reg_verify',
            self::VERIFY_TYPE_BANK=>'bank_verify',
            self::VERIFY_TYPE_FORGET=>'forget_verify',
            self::VERIFY_TYPE_LOAN=>'loan_verify',
            self::VERIFY_TYPE_ASSURANCE=>'assurance_verify',
            self::VERIFY_TYPE_FORGET_ZF=>'forget_zf',
            self::VERIFY_TYPE_UPDATE_BANKCARD=>'update_bankcard',
			self::VERIFY_TYPE_UPDATE_TELEPHONE=>'update_telephone',
        ];

        return $type===null?$data:$data[$type];
    }

    /**
    *提现发送手机短信
    */
    public static function sendMobileMsg($mobile,$data,$debug = false)
    {
        if ($mobile==false) return false;
        $params = yii::$app->params;

        $money=$data['price'];
        $bankcard=substr($data['no'],-4);

        if ($data['out_type'] ==2)
        { //1-3个工作日到账
            $string="预计1-3个工作日内到账，";
        }
        elseif ($data['out_type']==1)
        { //工作日当天到账
            $string="预计当天内到账，";
        }

        //手续费
        if ($data['fee']>0)
        {
            $fee = "手续费{$data['fee']}元，";
        }

        $content="【{$params['sms_name']}】您申请由零钱提现{$money}元至尾号为{$bankcard}的{$data['name']}卡已成功受理，{$fee}{$string}请留意您的银行卡资金变化";


        //调试模式，直接返回验证码，非调试模式发送短信，返回发送结果布尔值
        if ($debug)
        {
            return $content;
        }
        else
        {
            $flag = 1;      //$flag==1表示用三三得九的短信接口发送验证码
            if ($flag==1)
            {
                $start = stripos($content, '】');
                $content = substr($content, $start+3);
                $isTrue = self::sendNewSms($mobile, $content);
            }
            else
            {
                $isTrue = self::sendSms($mobile, $content);
            }
            return $isTrue>0 ? true : false;
        }
    }



    /**
     * 发送手机验证码 
     * @param string $mobile 接收手机号 (必填)
     * @param integer $type 验证码类型 (必填)
     * @param boolean $debug 调试模式,默认为false （选填）
     * @return 非调试模式 boolean true为发送成功, false为发送失败; 调试模式, integer [验证码]
     */
    public static function sendMobileVerifyCode($mobile, $type, $debug = false) {
//        file_put_contents("/home/wwwroot/dmall.dm188.cn/api/runtime/logs/aa.log","111111");
        if ($mobile==false) return false;
        //生成随机手机验证码
        $authnum = rand(1000, 9999);
        // $cache = Yii::$app->cache;

        yii::$app->session[self::getType($type).$mobile]=$authnum;
        $params = yii::$app->params;
        $content = "";
//        file_put_contents("/home/wwwroot/dmall.dm188.cn/api/runtime/logs/aa.log",$authnum);
        //根据不同类型，设置不同的短信模板
        switch ($type) {
            case self::VERIFY_TYPE_PWD:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'修改密码的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_PAYPWD:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'设置支付密码的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_CASH:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'申请提现的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_POINT:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'申请积分提现的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_EGD:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'申请积分兑换EGD的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_REG:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'注册的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_BANK:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'添加银行卡的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_FORGET:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'忘记密码的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_LOAN:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'小额贷的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_ASSURANCE:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'担保的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_FORGET_ZF:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'忘记支付密码的验证码为' . $authnum ; //短信内容
                break;
            case self::VERIFY_TYPE_UPDATE_BANKCARD:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'修改银行卡的验证码为' . $authnum ; //短信内容
                break;
			case self::VERIFY_TYPE_UPDATE_TELEPHONE:
                $content = '【'.$params['sms_name'].'】'.'尊敬的用户您好！您在'.$params['sms_content'].'  更换设备是校验的验证码为' . $authnum.'测试->'.session_id(); //短信内容
                break;
				
        }

        //调试模式，直接返回验证码，非调试模式发送短信，返回发送结果布尔值
        if ($content=="") return false;
        if ($debug)
        {
            return $authnum;
        }
        else
        {
            $flag = 1;      //$flag==1表示用三三得九的短信接口发送验证码
            if ($flag==1)
            {
                $start = stripos($content, '】');
                $content = substr($content, $start+3);
                $isTrue = self::sendNewSms($mobile, $content);
                return $isTrue;
            }
            else
            {
                $isTrue = self::sendSms($mobile, $content);
            }
            return $isTrue>0 ? true : false;
        }
    }


    /**
     * 发送手机短信  君诚短信通道
     * @param string $mobile 接收手机号
     * @param string $content 发送的内容
     */
    private static function sendSms($mobile, $content) {
        //开始发送
        $username = Yii::$app->params['sms_sn'];
        $pwd = Yii::$app->params['sms_pwd'];

        $password = md5($username."".md5($pwd));
        $dstime = date('Y-m-d H:i:s',time());
        $url = Yii::$app->params['sms_url'];

        $param = http_build_query(
            array(
                'username'=>$username,
                'password'=>$password,
                'mobile'=>$mobile,
                'content'=>$content,
                'dstime'=>''
            )
        );

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$param);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    
    /**
     * 三三得九
     * @param string $mobile 接收手机号
     * @param string $content 发送的内容
     */
    private static function sendNewSms($mobile, $content)
    {
        $username = '69451:admin';
        $password = '27743708@dmjt$%';
        $from = '000';
        $to = $mobile;
        $text = $content;
        $presendTime = '';
        $isVoice = '0|0|0|0';
    	require_once("lib/nusoap.php");
    	$client = new \nusoap_client('http://ws.iems.net.cn/GeneralSMS/ws/SmsInterface?wsdl',true);
    	$client->soap_defencoding = 'utf-8';   
    	$client->decode_utf8 = false;   
    	$client->xml_encoding = 'utf-8'; 


    	$parameters	= array($username,$password,$from,$to,$text,$presendTime,$isVoice);
    	$str=$client->call('clusterSend',$parameters);
    	$i = stripos($str, 'code');
    	$str = substr($str,$i,9);
    	$str = substr($str,-4);
    	if ($str=='1000')
        {
    	    return true;
    	}
        else
        {
    	    return false;
    	}
    }

    /**
     * 外部调用发短信
     */
    public static function pubSendNewSms($mobile, $content)
    {
        return self::sendNewSms($mobile,$content);
    }

    /**
     * 验证手机验证码
     * @param integer $verify 验证码
     * @param string  $mobile
     * @param integer $type 验证码类型 (必填)
     * @return boolean true为验证码正确, false为验证码错误
     */
    public static function checkVerify($verify, $mobile, $type)
    {
//           file_put_contents('/home/wwwroot/dmall2.dm188.cn/api/runtime/logs/aaa.log',
//              json_encode(['code'=>$verify,'session'=>Yii::$app->session[self::getType($type) . $mobile] , 'type'=>$type,'mobile'=>$mobile,'session_id'=>session_id(),'type_name'=>self::getType($type)]));
       
        if ($mobile==false) return false;
        //根据验证码验证类型，获取session中存储的验证码的值

        // $mobile_check = Yii::$app->cache->get(self::getType($type).$mobile);
         $mobile_check = Yii::$app->session[self::getType($type) . $mobile];

        //判断用户输入的验证码和session中的验证码是否一致,返回验证结果
        if ($verify == $mobile_check && $mobile_check != null)
        {
            Yii::$app->session[self::getType($type) . $mobile]=null;
            return true;
        }
        else
        {
            return false;
        }
    }
    public static function put_file_from_url_content($url,$savefile)
    {
        // 设置运行时间为无限制
        set_time_limit(0);

        $curl = curl_init($url);
        // 设置你需要抓取的URL
        curl_setopt($curl, CURLOPT_NOBODY, 0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
        // 设置header
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 运行cURL，请求网页
        $file = curl_exec($curl);
        // 关闭URL请求
        curl_close($curl);
        //$filename=$savefile."qrcode.jpg";
        $handle = fopen($savefile,'w');
        if (false !== fwrite($handle,$file))
        {
                fclose($handle);
        }
    }
    public static function smallImg($file, $w, $h, $path) {
        //1,得到原图的长和高
        list($width, $height) = getimagesize($file);
        $_width = $w;
        $_height = $h;
        //创建一个新图
        $im = imagecreatetruecolor($_width, $_height);
        //载入原图
        $image = imagecreatefromjpeg($file);
        //将原图采样，拷贝到新图上
        imagecopyresampled($im, $image, 0, 0, 0, 0, $_width, $_height, $width, $height);
        //将新图输出
        imagejpeg($im, $path, 100);
        //清除所有资源
        imagedestroy($im);
        imagedestroy($image);
    }
    public static function copyImg($img1, $name, $x, $y, $path, $back) {
        $imgs = array();
        $imgs[0] = $img1;
        $target = $back; //背景图片
        $target_img = Imagecreatefromjpeg($target);
        $source = array();
        foreach ($imgs as $k => $v) {
            $source[$k]['source'] = Imagecreatefromjpeg($v);
            $source[$k]['size'] = getimagesize($v);
        }
        $num1 = 0;
        $num = 3; //控制列数，一行几列，0为1以此类推。
        $tmp = $x; //左间距
        $tmpy = $y; //上间距
        for ($i = 0; $i < 1; $i++) {
            imagecopy($target_img, $source[$i]['source'], $tmp, $tmpy, 0, 0, $source[$i]['size'][0], $source[$i]['size'][1]);
            $tmp = $tmp + $source[$i]['size'][0];
            $tmp = $tmp + 5;
            if ($i == $num) {
                $tmpy = $tmpy + $source[$i]['size'][1];
                $tmpy = $tmpy + 5;
                $tmp = 2;
                $num = $num + 3;
            }
        }
        Imagejpeg($target_img, $path . $name);
    }
    /**
     * 将图片保存为jpg
     */
    public static  function saveImageToJpg($sourceFile,$saveFile)
    {
        $imginfo= getimagesize($sourceFile);
        $type=strtolower($imginfo['mime']);
        if ($imginfo && count($imginfo)>0)
        {
            switch($type)
            {
                case strstr($type,"png")!='':
                    $img = imagecreatefrompng($sourceFile);
                    imagejpeg($img,$saveFile);
                    imagedestroy($img);
                    break;
                case strstr($type,"bmp")!='':
                    $img=  imagecreatefromwbmp($sourceFile);
                    imagejpeg($img,$saveFile);
                    imagedestroy($img);
                    break;
                case strstr($type,"gif")!='':
                    $img = imagecreatefromgif($sourceFile);
                    imagejpeg($img,$saveFile);
                    imagedestroy($img);
                    break;
                default:
                    //保存原图片
                    rename($sourceFile, $saveFile);
                    break;

            }
        }else
            throw new Exception("无法识别图片");
    }

}
