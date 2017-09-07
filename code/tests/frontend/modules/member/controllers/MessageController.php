<?php

namespace frontend\modules\member\controllers;

use common\service\MongoService\MessageService as MongoMessageService;
use yii\data\Pagination;
use frontend\controllers\WebController;


class MessageController extends WebController
{
    /**
     * 会员中心--消息中心
     * @return array
     */
    public function actionIndex()
    {
        $this->mid = 65;
        $service = new MongoMessageService($this->mid);
        $condition = [
            'member_id' => $this->mid,
        ];
        $pagination = new Pagination([
            'defaultPageSize' => 3,
            'totalCount' => $service->findCount($condition),
        ]);

        $result_limit = [
            'sort' => array('_id' => -1),
        ];
        $list = $service->findList($condition, $result_limit, ['news_id', 'is_read', 'create_time']);var_dump($list);exit;

        return $this->render('index', [
            'model' => $list,
            'pagination' => $pagination,
        ]);
    }
}