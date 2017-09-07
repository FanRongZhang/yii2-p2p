<?php

namespace backend\modules\member\controllers;

use Yii;
use common\models\QfbMember;
use backend\modules\member\models\Meminfosearch;
use backend\modules\member\models\MemberVoucherSearch;
use common\models\QfbMemberVouchers;
use common\models\QfbMemberMoney;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MeminfoController implements the CRUD actions for QfbMember model.
 */
class MeminfoController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all QfbMember models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new Meminfosearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbMember model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new QfbMember model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbMember();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbMember model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

     /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * 一度人脉
     */
    public function actionOneContacts($id) {
        $this->layout="//member";
        //一度人脉的数量
        $direct_count = QfbMember::find()->where(['r_member_id'=>$id])->count();
        //活期在投总额、定期在投总额
        $sql = "select sum(live_money+pre_live_money) as live_money,sum(fix_money) as fix_money from qfb_member_money where member_id in (select id from qfb_member where r_member_id = {$id})";
        $live_sum = Yii::$app->db->createCommand($sql)->queryOne();
        //活期贡献总分润、定期贡献总分润
        $sql = "select sum(money) as money from qfb_money_detail where money_type = 1 and member_id = {$id} and from_member_id in (select id from qfb_member where r_member_id = {$id})";
        $live_profit = Yii::$app->db->createCommand($sql)->queryOne();
        $sql = "select sum(money) as money from qfb_money_detail where money_type = 2 and member_id = {$id} and from_member_id in (select id from qfb_member where r_member_id = {$id})";
        $fix_profit = Yii::$app->db->createCommand($sql)->queryOne();

        $searchModel =new MeminfoSearch();
        $dataProvider = $searchModel->search($id);

        return $this->render('one-contacts', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'direct_count' => $direct_count,
            'live_sum' => $live_sum,
            'live_profit' => $live_profit,
            'fix_profit' => $fix_profit
        ]);
    }

     /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * 二度人脉
     */
    public function actionTwoContacts($id) {
        $this->layout="//member";
        $searchModel = new MeminfoSearch();
        //查询一度人脉
        $ones = array();
        $one_con = QfbMember::find()->select('id')->where(['=','r_member_id',$id])->all();
        if (!empty($one_con)) {
           foreach ($one_con as $k => $one) {
                $ones[$k] = $one->id; 
           }
           $dataProvider = $searchModel->search($ones);
                
        } else {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }
        //二度人脉数量
        $sql = "select count(id) as count from qfb_member where r_member_id in (select id from qfb_member where r_member_id = {$id})";
        $count = Yii::$app->db->createCommand($sql)->queryOne();
        //活期在投总额、定期在投总额
        $sql = "select sum(live_money+pre_live_money) as live_money,sum(fix_money) as fix_money from qfb_member_money where member_id in (select count(id) as count from qfb_member where r_member_id in (select id from qfb_member where r_member_id = {$id}))";
        $live_sum = Yii::$app->db->createCommand($sql)->queryOne();
        //活期贡献总分润、定期贡献总分润
        $sql = "select sum(money) as money from qfb_money_detail where money_type = 1 and member_id = {$id} and from_member_id in (select count(id) as count from qfb_member where r_member_id in (select id from qfb_member where r_member_id = {$id}))";
        $live_profit = Yii::$app->db->createCommand($sql)->queryOne();
        $sql = "select sum(money) as money from qfb_money_detail where money_type = 2 and member_id = {$id} and from_member_id in (select count(id) as count from qfb_member where r_member_id in (select id from qfb_member where r_member_id = {$id}))";
        $fix_profit = Yii::$app->db->createCommand($sql)->queryOne();

        return $this->render('two-contacts', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'count' => $count,
            'live_sum' => $live_sum,
            'live_profit' => $live_profit,
            'fix_profit' => $fix_profit
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * 代金券明细
     */
    public function actionVoucher($id) {
        $this->layout="//member";
        //查询未使用的代金券数量
        $no_use_count = QfbMemberVouchers::find()->where(['member_id'=>$id,'status'=>0])->count();
        $searchModel = new MemberVoucherSearch();
        $dataProvider = $searchModel->search($id);

        return $this->render('voucher', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'no_use_count' => $no_use_count
        ]);
    }

    /**
     * Deletes an existing QfbMember model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the QfbMember model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbMember the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbMember::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
