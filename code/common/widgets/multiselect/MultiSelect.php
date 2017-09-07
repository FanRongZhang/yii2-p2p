<?php
namespace common\widgets\multiselect;
use Yii;
use yii\widgets\InputWidget;
use common\widgets\multiselect\MultiSelectAsset;
use yii\helpers\Html;

/**
 * 左右多项选择
 * @author Ben
 *
 */
class MultiSelect extends InputWidget
{
    /**
     * 控件属性
     * @var unknown
     */
    public $attributes;
    
    public function init()
    {
        parent::init();
    }
    
    /**
     * (non-PHPdoc)
     * @see \yii\base\Widget::run()
     */
    public function run()
    {
        $this->attributes['id']=$this->options['id'];
        $left_id=sprintf('%s_select1',$this->options['id']);
        $right_id=sprintf('%s_select2',$this->options['id']);
        $data = $this->options['list'];
        
        if(!isset($this->options['width'])||empty($this->options['width'])||$this->options['width']=='')
        {
            $this->options['width']='120px';
        }
        if(!isset($this->options['height'])||empty($this->options['height'])||$this->options['height']=='')
        {
            $this->options['height']='250px';
        }
        
        $html='<table class="multi-selector"><tr>
            <td>
            <select name="'.$left_id.'" size="6" multiple id="'.$left_id.'" style="width:'.$this->options['width'].';height:'.$this->options['height'].';" >'.$this->getListOptions($data[0]).'</select></td>';
        
        $html.='<td style="padding:5px;">
            <input name="sure1" type="button" id="sure1" 
            onClick="allsel(document.getElementById(\''.$right_id.'\'),document.getElementById(\''.$left_id.'\'));
                setSelectedOptions(document.getElementById(\''.$right_id.'\'),\''.$this->options['id'].'\');"
                value="<<"><br/><br/>';
        $html.='<input name="sure2" type="button" id="sure2" 
            onClick="allsel(document.getElementById(\''.$left_id.'\'),document.getElementById(\''.$right_id.'\'));
                setSelectedOptions(document.getElementById(\''.$right_id.'\'),\''.$this->options['id'].'\');" value=">>" align="center" height="2"></td>';
        $html.='<td><select name="'.$right_id.'" size="6" multiple id="'.$right_id.'" style="width:'.$this->options['width'].';height:'.$this->options['height'].';">'.$this->getListOptions($data[1]).'</select>';
        if($this->hasModel()){
            $html.=Html::activeHiddenInput($this->model,$this->attribute,$this->options);
        }else{
            $html.=Html::activeHiddenInput($this->name,$this->attribute,$this->options);
        }
        $html.='</td></tr></table>';
        echo $html;
        $this->view->registerJs('setSelectedOptions(document.getElementById(\''.$right_id.'\'),\''.$this->options['id'].'\')',yii\web\View::POS_READY);
        MultiSelectAsset::register($this->getView());
    }
        
    /**
     * 获取目标options选项
     * @param array $data
     */
    function getTargetOptions($data)
    {
        $r='';
        foreach($data as $k=>$v)
        {
            $r.='<option value="'.$k.'">'.$v.'</option>';
        }
        return $r;
    }
    
    /**
     * 获取options
     * @param array $data
     * @return string
     */
    function getListOptions($data)
    {
        if(!is_array($data))
            return '';
        $options = '';
        foreach($data as $k=>$v)
        {
            $options.='<option value="'.$k.'">'.$v.'</option>';
        }
        return $options;
    }
}

?>