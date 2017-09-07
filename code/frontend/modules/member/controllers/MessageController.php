<?php

namespace frontend\modules\member\controllers;

use common\models\QfbNotice;
use common\service\MessageService;
use common\service\MongoService\MessageService as MongoMessageService;
use yii\data\Pagination;


class MessageController extends BaseController
{
    /**
     * 会员中心--消息中心
     * @return array
     */
    public function actionIndex()
    {
        $service = new MongoMessageService($this->mid);
        $condition = [
            'member_id' => (int)$this->mid,
        ];

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $service->findCount($condition),
        ]);

        $result_limit = [
            'sort' => array('_id' => -1),
            'limit' => $pagination->limit,
            'start' => $pagination->offset,
        ];

        $list = $service->findList($condition, $result_limit, ['news_id', 'is_read', 'create_time']);
        if (count($list) != 0) {
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
        }else{
            $list = [];
        }

        return $this->render('index', [
            'model' => array_values($list),
            'pagination' => $pagination,
        ]);
    }


    public function actionDetail()
    {
        $id = $this->get('id', 0);
        $NewsService = new MessageService();
        $service = new MongoMessageService($this->mid);
        $condition = ['news_id'=>(int)$id, 'member_id'=>(int)$this->mid];
        $model = $service->findOne($condition);
        if ($model && $model['is_read'] == 0) {
            $service->read(['_id' => $model['_id']]);
        }

         $new = $NewsService->getOneById($id);

        if (empty($new)){
            return $this->error('消息不存在');
        }else{
            return $this->render('detail', ['data'=>$new]);
        }
    }
}