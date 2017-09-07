<?php
namespace backend\controllers;

use common\service\FileUploadService;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Site controller
 */
class UploadController extends Controller
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
				        'actions' => ['upload','goodsupload','deletefile','supplierupload','brandupload'],
				        'allow' => true,
			        ]
		        ],
	        ],
        ];
    }
	/**
	 * 上传图片过滤(non-PHPdoc)
	 * @see \yii\web\Controller::beforeAction()
	 */
	public function beforeAction($action) {
		$this->enableCsrfValidation = ($action->id !== "upload" && $action->id !== "deletefile" && $action->id !== "goodsupload" && $action->id !== "supplierupload" && $action->id !== "brandupload");
		return parent::beforeAction($action);
	}

	/**
	 * 上传图片
	 */
	public function actionSupplierupload(){
		FileUploadService::uploadImage(\yii::$app->params['ftp_supplier_dir'],false);
	}

	public function actionGoodsupload()
	{		
		FileUploadService::uploadImage(\yii::$app->params['ftp_product_dir'],true);
	}

	public function actionBrandupload()
	{
		FileUploadService::uploadImage(\yii::$app->params['ftp_brand_dir'], false);
	}

	public function actionDeletefile()
	{
		FileUploadService::deleteFile(\yii::$app->params['images']);
	}
}
