<?php
namespace common\service;
use Yii;
use yii\db\ActiveRecord;
use api\common\helpers\ReseponseCode as Code;

/**
 * 业务逻辑处理基类
 * @author Administrator
 *
 */
abstract class BaseService
{

    public $list; //信息列表
    public $info; //单条信息
    protected $model; //模型
    public $message;//信息
    protected $messages;//错误信息列表
    //public $code;  //编号
    public $errorModel ; //报错模型

    protected static $now_time;//当前时间
    /**
     * 获取当前时间
     * @return [type] [description]
     */
    public static function getTime(){
        return static::$now_time=static::$now_time==null?time():static::$now_time;
    }
   // public $pagesize;
   // public $offset;
    /**
     * 去掉前后1个字符串
     * @param string $data
     */
    public static function charTrim($data)
    {
        return substr($data,1,strlen($data)-2);
    }
    public function findModel($id){
        if($this->model){
            if($this->model[$this->model->primaryKey]==$id ) return $this->model;
        }
        $model = new $this->_className;
        return $this->model=$model::findOne($id);
    }
    /**
     * [returnError 返回错误]
     * @param  [type] $message [错误信息]
     * @return [type]          [description]
     */
    protected function returnError($message){
        $this->message = $message;
        return false;
    }

    /**
     * 获取用户id
     * [getMemberID description]
     * @return [type] [description]
     */
    protected function getMemberID(){
        return Yii::$app->user->identity->id?Yii::$app->user->identity->id:0;
    }

    /**
     * 获取模型
     */
    public function getModel(){
        return $this->model;
    }
    /**
     * 新建模型
     * [newModel description]
     * @return [type] [description]
     */
    public function newModel(){
        return $this->model=new $this->_className;
    }

    /**
     * 判断存在
     */
    public function validExist($id){
        $this->model = $this->findModel($id);
        if($this->model==null){
            $this->addMessage('id',Code::$statusTexts[Code::NO_EXIST]);
            return false;
        }
        return true;
    }
    protected function addMessage($name,$value){
        $this->messages[$name]=$value;
    }

    public function afterValid(){
        return $this->messages?false:true;
    }

    public function getMessages(){
        return $this->messages;
    }
    public function findOneMessage(){
        if($this->messages==null) return null;
        foreach ($this->messages as $key => $value) {
            return $value;
        }
    }
    // public function findModelByPk($pk,$join=null,$select=null,$model=null){

    //     $this->model=$model ?$model:$this->getModel();


    //     $key=$this->model->primaryKey()[0];
    //     $query=$this->model->find();
    //     $query->andWhere(['=', $this->model->tableName().'.'.$key, $pk]);
    //     if(!empty($join)){
    //         foreach ($join as $key => $value) {
    //             $query->joinWith($value);
    //         }
    //     }
    //     if($select){
    //         $query->select=$select;
    //     }
    //     return $this->list[$pk]=$this->info=$query->one();
    // }

    // public function findSearch($where=null,$join=null,$select=null,$model=null,$order=null,$group=null){
    //     $this->model=$model ?$model:$this->getModel();
    //     $primaryKey=$this->model->primaryKey()[0];
    //     $query=$this->model->find();
    //     if(!empty($join)){
    //         foreach ($join as $key => $value) {
    //             $query->joinWith($value);
    //         }
    //     }
    //     if(!empty($where)){
    //         foreach ($where as $key => $value) {
    //             $query->andFilterWhere($value);
    //         }
    //     }
    //     if(!empty($select)){
    //         $query->select($select);
    //     }
    //     if(!empty($order)){
    //         $query->orderBy($order);
    //     }else{
    //         $query->orderBy($this->model->tableName().'.'.$primaryKey.' desc');
    //     }
    //     if(!empty($group)){
    //         $query->groupBy($group);
    //     }

    //     $params=Yii::$app->request->queryParams;

    //     $page=isset($params['page'])?$params['page']:1;
    //     $pagesize=10;
    //     $query->limit=$pagesize;
    //     $query->offset=($page-1)*$pagesize;
    //     return $query;
    // }
    // public function scenario($scenario){
    //     $this->model->scenario=$scenario;
    //     return $this->model;
    // }
    //  public  function findModelByWhere($where,$join=null,$select=null,$model=null,$asArray=0,$orderby=null){
    //     $this->model=$model ?$model:$this->getModel();
    //     $query=$this->model->find();
    //     foreach ($where as $key => $value) {
    //         $query->andWhere($value);
    //     }
    //     if(!empty($join)){
    //         foreach ($join as $key => $value) {
    //             $query->joinWith($value);
    //         }
    //     }
    //     if($select){
    //         $query->select($select);
    //     }
    //     if($asArray){
    //         $query->asArray();
    //     }
    //     if($orderby){
    //         $query->orderBy($orderby);
    //     }
    //     return $query->one();
    // }

     public function findBySql($sql,$is_one=0){
         $command = Yii::$app->db->createCommand($sql);
         if($is_one){
             return $command->queryOne();
         }
         $result= $command->queryAll();
         return $result;
     }

     /**
     * 写日志，
     */
     function serviceWirteLog($fileName, $content='')
     {
        $notifyLog = Yii::$app->getRuntimePath() . '/logs/' . $fileName;
        if (!file_exists($notifyLog)) {
            touch($notifyLog);
        }
        $fp = fopen($notifyLog, "a");
        fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);
     }
}

?>