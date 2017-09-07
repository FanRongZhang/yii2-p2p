<?php
/*** Mongodb类** examples:
 * $mongo = new MongoDb("127.0.0.1:11223");
 * $mongo->selectDb("test_db");
 * 创建索引
 * $mongo->ensureIndex("test_table", array("id"=>1), array('unique'=>true));
 * 获取表的记录
 * $mongo->count("test_table");
 * 插入记录
 * $mongo->insert("test_table", array("id"=>2, "title"=>"asdqw"));
 * 更新记录
 * $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"));
 * 更新记录-存在时更新，不存在时添加-相当于set
 * $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"),array("upsert"=>1));
 * 查找记录
 * $mongo->find("c", array("title"=>"asdqw"), array("start"=>2,"limit"=>2,"sort"=>array("id"=>1)))
 * 查找一条记录
 * $mongo->findOne("$mongo->findOne("ttt", array("id"=>1))", array("id"=>1));
 * 删除记录
 * $mongo->remove("ttt", array("title"=>"bbb"));
 * 仅删除一条记录
 * $mongo->remove("ttt", array("title"=>"bbb"), array("justOne"=>1));
 * 获取Mongo操作的错误信息
 * $mongo->getError();
 */
namespace common\extension;
use yii;
class MongoDb
{
	//private $mongo_server = "192.168.1.5:11707";//"127.0.0.1:27017";//112.124.60.233
	//private $mongo_options = array('connect'=>false, 'replicaSet'=>'');
	//private $mongo_auto_balance = true;
	
	//Mongodb连接
	var $mongo;

	var $curr_db_name;
	var $curr_table_name;
	var $error;
	var $grid;
	
	/**
	 * 构造函数
	 * 支持传入多个mongo_server(1.一个出问题时连接其它的server 2.自动将查询均匀分发到不同server)
	 *
	 * 参数：
	 * $mongo_server:数组或字符串-array("127.0.0.1:1111", "127.0.0.1:2222")-"127.0.0.1:1111"
	 * $mongo_auto_balance:是否自动做负载均衡，默认是
	 * 
	 * $mongo_options = array(
     * 'connect' => true, // true表示Mongo构造函数中建立连接。
     * 'timeout'=>xxxx, // 配置建立连接超时时间，单位是ms
     * 'replicaSet'=>'name', // 配置replicaSet名称
     * 'username'=>'', // 覆盖$server字符串中的username段，如果username包含冒号:时，选用此种方式。
     * 'password'=>'', // 覆盖$server字符串中的password段，如果password包含符号@时，选用此种方式。
     * 'db'=>'' // 覆盖$server字符串中的database段
	 * );
	 * 
	 * 返回值：
	 * 成功：mongo object
	 * 失败：false
	 */
	
	function __construct($mongo_server, $mongo_options=array('connect'=>TRUE), $auto_balance=TRUE)
	{
		//$mongo_server = $this->mongo_server;
		//$options = $this->mongo_options;
		//$auto_balance = $this->mongo_auto_balance;
		if (is_array($mongo_server))
		{
			$mongo_server_num = count($mongo_server);
			if ($mongo_server_num > 1 && $auto_balance)
			{
				$prior_server_num = rand(1, $mongo_server_num);
				$rand_keys = array_rand($mongo_server,$mongo_server_num);
				$mongo_server_str = $mongo_server[$prior_server_num-1];
				foreach ($rand_keys as $key)
				{
					if ($key != $prior_server_num - 1)
					{
						$mongo_server_str .= ',' . $mongo_server[$key];
					}
				}
			}
			else
			{
				$mongo_server_str = implode(',', $mongo_server);
			}
		}
		else
		{
			$mongo_server_str = $mongo_server;
		}
		try {
			$this->mongo = new \MongoClient($mongo_server_str, $mongo_options);
		}
		catch (MongoConnectionException $e)
		{
			$this->error = $e->getMessage();
			return false;
		}
	}

	function getInstance($mongo_server, $flag=array())
	{
		static $mongodb_arr;
		if (empty($flag['tag']))
		{
			$flag['tag'] = 'default';
		}
		if (isset($flag['force']) && $flag['force'] == true)
		{
			$mongo = new MongoDb($mongo_server);
			if (empty($mongodb_arr[$flag['tag']]))
			{
				$mongodb_arr[$flag['tag']] = $mongo;
			}
			return $mongo;
		}
		else if (isset($mongodb_arr[$flag['tag']]) && is_resource($mongodb_arr[$flag['tag']]))
		{
			return $mongodb_arr[$flag['tag']];
		}
		else
		{
			$mongo = new MongoDb($mongo_server);
			$mongodb_arr[$flag['tag']] = $mongo;
			return $mongo;
		}
	}

	/**
	 * 连接mongodb server
	 *
	 * 参数：无
	 *
	 * 返回值：
	 * 成功：true
	 * 失败：false
	 */
	function connect()
	{
		try {
			$this->mongo->connect();
			return true;
		}
		catch (MongoConnectionException $e)
		{
			$this->error = $e->getMessage();
			return false;
		}
	}

	/**
	 * select db
	 *
	 * 参数：$dbname
	 *
	 * 返回值：无
	 */
	function selectDb($dbname)
	{
		$this->curr_db_name = $dbname;
	}
	
	/**
	 * 
	 * 返回数据库列表
	 * 
	 * 返回值：结果集
	 */
	function listDbs()
	{
		
		$dbs = $this->mongo->listDBs();
		
		return $dbs;
	}

	/**
	 * 创建索引：如索引已存在，则返回。
	 *
	 * 参数：
	 * $table_name:表名
	 * $index:索引-array("id"=>1)-在id字段建立升序索引
	 * $index_param:其它条件-是否唯一索引等
	 *
	 * 返回值：
	 * 成功：true
	 * 失败：false
	 */
	function ensureIndex($table_name, $index, $index_param=array())
	{
		$dbname = $this->curr_db_name;
		$index_param['safe'] = 1;
		try {
			$this->mongo->$dbname->$table_name->ensureIndex($index, $index_param);
			return true;
		}
		catch (MongoCursorException $e)
		{
			$this->error = $e->getMessage();
			return false;
		}
	}

	/**
	 * 插入记录
	 *
	 * 参数：
	 * $table_name:表名
	 * $record:记录
	 *
	 * 返回值：
	 * 成功：最新的记录ID
	 * 失败：false
	 */
	function insert($table_name, $record)
	{
		$dbname = $this->curr_db_name;
		try {
			$record = $this->jstripslashes($record);
			$this->mongo->$dbname->$table_name->insert($record, array('w'=>true));
			if(is_array($record))
				return $record['_id']->{'$id'};
			elseif (is_object($record))
				return $record->_id->{'$id'};
		}
		catch (MongoCursorException $e)
		{
			$this->error = $e->getMessage();
			return false;
		}
	}

	/**
	 * 查询表的记录数
	 *
	 * 参数：
	 * $table_name:表名
	 *
	 * 返回值：表的记录数
	 */
	function count($table_name, $arrWhere = array() )
	{
		$dbname = $this->curr_db_name;
	
		return $this->mongo->$dbname->$table_name->count($arrWhere);
	}

     /**
	 * 分组查询
	 * @param  $table_name
	 * @param  $condition
	 * @param  $keys
	 * @param  $initial
	 * @param  $reduce
	 */
	function group( $table_name, $condition, $keys,  $initial = array("count" =>0), $reduce = "function (obj, prev) { prev.count++; }" )
	{
		/*
		  $arr = $mongo->group('comm_rule', array(), array('area_id'=>1,'type_id'=>1),array("count" =>0,"items"=>array()),"function (obj, prev) { prev.count++; if(prev.items.length==0){prev.items.push(obj);}}"); 
		 */
		$dbname = $this->curr_db_name;
		return $this->mongo->$dbname->$table_name->group($keys, $initial, $reduce, array('condition'=>$condition));//$condition
	}
	
	
	/**
	 * 更新记录
	 *
	 * 参数：
	 * $table_name:表名
	 * $condition:更新条件
	 * $newdata:新的数据记录
	 * $options:更新选择-upsert/multiple
	 *
	 * 返回值：
	 * 成功：true
	 * 失败：false
	 */
	function update($table_name, $condition, $newdata, $options=array())
	{
		$dbname = $this->curr_db_name;
		$options['upsert'] = 1;
		if (!isset($options['multi']))
		{
			$options['multi'] = true;
		}
			
		try {
				$newdata = $this->jstripslashes($newdata);
				$mVar = $this->mongo->$dbname->$table_name->update($condition, $newdata, $options);
				return $mVar;
			}
		catch (MongoCursorException $e)
			{
				$this->error = $e->getMessage();
				return false;
			}
	}

	/**
	 * 删除记录
	 *
	 * 参数：
	 * $table_name:表名
	 * $condition:删除条件
	 * $options:删除选择-justOne
	 *
	 * 返回值：混合型
	 * 成功：1. Array ( [n] => 1 [connectionId] => 4 [err] => [ok] => 1 )  成功删除
	 * 		 2. Array ( [n] => 0 [connectionId] => 4 [err] => [ok] => 1 )  已经删除过了,可以理解成删除失败

	 * 
	 * 失败：false
	 */
	function remove($table_name, $condition, $options=array())
	{
		$dbname = $this->curr_db_name;
		$options['w'] = 1;
		try {
			return $this->mongo->$dbname->$table_name->remove($condition, $options);
			
		}
		catch (MongoCursorException $e)
		{
			$this->error = $e->getMessage();
			return false;
		}
	}
	
	/**
	 * 删除会在内存和文件产生碎片，整理内存碎片。*
--------------------------------------------------------------------------------
	 *	返回值数组格式Array( [ok] => 1)；
	 */
	function repair()
	{
		$dbname = $this->curr_db_name;
		return $this->mongo->$dbname->repair();
	}
	
	

	/**
	 * 查找记录
	 *
	 * 参数：
	 * $table_name:表名
	 * $query_condition:字段查找条件
	 * $result_condition:查询结果限制条件-limit/sort等
	 * $fields:获取字段
	 *
	 * 返回值：
	 * 成功：记录集
	 * 失败：false
	 */
	function find($table_name, $query_condition, $result_condition=array(), $fields=array())
	{
		$dbname = $this->curr_db_name;

		$cursor = $this->mongo->$dbname->$table_name->find($query_condition, $fields);
		
		if (!empty($result_condition['limit']))
		{
			$cursor->limit($result_condition['limit']);
		}
		
		if (!empty($result_condition['start']))
		{
			$cursor->skip($result_condition['start']);
		}
		
		if (!empty($result_condition['sort']))
		{
			$cursor->sort($result_condition['sort']);
		}
		$result = array();
		try {
			while ($cursor->hasNext())
			{ 
					$result[] = $cursor->getNext();
			}
		}
		catch (MongoConnectionException $e)
		{
			$this->error = $e->getMessage();
			return false;
		}
		catch (MongoCursorTimeoutException $e)
		{
			$this->error = $e->getMessage();
			return false;
		}
		
		return $result;
	}

	/**
	 * 查找一条记录
	 *
	 * 参数：
	 * $table_name:表名
	 * $condition:查找条件
	 * $fields:获取字段
	 *
	 * 返回值：
	 * 成功：一条记录
	 * 失败：false
	 */
	function findOne($table_name, $condition, $fields=array())
	{
		$dbname = $this->curr_db_name;
		return $this->mongo->$dbname->$table_name->findOne($condition, $fields);
	}

	/**
	 * 获取当前错误信息
	 *
	 * 参数：无
	 *
	 * 返回值：当前错误信息
	 */
	function getError()
	{
		return $this->error;
	}
	
	/**
	 * 
	 * 取得girdfs对像
	 * 
	 * 参数：无
	 * 
	 * 返回值：
	 * 成功：true
	 * 失败：false
	 */
	function girdfs()
	{
		$dbname = $this->curr_db_name;
		try 
		{
			$this->grid = $this->mongo->$dbname->getGridFS();
			return $this->grid;
		}
		catch (MongoCursorException $e)
		{
			$this->error = $e->getMessage();
			return false;
		}
	}

    function jstripslashes($string) {
        if (is_array ( $string )) {
            foreach ( $string as $key => $val ) {
                $string [$key] = $this->jstripslashes ( $val );
            }
        } else {
            if(gettype($string)=="string")
                $string = stripslashes ( $string );
        }
        return $string;
    }

}

