<?php
namespace common\widgets\datepicker;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * 日期控件
 * @author Ben
 *
 */
class DatePicker extends InputWidget
{
   public $attributes;
 
    public function init()
    {
        parent::init();
    }
 
    /**
     * 重写run方法
     * (non-PHPdoc)
     * @see \yii\base\Widget::run()
     */
    public function run()
    {
        $view = $this->getView();
        $this->attributes['id']=$this->options['id'];
        if($this->hasModel()){
            $name= Html::getInputName($this->model, $this->attribute);
            $value =Html::getAttributeValue($this->model, $this->attribute);
            $this->options['elem']='#'.strtolower($this->model->formName().'-'.$this->attribute);
            $input=Html::activeInput('text',$this->model, $this->attribute,$this->getOptions($this->options,$name,$value));
        }else{
            $this->options['elem']='#'.$this->name;
            $input=Html::activeInput('text',$this->name,'',$this->attributes);
        }
        echo $input;
        DatePickerAsset::register($view);

    }
    
    /**
     * 生成参数
     * @param unknown $options
     */
    function getOptions($options,$name,$value)
    {
        $readonly=false;
        $arr=array();
         if(in_array('readonly',array_keys($options))){
            $readonly = $options['readonly'];
        }
        unset($options['readonly']);
        $arr=[
        		'onclick'=>'laydate('.Json::encode($options).')',
        		'class'=>'laydate-icon'
        ];
        //转换int时间
        if(isset($name) && $name!='' && $value!='')
            $arr['value']=date('Y-m-d',$value);
        if($readonly)
			$arr['readonly']='readonly';
        return $arr;
    }
    
    /**
     * ���ֶδ�stringת��intʱ��
     * @param model $model
     * @param params $params
     * @param array fields $field
     */
    public static function strToTime(&$model,&$params,$field)
    {
    	$scope = $model->formName();
    	foreach($field as $k=>$v)
    	{
    	    if($scope!=='')
    	    {
    	        if(isset($params[$scope][$v])&&$params[$scope][$v]!==''){
    	            $params[$scope][$v]=strtotime($params[$scope][$v]);
    	            $model->$v=$params[$scope][$v];
    	        }
    	    }else {
    	        if(isset($params[$v])&&$params[$v]!==''){
    	            $params[$v]=strtotime($params[$v]);
    	            $model->$v=$params[$v];
    	        }
    	    } 
    	}
    }
}

?>