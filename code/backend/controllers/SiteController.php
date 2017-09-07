<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use backend\models\LoginForm;
use backend\models\Admin;
use yii\filters\VerbFilter;
use common;
use common\models\Order;
use common\service\PaymentService;
/**
 * Site controller
 */
class SiteController extends Controller
{
    public $layout = 'site';
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error','test'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'return-money'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post','get'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * 首页
     * @return \yii\web\Response|string
     */
    public function actionIndex()
    {
        //去掉布局
        $this->layout='menu';
        return $this->render('index',['menu'=>common\service\AdminService::getUserMenu()]);
    }

    /**
     * 登录
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            //修改登录后的登录ip和登录时间
            $admin =Admin::findIdentity(yii::$app->user->identity->id);
            $admin->last_login=time();
           // $admin->last_ip=yii::$app->request->userIP;
            $admin->save();
           /* yii::$app->user->identity->last_login=$admin->last_login;
            yii::$app->user->identity->last_ip=$admin->last_ip;*/
            //获取用户权限
            
            return $this->goHome();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 退出登录 
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect('/site/login');
    }

    /**
     *  奇葩店铺，下单用户手动退款程序
     */
    public function actionReturnMoney()
    {
        $sn_arr = ['dm_14472068021241','dm_14470274211689'];
        $order = Order::find()->where(['in','sn',$sn_arr])->all();
        if($order){
            foreach($order as $v){
                $res = PaymentService::backMoney($v->id);
                print_r($res);
            }
        }else{
            print_r("没有找到订单");
        }
    }
}
