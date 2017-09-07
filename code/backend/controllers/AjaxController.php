<?php
namespace backend\controllers;

use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\filters\AccessControl;
use \common\models;
use yii\web\NotFoundHttpException;

/**
 * Site controller
 */
class AjaxController extends Controller
{
    
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
				        'actions' => ['cascade'],
				        'allow' => true,
			        ]
		        ],
	        ],
        ];
    }

	/**
	 * 提交过滤。非ajax不可提交
	 * @return bool|void
	 */
	public function beforeaction(){
		if(!Yii::$app->request->isAjax){
			throw new NotFoundHttpException('The requested page does not exist.');
		}else{
			return true;
		}
	}
	/**
	 * 级联
	 */
	public function actionCascade(){
		$request = Yii::$app->request;

		/**判断是否存在model */
		$model_name = 'common\\models\\'.$request->post('m');
		if(!class_exists($model_name)){
			$result['success'] = 0;
			$result['msg'] = 'Failed! The model is not exist';
		}
		/**判断是否method*/
		$method = $request->post('f');
		if(!method_exists($model_name, $method)){
			$result['success'] = 0;
			$result['msg'] = 'Failed! The method is not exist';
		}
		/**实例化 model 并且传入参数 调用方法 */
		$model = new $model_name;
		$param = $request->post('p');
		$data = $model->$method($param);
		if(!empty($data)){
			$result['success'] = 1;
			$result['data'] = $data;
			$result['msg'] = 'Success !';
		}else{
			$result['success'] = 0;
			$result['msg'] = 'Failed！The data is empty';
		}

		print Json::encode($result);exit;
	}
}
