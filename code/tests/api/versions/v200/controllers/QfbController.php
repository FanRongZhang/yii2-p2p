<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/8
 * Time: 10:53
 */
namespace api\versions\v200\controllers;

use yii;
use api\common\helpers\ReseponseCode as Code;
use api\common\BaseController;
use common\models\QfbShare;
use common\service\MoneyLogService;
use common\service\ApiService;
class QfbController extends BaseController
{
    public function actionList()
    {
        $params = $this->getParams();
        $service = new MoneyLogService($this->member_id);
        $data=$service->getList($params['page'],$params['limit']);
        return ApiService::send(Code::HTTP_OK,'',$data);
    }


    public function actionShare()
    {
        $params = $this->getParams();
        if (!($params['type']))
        {
            return [
                'code' => Code::COMMON_ERROR_CODE,
                'msg' => '参数缺失！'
            ];
        }
        $shareModel = new QfbShare();
        $data = $shareModel->find()->select('title,content,pic_url,url')->where(['=','type',$params['type']])->one();
        if (count($data) > 0)
        {
            $data['pic_url']	= Yii::$app->params['img_domain'].'/'.$data['pic_url'];

            return [
                'code' => Code::HTTP_OK,
                'msg' => 'success',
                'data' => $data
            ];
        }
        else
        {
            return [
                'code' => Code::HTTP_OK,
                'msg' => 'success',
                'data' => (object)[]
            ];
        }
    }

}
