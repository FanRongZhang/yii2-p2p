<?php
namespace common\service\MongoService;
use common\models\MongodbModel;

/**
 * 用户不能重复登录的业务逻辑
 * @author panheng
 *
 */
class HkyhConsoleService
{
    protected $tableName;
    protected $model;
    public function __construct($string){
        $this->tableName="hkyk_{$string}";
        $this->model = new MongodbModel();

    }

    public function insert (array $params, $dbserver="dw"){
        $this->model->insertMongo($this->tableName, $params, $dbserver);
    }

    public function update(array $condition, array $params, $options = array(), $dbserver="dw"){
        return $this->model->updateMongo($this->tableName,$condition, ['$set'=>$params], $options , $dbserver);
    }

    public function findOne(array $condition, $fields = array(), $dbserver="dw"){
        return $this->model->findMongo($this->tableName, $condition , $fields , $dbserver );
    }

}