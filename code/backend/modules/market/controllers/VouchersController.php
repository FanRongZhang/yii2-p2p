<?php

namespace backend\modules\market\controllers;

use Yii;
use common\models\QfbVouchers;
use backend\modules\market\models\VouchersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\enum\LevelEnum;
use yii\filters\AccessControl;
use common\models\QfbMemberVouchers;
use yii\data\ActiveDataProvider;
use common\models\QfbMemberInfo;

/**
 * VouchersController implements the CRUD actions for QfbVouchers model.
 */
class VouchersController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all QfbVouchers models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VouchersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbVouchers model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($vouchers_id = 0)
    {

        $model = new QfbMemberVouchers();
        $params = Yii::$app->request->queryParams;
        
        if(isset($params['vouchers_id']) ){
            $model->vouchers_id = $vouchers_id;
        }else{
            $vouchers_id = $params['QfbMemberVouchers']['vouchers_id'];
        }
        
        $model->load($params);
        
        
        $query = QfbMemberVouchers::find();
        $query->joinWith(['member.memberInfo']);
        
        $query->where(['vouchers_id' =>$vouchers_id ]);
        if (isset($params['QfbMemberVouchers'])) {
            
            $model->receive_time = empty($params['QfbMemberVouchers']['receive_time']) ? null : strtotime($params['QfbMemberVouchers']['receive_time']);
            $model->receive_time_end = empty($params['QfbMemberVouchers']['receive_time_end']) ? null : strtotime($params['QfbMemberVouchers']['receive_time_end']);
            $model->realname = empty($params['QfbMemberVouchers']['realname']) ? null : $params['QfbMemberVouchers']['realname'];
            $model->accout = isset($params['QfbMemberVouchers']['accout']) ? $params['QfbMemberVouchers']['accout'] : null;
            
            if ($model->status == 0){
                $query->andFilterWhere([ 'status' => $model->status])
                    ->andFilterWhere(['>', 'invalid_time', time()]);
            }elseif ($model->status == 1){
                $query->andFilterWhere([ 'status' => $model->status]);
            }elseif ($model->status == 2){
                $query->andFilterWhere([ 'status' => 0])
                ->andFilterWhere(['<', 'invalid_time', time()]);
            }
        
            $query->andFilterWhere(['like', 'member.account', $model->accout])
            ->andFilterWhere(['>', 'receive_time', $model->receive_time ])
            ->andFilterWhere(['<', 'receive_time', $model->receive_time_end])
            ->andFilterWhere(['like', QfbMemberInfo::tableName().'.realname', $model->realname]);
        
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        return $this->render('view', [
            'searchModel' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new QfbVouchers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbVouchers();

        $post = Yii::$app->request->post();
        if ($post){
            if ($post['QfbVouchers']['use_members'][0] == 'all'){
                unset($post['QfbVouchers']['use_members'][0]);
            }
            $post['QfbVouchers']['use_type'] = 2;
            $post['QfbVouchers']['use_members'] = implode(',', $post['QfbVouchers']['use_members']);
            $post['QfbVouchers']['create_time'] = time();
            $post['QfbVouchers']['start_time'] = strtotime($post['QfbVouchers']['start_time']);
            $post['QfbVouchers']['end_time'] = strtotime($post['QfbVouchers']['end_time']);
        }
        
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            
            $members = [ 'all'=>'全部会员'];
            foreach (LevelEnum::getName() as $key=>$val){
                $members[$key] = $val;
            }
            
            return $this->render('create', [
                'model' => $model,
                'members' => $members,
            ]);
        }
    }

    /**
     * Updates an existing QfbVouchers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id,$status)
    {
        $model = $this->findModel($id);

        $post['QfbVouchers']['status'] = $status ? 0 : 1;
        
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['index' ]);
        }
    }

    /**
     * Deletes an existing QfbVouchers model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }*/

    /**
     * Finds the QfbVouchers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbVouchers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbVouchers::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
