<?php
namespace common\service\MongoService;
use common\models\MongodbModel;

/**
 * 会员等级业务逻辑
 * @author Ben
 *
 */
class MessageService
{
	protected $tableName;
	protected $model;
	public function __construct($member_id){

		$number=$member_id % 1000;
		$this->tableName="news_{$number}";
		$this->model = new MongodbModel();

	}

	public function findCount(array $condition){
		return $this->model->countMongo($this->tableName, $condition, $dbserver="dw");
	}

	//查一条
	public function findOne(array $condition, $fields = array(), $dbserver="dw"){
		return $this->model->findMongo($this->tableName, $condition , $fields , $dbserver );
	}
	//查找最近一条
	public function findOneDesc(array $condition, $fields = array(),$order = "_id", $dbserver="dw"){
		$list=$this->model->findMongoList($this->tableName, $condition, ['limit'=>1 ,'sort' => array($order => -1)], $fields, $dbserver);
		if(count($list)>0)
			return $list[0];
		return [];
	}
	//差列表
	public function findList(array $condition,$result_limit = array(), $fields = array(),$dbserver="dw"){
		return $this->model->findMongoList($this->tableName, $condition, $result_limit, $fields, $dbserver);
	}


	public function insert (array $params, $dbserver="dw"){

	}

	public function update(array $condition, array $params, $options = array(), $dbserver="dw"){
		return $this->model->updateMongo($this->tableName,$condition, ['$set'=>$params], $options , $dbserver);
	}

	public function read(array $condition){
        return $this->update($condition,['is_read'=>1]);
	}
}