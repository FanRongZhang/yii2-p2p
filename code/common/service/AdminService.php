<?php
namespace common\service;
use common\models\Admin;
use common\models\Supplier;
use common\models\AdminGroup;
use common\enum\PermissionEnum;
use common;
use Faker\Test\Provider\ro_RO\PersonTest;
use common\models\QfbAdminGroup;
use common\models\QfbAdmin;

/**
 * 会员等级业务逻辑
 * @author Ben
 *
 */
class AdminService extends BaseService implements IComponent
{   
    /**
     * 当前访问菜单
     * @var array
     */
    static $current_menu = null;
    
    /**
     * 用户当前页面权限值
     * @var array
     */
    static $permission_group = null ;
    
    /**
     * (non-PHPdoc)
     * @see \common\service\IComponent::getComponentData()
     */
    public function getComponentData($type=1,$sourceData=null)
    {
        
    }
        
    /**
     * 获取用户组用户数据
     * @param string $user_data
     * @return Ambigous <string, unknown>
     */
    public static function getGroupUsers($user_data)
    {
        $result = '';
        $ids=BaseService::charTrim($user_data);
        if($ids!='')
        {
            $users = QfbAdmin::find()->select('account,true_name')->where(['id'=>explode(',',$ids)])->asArray()->all();
            if($users && count($users)>0)
            {
                foreach($users as $k=>$v)
                {
                    if($result!='')
                        $result.='、';
                    $result.=$v['true_name']?$v['true_name']:$v['account'];
                }
            } 
        }
        return $result;
    }
    
    /**
     * 获取所有用户id
     */
    static function getGroupIds()
    {
        $groups = QfbAdmin::find()->select('id')->where(['is_sys'=>0])->asArray()->all();
        $ids = '';
        if($groups)
        {
            foreach($groups as $k=>$v)
            {
                $ids.=$v['id'].',';
            }
        }
        return substr($ids, 0, -1);
        
        $groups = QfbQfbAdminGroup::find()->select('users')->asArray()->all();
        $ids = '';
        if($groups && count($groups)>0)
        {
            foreach($groups as $k=>$v)
            {
                if($v['users']!=''){
                    if($ids!='')
                        $ids.=',';
                    $ids.=BaseService::charTrim($v['users']);
                }
            }
        }
        return $ids;
    }
    
    /**
    *获取供应商信息
    */
    public static function findSupplierByAdmin($admin_id){
        $query=QfbAdmin::find();
        $query->andWhere(['=', QfbAdmin::tableName().'.id', $admin_id]);
        $query->joinWith('supplier');
        $query->one();
        return $query->one();
    }

    /**
     * 获取多选框列表数据
     */
    public static function getMultiSelectData()
    {
        $data = [];
        $exist_ids = static::getGroupIds();
        $query = QfbAdmin::find()->select(['id','true_name','account']);
        if($exist_ids!='')
        {
            $query = $query->where('id not in ('.$exist_ids.')');
        }
        $list = $query->asArray()->where(['is_sys'=>0,'enabled'=>1])->all();
        if($list && count($list)>0)
        {
            foreach($list as $k=>$v)
            {
                if($v['true_name']=='')
                    $data[$v['id']]=$v['account'];
                else
                    $data[$v['id']]=$v['true_name'];
            }
        }
        return $data;
    }
    
    /**
     * 设置左边下拉列表栏目
     * @param string $model_data
     */
    public static function setDefaultSelectData($model_data=null)
    {
        $list_data = static::getMultiSelectData();
        $target_data = static::setMuliSelectData($model_data);
        if($model_data!=null)
        {
            foreach($target_data as $k=>$v)
            {
                if(isset($list_data[$k])||!empty($list_data[$k]))
                {
                    unset($list_data[$k]);
                }
            }
        }
        return [$list_data,$target_data];
    }
    
    /**
     * 设置数据
     * @param string $data
     */
    public static function setMuliSelectData($data)
    {
        if(strlen($data)>2)
        {
            $newstr = static::charTrim($data);
            $ids = explode(',', $newstr);
            $result = QfbAdmin::findAll($ids);
            $d = [];
            foreach($result as $k=>$v)
            {
                $d[$v->id]=$v->true_name==''?$v->account:$v->true_name;
            }
            return $d;
        }else
            return '';
    }
    
    /**
     * 获取权限数据
     * @param int $permission_value
     * @return array
     */
    public static function getPermissionsData($permission_value)
    {
        $pers = PermissionEnum::getPermissionText();
        $permission = [];
        if($permission_value>0){
            
        }
        return $permission;
    }
    
    /**
     * 判断权限值是否存在
     * @param integer $pv
     * @param array $data
     */
    public static function isChecked($key,$pv,$data)
    {
        if(!is_array($data))
            $data=json_decode($data,true);
        if(isset($data[$key]))
        {
            return ($data[$key]&$pv)==$pv;
        }
        return false;
    }
    
    /**
     * 获取用户权限菜单
     * @param integer $user_id
     * @return array
     */
    public static function getUserPermissionMenu($user_id)
    {
        if(static::$permission_group!=null)
            return static::$permission_group;
        $groups = QfbAdminGroup::find()->where(['like','users',sprintf(',%d,',$user_id)])->asArray()->all();
        $group_menus = [];
        if(is_array($groups))
        {
            $tmp_array=[];
             $tmp_array = array_merge($tmp_array,array_map(function($value){
                return json_decode($value['permission'],true);
            }, $groups));
             foreach($tmp_array as $k=>$v)
             {
                 if($v && is_array($v)){
                     foreach($v as $kk=>$vv)
                     {
                         $group_menus[$kk]=$vv;
                     }
                 }
             }
        }
        static::$permission_group=$group_menus;
        return $group_menus;
    }
    
    /**
     * 获取用户菜单
     * @param integer $user_id
     * @param boolean $is_sys
     */
    public static function getUserMenu($user_id=null,$is_sys=null)
    {
        return (new \common\service\MenuService($user_id==null?\Yii::$app->user->identity->id:$user_id,
            $is_sys==null?\Yii::$app->user->identity->is_sys==1:$is_sys))->getUserMenu();
    }

    /**
     * 获取当前用户访问菜单
     * @return string|multitype:string NULL
     */
    static function getCurrentMenu($view)
    {
        //获取当前视图数据
        $view_data=[
            $view->context->module->id,
            $view->context->id,
            $view->context->action->id,
            \Yii::$app->request->getUrl()
        ];
        if(static::$current_menu!=null)
            $menu=static::$current_menu;
        else{
            $menu = common\models\Menu::find()
            ->select('id,permision_value')
            ->where(['like','url',sprintf('/%s/%s',$view_data[0],$view_data[1])])
            ->asArray()->one();
            static::$current_menu = $menu;
        }
        return $menu;
    }
    
    /**
     * 判断用户是否拥有某个权限
     * @param object $view  当前视图
     * @param integer $per 当前权限制
     */
    public static function hasPermision($view,$per)
    {
        if(\Yii::$app->user->identity->is_sys==1)
            return true;
        $menu = static::getCurrentMenu($view);
        $user_per = static::getUserPermissionMenu(\Yii::$app->user->identity->id);     
        if($menu && count($menu)>0)
        {
            foreach($user_per as $k=>$v)
            {
                if($k==$menu['id']){
                    return ($v&$per)==$per;
                }
            }
        }
        return false;
    }
    
    /**
     * 获取用户权限按钮
     * @param object $view
     * @param array $my_buttons 用户自定义按钮
     * @return multitype:string multitype:NULL  |string
     * @return mixed
     */
    public static function getGrideViewButtons($view,$my_buttons=null,$template='{view} {update} {delete}')
    {
        $menu = static::getCurrentMenu($view);
        $templates=[];
        if(\Yii::$app->user->identity->is_sys==1)
        {
            $templates = common\enum\PermissionEnum::getPermissionViewButtons($template,$menu['permision_value'],$my_buttons);
        }else
        {
            if($menu)
            {
                //查找用户组权限
                $user_per = static::getUserPermissionMenu(\Yii::$app->user->identity->id);
                if(is_array($user_per))
                {
                    $my_per_value = 0;
                    foreach($user_per as $k=>$v)
                    {
                        if($k==$menu['id'])
                        {
                            $my_per_value = $v;
                            break;
                        }
                    }
                   $templates = common\enum\PermissionEnum::getPermissionViewButtons($template,$my_per_value,$my_buttons);
                }else {
                    $user_per=[];
                }
            }
  
        }
        $template_array = [
            'class' => 'yii\grid\ActionColumn',
            'header'=>\Yii::t('app','Operation'),
            
        ];
        $t_str = [];
        foreach($templates as $k=>$v)
        {
            if(is_object($v))
            {
                $template_array['buttons'][$k]=$v;
                $t_str[]=$k;
            }else
            {
                $t_str[]=$k;
            }
        }
        $template_array['template']=implode(array_map(function($value){
            return sprintf('{%s}',$value);
        }, $t_str), ' ');
        return $template_array;
    }
    
    /**
     * 判断用户是否拥有菜单中某个权限
     * @param integer $menu_id
     * @param integer $permission_value
     * @return boolean
     */
    public static function hasPermission($menu_id,$permission_value)
    {
        
        
    }
}

?>