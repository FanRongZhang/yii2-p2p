<?php
namespace common\service;
use common\models\Menu;

/**
 * 菜单处理服务类
 * @author Ben
 *
 */
class MenuService extends BaseService
{
    /**
     * 用户id
     * @var integer
     */
    private $_user_id;
        
    /**
     * 最终菜单数据
     * @var array
     */
    private $_menus = [];
    
    /**
     * 父id 标识
     * @var string
     */
    private $_parent_id = 'parent_id';
    
    /**
     * 是否管理员
     * @var boolean
     */
    private $_is_administrator = false;
    
    /**
     * 子菜单标识
     * @var string
     */
    private $_children = 'children';
    
    /**
     * 用户组菜单
     * @var array
     */
    private $_user_group_menus = [];
    
    function __construct($user_id=null,$is_administrator=null,$parent_id='parent_id',$children='children'){
        $this->_is_administrator=$is_administrator==null?\Yii::$app->user->identity->is_sys==1:$is_administrator;
        $this->_user_id=$user_id==null?\Yii::$app->user->identity->id:$user_id;
        $this->_parent_id = $parent_id;
        $this->_children=$children;
        $this->_init();
    }
    
    /**
     * 初始化
     */
    function _init()
    {
        if(!$this->_is_administrator)
            $this->_user_group_menus = AdminService::getUserPermissionMenu($this->_user_id);
        $menus = $this->recursiveMenu(Menu::find()->orderBy(['sorts'=>SORT_ASC])->asArray()->all());
        $this->_menus=\Yii::$app->session->get('user_menu');
        if(!isset($this->_menus)||empty($this->_menus) || count($this->_menus)==0)
        {
            $this->_menus = $this->_is_administrator?$menus:$this->recursiveUserMenu($menus);
            \Yii::$app->session->set('user_menu',$this->_menus);
        }
    }

    /**
     * 递归合并用户菜单
     */
    function recursiveUserMenu($menus)
    {
        $m = [];
        foreach($menus as $k=>$v){
            $m[$k] = $v;
            if(isset($v[$this->_children])
                &&count($v[$this->_children])>0)
            {
                $m[$k][$this->_children]=$this->recursiveUserMenu($v[$this->_children]);
                if(!isset($m[$k][$this->_children])||count($m[$k][$this->_children])==0)
                {
                    if($v[$this->_parent_id]==0)
                        unset($m[$k]);
                    else 
                        unset($m[$k][$this->_children]);
                }                     
            }else 
            {
                if(array_key_exists($v['id'], $this->_user_group_menus))
                {
                    $m[$k] = $v;
                    $m[$k]['per']=$this->_user_group_menus[$v['id']];
                }else
                    unset($m[$k]);
            }
        }
        return $m;
    }
    
    /**
     * 获取菜单数据
     * @return array
     */
    public function getMenus()
    {
        return $this->_menus;
    }
    
    /**
     * 获取用户菜单
     * @param array $user_menu 用户菜单
     * @return string $menu
     */
    public function getUserMenu()
    {        
        $view_menu = $this->getViewMenu($this->_menus);
        return json_encode($view_menu);
    }

    /**
     * 递归获取所有菜单
     * @param array $menu_data 菜单数据
     * @param array $main_menu 主菜单
     * @return array
     */
    function recursiveMenu($menu_data,$main_menu=null)
    {
        if($main_menu==null)
        {
            return $this->recursiveMenu($menu_data,
                array_filter($menu_data,
                    function($val){
                        return $val[$this->_parent_id]==0;
                    }));
        }else 
        {
            foreach($main_menu as $k=>$v)
            {
                $tmp=$v;
                foreach($menu_data as $kk=>$vv)
                {
                    if($vv[$this->_parent_id]==$v['id'])
                    {
                        $tmp[$this->_children][]=$vv;
                    }
                }
                $main_menu[$k]=$tmp;
                if(isset($tmp[$this->_children])&&count($tmp[$this->_children])>0){
                    $main_menu[$k][$this->_children]= $this->recursiveMenu($menu_data,$tmp[$this->_children]);
                } 
            }
            return $main_menu;
        }
    }
    
    
    /**
     * 转换成首页需要的menu
     * @param  array $menu 菜单
     * @return array 转换后的菜单
     */
    function getViewMenu($menu)
    {
        $new_menus = [];
        $tmp=[];
        foreach($menu as $k=>$v)
        {
            if($v['display']==1){
                $tmp=[
                    'text'=>$v['name'],
                    'collapsed'=>true
                ];
                if(isset($v[$this->_children]) && !empty($v[$this->_children]))
                {
                    foreach($v[$this->_children] as $j=>$k)
                    {
                        if($k['display']==1)
                        $tmp['items'][]=[
                            'id'=>'menu_'.$k['id'],
                            'text'=>$k['name'],
                            'href'=>$k['url']
                        ];
                    }
                }
                $new_menus[]=$tmp;
            }
        }
        if(count($new_menus)>0)
            $new_menus[0]['collapsed']=false;
        return $new_menus;
    }
}

?>