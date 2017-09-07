<?php
namespace api\versions\v200\controllers;

use yii\data\ActiveDataProvider;
use api\common\BaseController;
use common\models\QfbFeedback;
use common\service\FeedbackService;
use api\common\helpers\ReseponseCode as Code;
/**
 * @author wang
 * @since 2.0
 */
class FeedbackController extends BaseController
{
    /**
    *   我的反馈
    */
    public function actionList(){        
        //用户id
        $uid = $this->member_id;
        $params = $this->getParams();
        $page = $params['page']?$params['page']:1;
        $limit = $params['limit']?$params['limit']:10;
        $service = new FeedbackService();
        $model = $service->getList($uid,$page,$limit);
        //拼接数据
        $data = array();
        if(!empty($model)) {
            foreach($model as $k => $m) {
                $data[$k]['id'] = (int)$m->id;
                $data[$k]['title'] = (string)$m->title;
                $data[$k]['time'] = (string)date("Y-m-d H:i:s",$m->create_time);
                $data[$k]['reply'] = (bool)($m->reply === 0 ? false : true);
            }
        }
        
        return [
                    'code' => Code::HTTP_OK,
                    'msg' => Code::$statusTexts[Code::HTTP_OK],
                    'data' => $data
               ];
    
    }

    /**
    *   反馈详情
    */
    public function actionContent(){

        $params = $this->getParams();
        //反馈id
        $id = $params['id'];
        if (!empty($id)) {
            $service = new FeedbackService();
            $model = $service->getContent($id);
        } 

        return [
                    'code' => Code::HTTP_OK,
                    'msg' => Code::$statusTexts[Code::HTTP_OK],
                    'data' => $model
               ];
    }


    /**
    *   提交反馈
    */
    public function actionCommit(){
        //用户id
        $uid = $this->member_id;
        $params = $this->getParams();
        $content = $params['content'];
        if(empty($content)) {
            return [
                    'code' => Code::HTTP_NO_CONTENT,
                    'msg' => Code::$statusTexts[Code::HTTP_NO_CONTENT],
                   ];
        }
        $service = new FeedbackService();
        $model = $service->modifyContent($uid,$content);

        if ($model) {
            return [
                    'code' => Code::HTTP_OK,
                    'msg' => Code::$statusTexts[Code::HTTP_OK],
                   ];
        } else {
            return [
                    'code' => Code::HTTP_CREATED,
                    'msg' => Code::$statusTexts[Code::HTTP_CREATED],
                   ];
        }
    }

}