<?php
/**
 * Created by zendstudio.
 * User: lijunwei
 * Date: 2017/05/25 1612
 * Time: 上午 9:55
 */

namespace frontend\controllers;

use common\extension\curl\Curl;
use common\models\QfbMember;
use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use yii\data\Pagination;
use common\service\CommonService;
use common\service\MongoService\UserBehaviorService as UserMongoService;

class WebController extends Controller
{
    /**
     * 提交查询验证
     * @var boolean
     */
    public $enableCsrfValidation = false;
    public $isPost = false;
    public $_data;
	public $_setting = array();
	public $memberData = [];
	public $mid = 0;
	public $proxy_code = '';
	public $member_type;

	// protected $actions = ['*'];
 //    protected $except = [];
 //    protected $mustlogin = [];
 //    protected $verbs = [];

 //    public function behaviors()
 //    {
 //        return [
 //            'access' => [
 //                'class' => \yii\filters\AccessControl::className(),
 //                'only' => $this->actions,
 //                'except' => $this->except,
 //                'rules' => [
 //                    [
 //                        'allow' => false,
 //                        'actions' => empty($this->mustlogin) ? [] : $this->mustlogin,
 //                        'roles' => ['?'], // guest
 //                    ],
 //                    [
 //                        'allow' => true,
 //                        'actions' => empty($this->mustlogin) ? [] : $this->mustlogin,
 //                        'roles' => ['@'],
 //                    ],
 //                ],
 //            ],
 //            'verbs' => [
 //                'class' => \yii\filters\VerbFilter::className(),
 //                'actions' => $this->verbs,
 //            ],
 //        ];
 //    }


	public function init()
    {
        parent::init();
		$this->memberData = \Yii::$app->session->get('LOGIN');

		/****************检验账号是否在其他地方登录******************/
		if(!empty($this->memberData)){
			$userMongoService = new UserMongoService($this->memberData['id']);
			$data = $userMongoService->findOne([], ['user_token']);

			if($data['user_token'] !== $this->memberData['user_token']){
				return $this->redirect('/login/login/logout');
			}
		}

		$this->_data['mid'] = $this->mid = !empty($this->memberData) ? $this->memberData['id'] : 0;

		$this->_data['memberLogin'] = $this->memberData;

		if($member = QfbMember::findOne($this->mid))
			$this->member_type = $member->member_type;
    }


    /**
     * ---------------------------------------
     * 标记当前位置到cookie供后续跳转调用
     * ---------------------------------------
     */
    public function setForward(){
        \Yii::$app->getSession()->setFlash('__forward__', $_SERVER['REQUEST_URI']);
    }


    /**
     * ---------------------------------------
     * 获取之前标记的cookie位置
     * @param string $default
     * @return mixed
     * ---------------------------------------
     */
    public function getForward($default=''){
        $default = $default ? $default : Url::toRoute([Yii::$app->controller->id.'/index']);
        if( Yii::$app->getSession()->hasFlash('__forward__') ) {
            return Yii::$app->getSession()->getFlash('__forward__');
        } else {
            return $default;
        }
    }


    /**
     * [page 分页]
     * @param  [int] $count [总条数]
     * @return [type]        [description]
     */
    public function page($count,$pageSize)
    {
    	return $pagination = new Pagination([
    			'pageSize'   => $pageSize,
    			'totalCount' => $count,
    			'pageSizeParam'=>false
    			]);
    }
    
    /**
     * [query 查询所有记录]
     * @param  [type] $model [Model]
     * @param  string $with  [联表]
     * @param  array $where [条件]
     * @param  string $order [排序]
     * @return [type]        [description]
     */
    public function query($model, $with = '', $where = [], $order = 'id desc',$pageSize=10)
    {
    	$query = $model->find();
    	if ($with != '') $query = $query->innerJoinWith($with);
    	if ($where != '') $query  =  $query->where($where);
    
    	$count = $query->count();
    	$page  = $this->page($count,$pageSize);
    	$query  = $query->orderBy($order)->offset($page->offset)->limit($page->limit)->all();
    
    	return ['count' => $count, 'page' => $page, 'data' => $query,'pageSize'=>$pageSize];
    }


    /**
     * [view 渲染视图]
     * @param  string $view [视图名称]
     * @return [type]       [nul]
     */
    public function view($view = '')
    {
    	if ($view == '')
    	{
    		$view = $this->action->id;
    	}
    	return $this->render($view, $this->_data);
    }


	protected function getThisUrl($action)
	{
		if (!empty($this->memberData))
		{
			$_GET['c'] = $this->memberData['proxy_code'];
		}
		return Yii::$app->request->getHostInfo().Url::toRoute(array_merge([$action], $_GET));
	}
	
	
	/**
	 * 获取客户端IP地址
	 * @return array
	 */
	public function clientIp() {
		header("Content-type: text/html; charset=gb2312");
		$ip138 = \Yii::$app->curl->post('http://1111.ip138.com/ic.asp', '');
		preg_match("#<center[^>]*>(.*?)<\/center>#", $ip138, $ipinfo);
		preg_match("/(?:\[)(.*)(?:\])/i", $ipinfo[1], $ip);
		$getaddress = empty($ipinfo[1]) ? '' : $ipinfo[1];
		$getip = empty($ip[1]) ? '' : $ip[1];
		return ['address' => iconv('GBK//IGNORE', 'UTF-8', $getaddress), 'ip' => $getip];
	}

	public function getIpAddress()
	{
		$ip = Yii::$app->request->userIP;
		$ch = curl_init();
		$url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
		// 添加apikey到header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 执行HTTP请求
		curl_setopt($ch , CURLOPT_URL , $url);
		$res = curl_exec($ch);
		$data = json_decode($res, true);

		$address = '';
		if($data['code'] == 0){
			$address = $data['data']['country'].' '.$data['data']['region'].' '.$data['data']['city'];
		}
		return $address;
	}


	/**
	 * [get 获取GET参数]
	 * @param  string $key [参数名称]
	 * @return [string]      [参数值]
	 */
	public function get($key = '')
	{
		$get  = \Yii::$app->request->get($key);
	
		if (is_array($get))
		{
			foreach ($get as $k => $v)
			{
				$get[$k] = trim($v);
			}
	
			return $get;
		}
		else
		{
			return trim($get);
		}
	
	}


	/**
	 * [post post参数获取]
	 * @param  string $key [表单Name]
	 * @return [type]      [表单值]
	 */
	public function post($key = null)
	{
		$post = \Yii::$app->request->post($key);
		if (is_array($post))
		{
			foreach ($post as $k => $v)
			{
				$post[$k] = trim($v);
			}
			return $post;
		}
		else
		{
			return trim($post);
		}
	
	}
	

	/**
	 * [error 错误提示]
	 * @param  [type] $msg [提示语]
	 * @param  [type] $url [跳转URL]
	 * @param  [type] $close [是否关闭弹出层]
	 * @return [type]      [description]
	 */
	public function error($msg, $url = null, $close = false)
	{
		if(\Yii::$app->request->isAjax){
			$array = array(
					'info' => $msg,
					'status' => false,
					'url' => $url,
					'close' => $close,
			);
			$this->ajaxReturn($array);
		}else{
			$this->alert($msg, $url);
		}
	}
	

	/**
	 * [success 成功提示]
	 * @param  [type] $msg [提示语]
	 * @param  [type] $url [跳转UrL]
	 * @param  [type] $close [是否关闭弹出层]
	 * @return [type]      [description]
	 */
	public function success($msg, $url = null, $close = false)
	{
		if(\Yii::$app->request->isAjax){
			$array = array(
					'info' => $msg,
					'status' => true,
					'url' => $url,
					'close' => $close,
			);
			$this->ajaxReturn($array);
		}else{
			$this->alert($msg, $url);
		}
	}
	

	/**
	 * AJAX返回
	 * @param string $message 提示内容
	 * @param bool $status 状态
	 * @param string $jumpUrl 跳转地址
	 * @return array
	 */
	public function ajaxReturn($data)
	{
		header('Content-type:text/json');
		echo json_encode($data);
		exit;
	}
	

	/**
	 * [alert description]
	 * @param  [type] $msg     [description]
	 * @param  [type] $url     [description]
	 * @param  string $charset [description]
	 * @return [type]          [description]
	 */
	public function alert($msg, $url = NULL, $charset='utf-8')
	{
		header("Content-type: text/html; charset={$charset}");
		$alert_msg="alert('$msg');";
		if( empty($url) ) {
			$go_url = 'history.go(-1);';
		}else{
			$go_url = "window.location.href = '{$url}'";
		}
		echo "<script>$alert_msg $go_url</script>";
		exit;
	}


	/**
	 * @desc 验证数据
	 * @access protected
	 * @param int $data 数据
	 * @param bool $type 判断类型 string|int|bool
	 * @return mixed
	 */
	protected function request_verify($data, $type = 'string',$datavalue)
	{
		
		if(!is_array($data))
		{
			$data = array($data);
		}
		switch($type)
		{
			case 'string':
				foreach($data as $val)
				{	
					if (!isset($datavalue[$val]) || !is_string($datavalue[$val]) || empty($datavalue[$val]))
					{
						return '';
					}
				}
				break;
			case 'int':
				foreach($data as $val)
				{
					if (!isset($datavalue[$val]) || !is_numeric($datavalue[$val]))
					{
						return 0;
					}
				}
				break;
			case 'bool':
				foreach($data as $val)
				{
					if (!isset($datavalue[$val]) || !is_bool($datavalue[$val]))
					{
						return false;
					}
				}
				break;
			case 'date':
				foreach($data as $val)
				{
					if (!isset($datavalue[$val]) || !\lib\components\AdCommon::isDate($datavalue[$val]))
					{
						return '';
					}
				}
				break;
		}
		return true;
	}


	/*
    * 生成下载excel文件
    * $filename="业务员录入数据";
    * $headArr=array("用户名","密码");
    * $data array(array('username'=>1,'pwd'=>2),array(...)..);
    * $this->getExcel($filename,$headArr,$data);
     *  */
	/*************************
	 * 修改函数，增加生成纵向表格
	 * author: lijunwei
	 * $filename：导出的文件名，默认加上生成文件日期;
	 * $headArr：一维数组，表头;
	 * $data:二维数组，要导出的数据;
	 * $type:导出表格类型，默认为1纵向表格，2为横向表格
	 * $search:文件名是否需要添加导出日期，默认为1，当=1时添加，当=0时不添加
	 * $this->getExcel($filename,$headArr,$data);
	 *************************/
	public function getExcel($fileName, $headArr, $data,$type=1,$search=1)
	{
		//对数据进行检验
		if (empty($data) || !is_array($data)) {
			die("没有数据可以导出！");
		}
		//检查文件名
		if (empty($fileName)) {
			exit;
		}

//        $objPHPExcel = new \PHPExcel();
		include_once("../lib/vendor/phpexcel/PHPExcel.php");
		$objPHPExcel = new \PHPExcel();

		//Set properties 设置文件属性
		$objProps = $objPHPExcel->getProperties();

		//设置文件名
		if($search==1){
			$date = date("Y_m_d", time());
			$fileName .= "_{$date}.xls";
		}elseif($search==0){
			$fileName .= ".xls";
		}

		//导出数据的默认格式：第一行表头，下方为数据
		if ($type == 1) {
			//导入表头
			$key = ord("A");
			foreach ($headArr as $v) {
				$colum = chr($key);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
				//单元格宽度自适应
				$objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setAutoSize(true);
				$key += 1;
			}
			//写入数据
			$column = 2;
			$objActSheet = $objPHPExcel->getActiveSheet();
			foreach ($data as $key => $rows) { //行写入
				$span = ord("A");
				foreach ($rows as $keyName => $value) { // 列写入
					$j = chr($span);
					//写入数据
					$objActSheet->setCellValue($j . $column, chunk_split($value, 500, ' '));
					//设置左右对齐
					$objActSheet->getStyle($j . $column)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					$span++;
				}
				$column++;
			}
		}
		//导出数据的第二种格式：第一列表头，右边为数据
		elseif($type==2){
			//导入表头
			$key = 1;
			foreach ($headArr as $v) {
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $key, $v);
				$key += 1;
			}
			//写入数据
			$span = ord("B");
			$objActSheet = $objPHPExcel->getActiveSheet();
			foreach ($data as $key => $rows) {//列写入
				$column = 1;
				foreach ($rows as $keyName => $value) {//行写入
					$j = chr($span);
					//写入数据
					$objActSheet->setCellValue($j . $column, chunk_split($value, 500, ''));
					//设置左右对齐
					$objActSheet->getStyle($j . $column)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					//单元格宽度自适应
					$objActSheet->getColumnDimension($j)->setAutoSize(true);
					$column++;
				}
				$span++;
			}
		}
		$fileName = iconv("utf-8", "gb2312", $fileName);
		//重命名工作表标签
		//$objPHPExcel->getActiveSheet()->setTitle($date);
		//设置活动单指数到第一个表,所以Excel打开这是第一个表
		$objPHPExcel->setActiveSheetIndex(0);
		ob_end_clean(); //清除缓冲区,避免乱码,那些年被坑过的乱码
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); //文件通过浏览器下载
		exit;
	}


	public function upload_image($savepath = ''){
		// Make sure file is not cached (as it happens for example on iOS devices)
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			exit; // finish preflight CORS requests here
		}

		if ( !empty($_REQUEST[ 'debug' ]) ) {
			$random = rand(0, intval($_REQUEST[ 'debug' ]) );
			if ( $random === 0 ) {
				header("HTTP/1.0 500 Internal Server Error");
				exit;
			}
		}

		@set_time_limit(5 * 60);

		$targetDir = 'upload_tmp';
//		$path = 'upload';
		$path = date("Ymd");
		/*相对路径*/
		$uploadDir = $savepath .DIRECTORY_SEPARATOR .$path;
		if(!is_dir($uploadDir))
		{
			mkdir($uploadDir,0777,true);
		}

		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds


// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}

// Create target dir
		if (!file_exists($uploadDir)) {
			@mkdir($uploadDir);
		}

// Get a file name
		if (isset($_REQUEST["name"])) {
			$fileName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$fileName = $_FILES["file"]["name"];
		} else {
			$fileName = uniqid("file_");
		}

		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
		$uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;


// Remove old temp files
		if ($cleanupTargetDir) {
			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}

			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// If temp file is current file proceed to the next
				if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
					continue;
				}

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
					@unlink($tmpfilePath);
				}
			}
			closedir($dir);
		}


// Open temp file
		if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

		$index = 0;
		$done = true;
		for( $index = 0; $index < $chunks; $index++ ) {
			if ( !file_exists("{$filePath}_{$index}.part") ) {
				$done = false;
				break;
			}
		}
		if ( $done ) {
			if (!$out = @fopen($uploadPath, "wb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}

			if ( flock($out, LOCK_EX) ) {
				for( $index = 0; $index < $chunks; $index++ ) {
					if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
						break;
					}

					while ($buff = fread($in, 4096)) {
						fwrite($out, $buff);
					}

					@fclose($in);
					@unlink("{$filePath}_{$index}.part");
				}

				flock($out, LOCK_UN);
			}
			@fclose($out);
		}

// Return Success JSON-RPC response die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}')
		echo $uploadDir;
	}


	/**
	 * 获取城市经纬度
	 */
	public function getCoordinate($city="深圳市",$area='南山区',$address='白石洲'){
//     	     	$city= "上海市";//$this->input->post('city');
//     	     	$province="上海市";//$this->input->post('province');
//     	     	$area="徐汇区";//$this->input->post('area');
//     	     	$address="";//$this->input->post('address');
		//百度地图
//		$json=file_get_contents("http://api.map.baidu.com/geocoder?address=".trim($area).trim($address)."&output=json&city=".trim($city)."");
		$address = str_replace(" ","",$address);
		//高德地图定位
		$json = file_get_contents("http://restapi.amap.com/v3/geocode/geo?key=389880a06e3f893ea46036f030c94700&s=rsv3&address=".trim($area).trim($address)."&city=".trim($city)."&output=json");
		$infolist = json_decode($json);
		$array = [];
		if(isset($infolist->geocodes[0]->location) && !empty($infolist->geocodes[0]->location)){
			$array = explode(",",$infolist->geocodes[0]->location);
			$array = array(
					'lng'=>$array[0],
					'lat'=>$array[1],
					'errorno'=>'0'
			);
		}
		return $array;
	}

	/**
	 * 设置流水号
	 */
	public function getBindSn($type='')
	{
		//生成随机字母+数字
		$str = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$code = "";
		$len = strlen($str);
		for ($i = 0; $i < 6; $i++) {
			$code .= $str{rand(0, $len - 1)};
		}
		return $type . date('YmdHis') . $code;
	}

	//发送验证码--阿里大鱼
   /* protected function sendSmsCode($mobile,$type) {

    	//测试阶段
    	$tar_content='1234';

    	$result = CommonService::sendMobileVerifyCode($mobile,$type);
    	
		$codesData = QfbSmsCode::findOne(['phone' => $mobile, 'status' => 1]);
		if (!empty($codesData))
		{
			$codesData->code = $tar_content;
			$codesData->sen_time = time();
			$codesData->save();
		} else {
			$codesData = new QfbSmsCode;
			$codesData->code = $tar_content;
			$codesData->phone=$mobile;
			$codesData->sen_time = time();
			$codesData->save();
		}*/
		//阿里大于短信发送
        /*$c = new TopClient;
        $c->appkey = '';
		$c->secretKey = '495e88853dc0596801e374f67a1aa8ed';
		$req = new AlibabaAliqinFcSmsNumSendRequest;
		$req->setExtend("$mobile");
		$req->setSmsType("normal");
		$req->setSmsFreeSignName('测试111林');
		$req->setSmsParam("{\"code\":\"$tar_content\",\"tel\":\"$mobile\"}");
		$req->setRecNum($mobile);
		$req->setSmsTemplateCode("SMS_72000230");
		$resp = $c->execute($req);*/
    //}


	/**
	 * 防表单重复提交
	 * @return bool
     */
	public function check($key)
	{
		$session = \Yii::$app->session;
		$user_id = Yii::$app->user->getId();
		$sessionKey = $user_id.$key;
		if(isset($session[$sessionKey])){
			$first_submit_time = $session[$sessionKey];
			$current_time      = time();
			if($current_time - $first_submit_time < 10){
				$session[$sessionKey] = $current_time;
				return false;
			}else{
				unset($session[$sessionKey]);//超过限制时间，释放session";
			}
		}
		//第一次点击确认按钮时执行
		if(!isset($session[$sessionKey])){
			$session[$sessionKey] = time();
		}

		return true;
	}


	public function get_amount($num){  
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
