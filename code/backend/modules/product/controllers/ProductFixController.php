<?php

namespace backend\modules\product\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\service\AssetService;
use common\service\OrderService;
use yii\web\NotFoundHttpException;
use backend\modules\product\models\ProductFixSearch;
use common\models\QfbProductAgreement;
use common\models\QfbPlatformIncome;
use common\models\QfbProfitSettings;
use common\models\QfbProductDetail;
use common\models\QfbProCategory;
use common\models\QfbMemberInfo;
use common\models\QfbAgreement;
use common\models\QfbOrderFix;
use common\models\QfbProduct;
use common\models\QfbMember;
use common\models\QfbBank;
use common\models\QfbWarranty;
use League\Flysystem\Exception;

/**
 * ProductFixController implements the CRUD actions for QfbProduct model.
 */
class ProductFixController extends Controller
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
     *富文本
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload-imperavi' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                'fileparam' => 'file',
                'responseUrlParam'=> 'filelink',
                'multiple' => false,
                'disableCsrf' => true
            ],
        ];
    }
    /**
     * Lists all QfbProduct models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductFixSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QfbProduct model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        //协议
        $agree = $this->findAgreeModel($id);

        $agr = array();
        foreach($agree as $k => $v) {
            $agr[$k] = $v['title'];
        }
        $ag_str = implode(',', $agr);
        $model = $this->findModel($id);
        
        //获取保证信息
        $warranty = QfbWarranty::find()->where(['product_id'=>$model->id])->asArray()->one();
        // echo "<pre>";
        // var_dump($warranty);die;
        //用户相关信息
        $res = QfbMemberInfo::find()->select(['card_no'])->where(['member_id' => $model->member_id])->asArray()->one();
        $member = QfbMember::find()->select(['account'])->where(['id' => $model->member_id])->asArray()->one();
        $bank = QfbBank::find()->select(['name', 'no', 'username'])->where(['member_id' => $model->member_id])->asArray()->one();
        $data = [
            'account' => $member['account'],
            'card_no' => $res['card_no'],
            'bankname' => $bank['name'],
            'bankno' => $bank['no'],
            'username' => $bank['username'],
        ];
        return $this->render('view', [
            'model' => $model,
            'detailmodel' => $this->findDetailModel($id),
            'ag_str' => $ag_str,
            'data' => $data,
            'warranty' => $warranty,
        ]);
    }

    /**
     * Creates a new QfbProduct model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->post();
        //查询分类
        $category = QfbProCategory::find()->where(['=','is_down',0])->all();
        $model = new QfbProduct();
        $profitmodel = new QfbProfitSettings();
        $detailmodel = new QfbProductDetail();
        $agreemodel = new QfbProductAgreement();
        $warranty = new QfbWarranty();
        // var_dump($params);die;
        //查询协议
        $agreements = QfbAgreement::find()->select(['id','title'])->where(['in','type',[1,2]])->andWhere(['=','is_del',0])->asArray()->all();
        //生成随机产品编号
        $service = new OrderService();
        $code = $service->random_numbers(6);
        $model->sn = 'Dq'.$code;
        //修改状态为已创建
        $model->status = 0;
        $model->create_time = time();

        //产品表插入成功再插入其他表
        if ($model->load($params)) {
            $model->end_time = strtotime($params['QfbProduct']['end_time']);
            if ($model->save()) {
                $profitmodel->product_id = $model->id;
                $profitmodel->product_type = $model->product_type;
                $profitmodel->direct_rate = Yii::$app->params['share-profit']['direct_rate'];
                $profitmodel->indirect_rate = Yii::$app->params['share-profit']['indirect_rate'];
                $profitmodel->share_rate = Yii::$app->params['share-profit']['share_rate'];
                $profitmodel->province_rate = Yii::$app->params['manage-rate']['province_rate'];
                $profitmodel->city_rate = Yii::$app->params['manage-rate']['city_rate'];
                $profitmodel->area_rate = Yii::$app->params['manage-rate']['area_rate'];
                $profitmodel->agent_rate = $params['QfbProfitSettings']['agent_rate'];
                $profitmodel->load($params);
                $detailmodel->product_id = $model->id;
                $detailmodel->load($params);

                $warrantyAttribures['product_id'] = $model->id;
                $warrantyAttribures['plate_number'] = isset($params['QfbWarranty']['plate_number']) ? $params['QfbWarranty']['plate_number'] : "";
                $warrantyAttribures['model'] = isset($params['QfbWarranty']['model']) ? $params['QfbWarranty']['model'] : "";
                $warrantyAttribures['engine_number'] = isset($params['QfbWarranty']['engine_number']) ? $params['QfbWarranty']['engine_number'] : "";
                $warrantyAttribures['vin'] = isset($params['QfbWarranty']['vin']) ? $params['QfbWarranty']['vin'] : "";
                $warrantyAttribures['contract_number'] = isset($params['QfbWarranty']['contract_number']) ? $params['QfbWarranty']['contract_number'] : "";
                $warrantyAttribures['warrantor'] = isset($params['QfbWarranty']['warrantor']) ? $params['QfbWarranty']['warrantor'] : "";
                $warrantyAttribures['id_card'] = isset($params['QfbWarranty']['id_card']) ? $params['QfbWarranty']['id_card'] : "";
                $warrantyAttribures['mobile'] = isset($params['QfbWarranty']['mobile']) ? $params['QfbWarranty']['mobile'] : "";
                $warrantyAttribures['guarantee_way'] = isset($params['QfbWarranty']['guarantee_way']) ? $params['QfbWarranty']['guarantee_way'] : 0;
                $warranty->attributes = $warrantyAttribures;
                if (!$warranty->validate()) {
                    $warranty->errors;
                }

                if ($params['QfbProductAgreement']['agreement_id']) {
                    foreach ($params['QfbProductAgreement']['agreement_id'] as $v) {
                        $agreemodel = new QfbProductAgreement();
                        $agreemodel->product_id = $model->id;
                        $agreemodel->agreement_id = $v;
                        $agreemodel->save();
                    }
                }
            }
        }

        if ($profitmodel->save() && $detailmodel->save() && $warranty->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->platform_income = 0;

            return $this->render('create', [
                'model' => $model,
                'profitmodel' => $profitmodel,
                'detailmodel' => $detailmodel,
                'agreemodel' => $agreemodel,
                'agreements' => $agreements,
                'warranty' => $warranty,
                'category' => $category
            ]);
        }
    }

    /**
     * Updates an existing QfbProduct model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $params = Yii::$app->request->post();
        //查询分类
        $category = QfbProCategory::find()->where(['=','is_down',0])->all();
        $model = $this->findModel($id);
        $profitmodel = $this->findProfitModel($id);
        $detailmodel = $this->findDetailModel($id);
        $agreemodel = $this->findAgreeModel($id);
        $warranty = $this->findwarrantyModel($id);

        //查询协议
        $agree = QfbAgreement::find()->select(['qfb_agreement.id','title'])->joinWith("product_agreement") ->where(['qfb_product_agreement.product_id'=>$id,'qfb_agreement.is_del'=>0])->andWhere(['in','type',[1,2]])->asArray()->all();
        $agrees = QfbAgreement::find()->select(['id','title'])->where(['in','type',[1,2]])->andWhere(['=','is_del',0])->asArray()->all();
        //合并需要合并的俩个数组
        $agreements = array_merge($agree,$agrees);
        //去重条件
        $key = 'id';
        $tmp_arr = array();
        foreach($agreements as $k => $v) {
            //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            if(in_array($v[$key], $tmp_arr)) {
                //删除掉数组里相同ID的数组
                unset($agreements[$k]);
            } else {
                //记录已有的id
                $tmp_arr[] = $v[$key];
            }
        }

        if (!empty($params)) {
            $model->load(['QfbProduct'=>$params['QfbProduct']]);
            //把正常时间转成时间戳
            $model->end_time = strtotime($params['QfbProduct']['end_time']);

            if ($model->save()) {
                $profitmodel->load($params);
                $profitmodel->recommond_rate = $params['QfbProfitSettings']['recommond_rate'];
                $profitmodel->manage_rate = $params['QfbProfitSettings']['manage_rate'];
                $profitmodel->agent_rate = $params['QfbProfitSettings']['agent_rate'];
                $detailmodel->load($params);
                $detailmodel->content = $params['QfbProductDetail']['content'];
                $detailmodel->detail = $params['QfbProductDetail']['detail'];
                $agreemodel = new QfbProductAgreement();
                $agreemodel->deleteAll(['product_id'=>$id]);

                $warrantyAttribures['product_id'] = $model->id;
                $warrantyAttribures['plate_number'] = isset($params['QfbWarranty']['plate_number']) ? $params['QfbWarranty']['plate_number'] : "";
                $warrantyAttribures['model'] = isset($params['QfbWarranty']['model']) ? $params['QfbWarranty']['model'] : "";
                $warrantyAttribures['engine_number'] = isset($params['QfbWarranty']['engine_number']) ? $params['QfbWarranty']['engine_number'] : "";
                $warrantyAttribures['vin'] = isset($params['QfbWarranty']['vin']) ? $params['QfbWarranty']['vin'] : "";
                $warrantyAttribures['contract_number'] = isset($params['QfbWarranty']['contract_number']) ? $params['QfbWarranty']['contract_number'] : "";
                $warrantyAttribures['warrantor'] = isset($params['QfbWarranty']['warrantor']) ? $params['QfbWarranty']['warrantor'] : "";
                $warrantyAttribures['id_card'] = isset($params['QfbWarranty']['id_card']) ? $params['QfbWarranty']['id_card'] : "";
                $warrantyAttribures['mobile'] = isset($params['QfbWarranty']['mobile']) ? $params['QfbWarranty']['mobile'] : "";
                $warrantyAttribures['guarantee_way'] = isset($params['QfbWarranty']['guarantee_way']) ? $params['QfbWarranty']['guarantee_way'] : 0;
                $warranty->attributes = $warrantyAttribures;

                if (!$warranty->validate()) {
                    $warranty->errors;
                }

                if (isset($params['QfbProductAgreement'])&& !empty($params['QfbProductAgreement'])) {
                    $agreemodel = new QfbProductAgreement();
                    foreach ($params['QfbProductAgreement']['agreement_id'] as $v) {
                        $agreemodel = new QfbProductAgreement();
                        $agreemodel->product_id = $model->id;
                        $agreemodel->agreement_id = $v;
                        $agreemodel->save();
                    }
                }
                if ($profitmodel->save() && $detailmodel->save() && $warranty->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }else{
                var_dump($model->errors);die;
            }

        } else {
            return $this->render('update', [
                'model' => $model,
                'profitmodel' => $profitmodel,
                'detailmodel' => $detailmodel,
                'category' => $category,
                'agreemodel' => $agreemodel,
                'agreements' => $agreements,
                'warranty' => $warranty,
            ]);
        }
    }

    /**
     * 放款逻辑
     * @author lijunwei <lijunwei@qq.com>
     */
    public function actionLoan($id)
    {
        $productinfo = $this->findModel($id);

        $order_fix_count = QfbOrderFix::find()->where(['product_id'=>$id, 'status'=>'0'])->count();

        // 判断是否超标
        if($productinfo->has_money > $productinfo->stock_money )
            throw new NotFoundHttpException('已超出标的金额，请核实');

        if($productinfo->status != 2 || $order_fix_count > 0)
            throw new NotFoundHttpException('标的未售罄或已放款');

        // if($productinfo->end_time < time())
        //     throw new NotFoundHttpException('标的已过期');

        $params['sn'] = $productinfo->sn;

        $tran = \Yii::$app->db->beginTransaction();

        try{

            // 获取标的所有订单总额
            $order_fix = QfbOrderFix::find()->select('sum(money) as total_money ')->where(['product_id'=>$id, 'status'=>'2'])->asArray()->one();

            // 没有投资订单
            if(empty($order_fix['total_money']))
                throw new Exception('不存在投资订单');

            // 放款总额
            $productinfo->total_credit_money = $order_fix['total_money'];
            // 已放款
            $productinfo->actual_credit_money = 0;
            // 更新订单放款时间
            $productinfo->credit_time = time();
            $productinfo->status = 5;

            if(!$productinfo->save())
                throw new Exception('标识产品放款操作有误');

            // 更改放款中
            $update_arr['option_status'] = 10;
            $update_arr['option_time'] = time();

            $update_result = QfbOrderFix::updateAll($update_arr, ['product_id'=>$id, 'status'=>'2']);

            if(empty($update_result))
                throw new Exception('标识产品订单放款操作有误');

            $tran->commit();
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Exception $e) {

            $tran->rollback();
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * Deletes an existing QfbProduct model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    //获取用户信息
    public function actionMemberinfo()
    {
        // 账号
        $account = Yii::$app->request->post('account');

       
        $member = QfbMember::find()->select(['member_type', 'id'])->where(['account' => $account])->asArray()->one();
        $member_id = $member['id'];

        $res = QfbMemberInfo::find()->select(['card_no', 'is_verify'])->where(['member_id' => $member_id])->asArray()->one();
        if ($member['member_type'] != 2) {  //借款人才可以创建产品（标的）
            echo json_encode(['status'=>'error', 'message'=>'不是借款人，请先成为借款人' ,'res'=>'']);
            exit;
        }
        if (isset($res) && !empty($res)) {
            if ($res['is_verify'] == 0) {   //创建产品（标的）之前必须先实名制
                echo json_encode(['status'=>'error', 'message'=>'还未实名认证，请先认证' ,'res'=>'']);
                exit;
            }

            $bank = QfbBank::find()->select(['name', 'no', 'username'])->where(['member_id' => $member_id])->asArray()->one();
            $data = [
                'member_id' => $member_id,
                'username' => $bank['username'],
                'card_no' => $res['card_no'],
                'bankname' => $bank['name'],
                'bankno' => $bank['no'],
            ];
            echo json_encode(['status'=>'success', 'message'=>'查询成功' ,'res'=>$data]);
            exit;
        } else {
            echo json_encode(['status'=>'error', 'message'=>'账号有误，查询失败' ,'res'=>'']);
            exit;
        }

    }


    //关闭
    public function actionOff(){
        $id = Yii::$app->request->get('id');
        $model = $this->findModel($id);
        $model->status = 4;
        $model->save();
        $this->redirect(['index']);
    }

    //发布
    public function actionOk(){

        $id = Yii::$app->request->get('id');

        $model = $this->findModel($id);

        // 创建标的
        $service = new AssetService;
        $res = $service->createByProject($model);

        $data = json_decode($res['data']);
        
        if (trim(strtoupper($res['status'])) == 'SUCCESS' && trim(strtoupper($data->status)) == 'SUCCESS' || mb_substr($data->errorMessage, 0, 5, 'utf-8') == '标的已存在') {
            //募集中的活期产品只能有一个
            if ($model->product_type === 1) {
                $beforemodel = QfbProduct::find()->where(['product_type'=>1,'status'=>1])->one();
                if (!empty($beforemodel)) {
                    $beforemodel->status = 0;
                    $beforemodel->save();
                }
            }
            $model->status = 1;
            $model->start_time = time();
            $model->save();

            $this->redirect(['index']);
        } else {
            throw new \yii\web\UnauthorizedHttpException($data->errorMessage);
        }
    }

    /**
     * Finds the QfbProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbProduct::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Finds the QfbProfitSettings model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbProfitSettings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findProfitModel ($id) {
        if (($model = QfbProfitSettings::find()->where(['=','product_id',$id])->one()) !== null) {
            return $model;
        }
    }

    /**
     * Finds the QfbProductDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbProductDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findDetailModel ($id) {
        if (($model = QfbProductDetail::find()->where(['=','product_id',$id])->one()) !== null) {
            return $model;
        }
    }

    /**
     * Finds the QfbProductDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbProductDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findAgreeModel ($id) {
        if (($model = QfbAgreement::find()->joinWith("product_agreement")
                ->where(['=','qfb_product_agreement.product_id',$id])->all()) !== null
        ) {
            return $model;
        }
    }

    /**
     * Finds the QfbProductDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbProductDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findwarrantyModel ($id) {
        if (($model = QfbWarranty::find()->where(['=','product_id',$id])->one()) !== null) {
            return $model;
        }
    }
}
