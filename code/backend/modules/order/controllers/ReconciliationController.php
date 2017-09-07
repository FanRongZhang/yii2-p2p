<?php

namespace backend\modules\order\controllers;

use Yii;
use common\models\QfbReconciliationLog;
use common\models\QfbTemporaryReconciliation;
use backend\modules\order\models\ReconciliationSearch;
use backend\modules\order\models\TemporaryReconciliationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ReconciliationController implements the CRUD actions for QfbReconciliationLog model.
 */
class ReconciliationController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['get'],
                ],
            ],
        ];
    }

    /**
     * Lists all QfbReconciliationLog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ReconciliationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all QfbReconciliationLog models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new TemporaryReconciliationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $file_type_arr = ['0'=>'充值', '1'=>'提现', '2'=>'交易', '3'=>'佣金'];

        $file_name = date("Ymd", (time()-24*3600*1));

        $data = QfbTemporaryReconciliation::find()->where('file_name=:file_name',[':file_name'=>$file_name])->asArray()->all();

        $msg = '';
        foreach ($file_type_arr as $ft_key=>$ft_val) {

            $status = false;

            foreach($data as $d_key=>$d_val){

                // 非同一类型
                if($d_val['file_type'] != $ft_key)
                    continue;

                $status = true;

                if ($d_val['end_time'] <= 0)
                    $msg.= $file_type_arr[$ft_key].'对账脚本未执行完成 &nbsp;&nbsp;&nbsp;';


                if ($d_val['status'] == 1)
                    $msg.= $file_type_arr[$ft_key].'文件对账异常 &nbsp;&nbsp;&nbsp;';

                if ($d_val['status'] == 2 && $d_val['status'] == '0')
                    $msg.= $file_name.'未提交银行对账确认 &nbsp;&nbsp;&nbsp;';

                if ($d_val['status'] == 2 && $d_val['status'] == '1')
                    $msg.= $file_name.'提交银行对账确认失败 &nbsp;&nbsp;&nbsp;';
            }

            // 未执行业务对账
            if($status != true)
                $msg.= $file_type_arr[$ft_key].' 文件对账未执行 &nbsp;&nbsp;&nbsp;';
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'msg' => $msg
        ]);
    }


    /**
     * Displays a single QfbReconciliationLog model.
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
     * Creates a new QfbReconciliationLog model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbReconciliationLog();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbReconciliationLog model.
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

    public function actionDel()
    {
        $id = Yii::$app->request->get('re_id');
        // var_dump($id);die;
        $count= QfbTemporaryReconciliation::findOne($id)->delete();
        // $count= QfbTemporaryReconciliation::model()->deleteAll('id=:id',array(':id'=>$id));

        return $this->redirect(['list']);
    }

    /**
     * Deletes an existing QfbReconciliationLog model.
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
     * Finds the QfbReconciliationLog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbReconciliationLog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbReconciliationLog::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
