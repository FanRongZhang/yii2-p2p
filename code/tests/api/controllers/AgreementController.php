<?php
namespace api\controllers;

use common\models\QfbAgreement;
use yii;
use yii\web\Controller;

/**
 * 协议h5页面控制器
 * @author steve
 */
class AgreementController extends Controller
{
    /**
     * 协议详情
     */
    public function actionIndex($id)
    {
        $data = QfbAgreement::findOne(['id'=>$id]);

        return $this->render('index',[
            'data'   => $data
        ]);
    }
}
