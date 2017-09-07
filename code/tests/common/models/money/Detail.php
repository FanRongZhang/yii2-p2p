<?php
namespace common\models\money;
//资产详情
class Detail{
    public $title ;
    public $type;
    public $content;
    public function __construct($title,$type,$content){
        $this->title = $title;
        $this->type = $type;
        $this->content=$content;
    }
}


