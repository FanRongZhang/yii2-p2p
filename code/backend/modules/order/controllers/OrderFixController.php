<?php

namespace backend\modules\order\controllers;

use Yii;
use common\models\QfbOrderFix;
use backend\modules\order\models\OrderFixSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

// blake
use common\service\MemberService;

/**
 * OrderFixController implements the CRUD actions for QfbOrderFix model.
 */
class OrderFixController extends Controller
{
    public function behaviors()
    {
       return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    /*过滤不需要登录的页面[
                        'actions' => ['index'],
                        'allow' => true,
                    ],*/
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
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
     * Lists all QfbOrderFix models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderFixSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbOrderFix model.
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
     * Creates a new QfbOrderFix model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new QfbOrderFix();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing QfbOrderFix model.
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
     * Deletes an existing QfbOrderFix model.
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
     * 放款
     */
    public function actionMakeLoans()
    {

        // 借款人账户查询
        $member_id = '1a20170606162811';
        // to
        // 用户查询
        $getHkyhUser = MemberService::getHkyhUser($member_id);
        $data = json_decode($getHkyhUser['data']['data'], true);

        echo "<br />";
        echo '账户余额：'.$data['balance'].'可用余额:'.$data['availableAmount'].'冻结金额'.$data['freezeAmount'];
        exit;
        $hkyh = \Yii::$app->Hkyh;

        // 单笔交易
        $serviceName = 'SYNC_TRANSACTION';

        // 流水号
        // $sn = $this->getBindSn('UPT');
        /**
         * 一笔交易包含多个业务
         */
        // 业务类型 -区分：投标确认 还款 还代偿款 佣金 派息 平台服务费等
        $details['bizType'] = 'TENDER';
        // 用户预处理流水号 -必填
        $details['freezeRequestNo'] = 'a111111111111112';
        // 出款人用户编号 -必填
        $details['sourcePlatformUserNo'] = '4a20170606180903';
        // 收款人用户编号 -必填
        $details['targetPlatformUserNo'] = '1a20170606162811';
        // 交易金额 本息和或佣金等其他 -必填
        $details['amount'] = 10;
        // 利息 -非必填
        $details['income'] = 0;
        // 债权份额
        // $details['share'] = '';
        // 平台商户自定义参数
        // $details['customDefine'] = '';
        // 备注 -非必填
        // $details['remark'] = '';

        // 请求流水号
        $reqData['requestNo'] = '4M1L2017021825452545';
        // 交易类型 -区分：投标 还款 债权认购  直接代偿 间接代偿 独立分润 平台营销款 收费 平台资金划拨
        $reqData['tradeType'] = 'TENDER';
        // 标的号 -非必填 --如果是投标确认，应该要填
        $reqData['projectNo'] = 'Dq20170606170522ERD9XT';
        // 债权出让请求流水号
        // $reqData['saleRequestNo'] = '';
        // 业务明细
        $reqData['details'][] = $details;
        /*echo "<pre>";
        var_dump($reqData);
        exit;*/
        $result = $hkyh->createPostParam($serviceName,$reqData);
        //这里根据业务逻辑自行处理，如果是直连则根据$result数据做处理，如果是网关则不返回数据，
        $data = json_decode($result['data'], true);

        // 用户查询
        $getHkyhUser = MemberService::getHkyhUser($member_id);

        // 判断处理业务
        if(strtolower($data['status']) == 'SUCCESS'){



            echo '投标确认成功';
        }

        echo '投标确认处理错误';

        $errorCode = $data['errorCode'];
        $errorMessage = $data['errorMessage'];

        echo "<pre>";
        var_dump($result);
        exit;

    }


    /**
     * Finds the QfbOrderFix model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbOrderFix the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbOrderFix::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
