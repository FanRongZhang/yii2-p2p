<?php 
namespace common\models\money;
//资产详情
class Type{
    protected $list;
    protected $statusType ;
    public function getStatusType($type){
        $this->statusType= $type;
        return $this;
    }
    public function all(){
        return $this->list;
    }
    public function load($params){
        foreach ($params as $key => $value) {
            $this->add($key,$value);
        }
        return $this;
    }

    private function add($key,$value){
        $this->list[]=new Detail($key,$this->statusType,$value);
    }

}


