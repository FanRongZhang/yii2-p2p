<?php
namespace common\service;
use yii;
use common\models\QfbFeedback;


/**
 * 反馈业务逻辑
 * @author wang
 *
 */
class FeedbackService extends BaseService
{
    /**
    *   获取反馈信息
    */
    public function getList($uid,$page,$limit) {

        $qfbfeedback = new QfbFeedback();

        $result =  $qfbfeedback->find()
                ->select(['id','title','create_time','reply'])
                ->offset(($page-1)*$limit)
                ->limit($limit)
                ->where(['=','member_id',$uid])
                ->orderBy('create_time desc')
                ->all();

       if ($result) {
            return $result;
       }else{
            return false;
       }

    }

    /**
    *   提交反馈信息
    */
    public function modifyContent($uid,$content) {

        $model = new QfbFeedback();
        $model->member_id = $uid;
        $model->content = $content;
        $model->title = mb_substr($content,0,20,'utf-8');
        $model->create_time = time();
        $result = $model->save();  

        if ($result) {
            return true;
        } else {
            return false;
        }      

    }

    /**
    *   反馈详情
    */
    public function getContent($id) {
        $model = new QfbFeedback();
        $result = $model->find()
                ->select(['id','reply','content','pid'])
                ->where(['=','id',$id])
                ->one();
        if (!empty($result) && $result->reply > 0 && !empty($id)) {
            $res = $model->find()->select('content')->where(['=','pid',$id])->one();
            $data['content'] = $result->content;
            $data['feedback'] = $res->content;
        } else {
            $data['content'] = $result->content;
            $data['feedback'] = '';
        }
        return $data;
    }

}
