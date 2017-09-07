<?php

namespace api\versions\v200\controllers;
use common\models\QfbImeiNotice;
use common\models\QfbNotice;
use common\service\MemberService;
use yii;
use api\common\BaseController;
use common\service\MessageService;
use common\service\MongoService\MessageService as MongoMessageService;
use api\common\helpers\ReseponseCode as Code;
/**
 * 首页操作
 * @author jin
 *
 */
class MessageController extends BaseController
{

	public function actionContent()
    {
        $params =  $this->getParams();
		$id = intval($params['id']);
        $type = intval($params['type']);

		if ($id <= 0 ) return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '非法操作2'];
        if ($type <= 0 ) return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '非法操作3'];
        if ($type == 1) {
            $NewsService = new MessageService();
            $new = $NewsService->getOneById($id);

            if (empty($new)) return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '消息不存在'];

            if (isset($params['access_token'])) {
                $member_service = new MemberService();
                $member_id = $member_service->findMemberIdByToken($params['access_token']);
                if (!$member_id->id) {
                    return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '非法操作'];
                }
                $service = new MongoMessageService($member_id->id);
                $condition = ['news_id' => $id, 'member_id' => $member_id->id];
                $model = $service->findOne($condition);
                if (empty($model)) return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '消息不存在'];
                if ($model && $model['is_read'] == 0) {
                    $service->read(['_id' => $model['_id']]);
                }
            }

            return [
                'code' => Code::HTTP_OK,
                'msg' => '成功',
                'data' => [
                    'content' => $NewsService->setContent($new['title'], $new['content'])
                ]
            ];
        } else {
            $noticeModel = new QfbNotice();
            $notice = $noticeModel->getOneById($id);

            return [
                'code' => Code::HTTP_OK,
                'msg' => '成功',
                'data' => [
                    'content' => $noticeModel->setContent($notice->title,$notice->content),
                ]
            ];
        }
	}


    /**
     * @消息列表
     * @参数：page页,limit 每页显示条数
     * @return mixed
     */
	public function actionList()
    {
		$params = \Yii::$app->request->get();
        $type = $params['type'];
        $noticeModel = new QfbNotice();
        $data = [];
        if (!$type) {
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '非法操作'];
        }
        /** login and un_login 只有私人消息*/
        if (isset($params['access_token'])) {
            if ($type == 2) {
                $page = $params['page']? $params['page']:1;
                $pageSize = $params['limit']? $params['page']:10;
                $offset = ($page-1)*$params['limit'];
                $result = $noticeModel->find()->select('id,title,send_time')->offset($offset)->limit($params['limit'])->orderBy('send_time desc')->all();
                foreach ($result as $k=>$v){
                    $data[$k]['id'] = $v->id;
                    $data[$k]['title'] = $v->title;
                    $data[$k]['time'] = date("Y-m-d H:i:s",$v->send_time);
                    $data[$k]['read'] = true;
                }
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => $data
                ];
            } else {
                $member_service = new MemberService();
                $member_id = $member_service->findMemberIdByToken($params['access_token']);
                if (!$member_id) {
                    return ['code' => Code::NOT_LOGIN, 'msg' => 'token失效'];
                }

                $service = new MongoMessageService($member_id->id);
                $condition = [
                    'member_id' => $member_id->id,
                ];

                $limit = $params['limit'] > 0 ? intval($params['limit']) : 20;
                $page = $params['page'] > 0 ? intval($params['page']) : 1;
                $offset = ($page - 1) * $limit;

                $result_limit = [
                    'sort' => array('_id' => -1),
                    'limit' => $limit,
                    'start' => $offset,
                ];

                $list = $service->findList($condition, $result_limit, ['news_id', 'is_read', 'create_time']);
                if (count($list) == 0) {
                    return [
                        'code' => Code::HTTP_OK,
                        'msg' => '成功',
                        'data' => []
                    ];
                }
                $MongoNewsService = new MessageService();
                foreach ($list as $key => &$value) {
                    $new = $MongoNewsService->getOneById($value['news_id']);
                    if ($new) {
                        $value['id'] = $new['id'];
                        $value['title'] = $new['title'];
                        $value['time'] = date("Y-m-d H:i:s", $value['create_time']);
                        $value['read'] = $value['is_read'] ? true : false;
                        unset($value['_id']);
                        unset($value['news_id']);
                        unset($value['is_read']);
                    } else {
                        unset($list[$key]);
                    }
                }
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => array_values($list)
                ];
            }
        }else{
            /** 2公告 即是首页消息  1私人消息*/
            if ($type == 2) {
                $page = $params['page']? $params['page']:1;
                $pageSize = $params['limit']? $params['limit']:10;
                $offset = ($page-1)*$params['limit'];
                $result = $noticeModel->find()->select('id,title,send_time')->offset($offset)->limit($params['limit'])->orderBy('send_time desc')->all();
                foreach ($result as $k=>$v) {
                    $data[$k]['id'] = $v->id;
                    $data[$k]['title'] = $v->title;
                    $data[$k]['time'] = date("Y-m-d H:i:s",$v->send_time);
                    $data[$k]['read'] = true;
                }
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => $data
                ];
            } else {
                return [
                    'code' => Code::HTTP_OK,
                    'msg' => '成功',
                    'data' => $data
                ];
            }
        }
	}

    /**
     * 首页显示的消息
     * @return array
     */
    public function actionTips()
    {
        $params = $this->getParams();
        if (!isset($params['imei'])) {
            return ['code' => Code::COMMON_ERROR_CODE, 'msg' => '确失参数'];
        }
        $imei = $params['imei'];
        $userImeiModel = new QfbImeiNotice();
        $user_imei = $userImeiModel->find()->where(['=','imei',$imei])->one();

        if (!$user_imei) {
            $userImeiModel->imei = $imei;
            $userImeiModel->notice_id = 0;
            $userImeiModel->save();
            $user_imei = $userImeiModel->find()->where(['=','imei',$imei])->one();
        }
        $noticeModel = new QfbNotice();
        $model = $noticeModel->find()->where(['>','show_end_time',time()])->orderBy('id desc')->one();
        $data = [];
        if (empty($model)) {
            return [
                "code" => Code::HTTP_OK,
                "msg" => "请求成功,没有最公告",
                'data' => (object)$data,
            ];
        }
        if ( $user_imei->notice_id == $model->id ) {
            $read = true;
        } else {
            $read = false;
        }
        if ($model) {

            $data=[
                'id' => $model->id,
                'head' => '重要公告',
                'title' => $model->title,
                'content' => $model->summary,
                'read' => $read ,
            ];
        } else {
            $data = (object)$data;
        }
        $imeiNoticeModel = QfbImeiNotice::find()->where(['=','imei',$params['imei']])->one();
        if($imeiNoticeModel) {
            $imeiNoticeModel->notice_id = $model->id;
            $imeiNoticeModel->save();
        }
        return [
            "code" => Code::HTTP_OK,
            "msg" => "请求成功",
            "data" => $data
        ];
    }
}