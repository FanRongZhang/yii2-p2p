<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/22
 * Time: 15:18
 */
namespace console\controllers;

use common\models\MongodbModel;
use common\models\QfbMessage;
use common\service\MemberService;
use yii\console\Controller;

class SendmessageController extends Controller
{
    public function actionSend()
    {
        $messageModel = new QfbMessage();
        $messages = $messageModel->find()->where(['=','send_type',0])->orderBy('id desc')->one();
        if (count($messages) > 0) {
            if($messages->send_time <= time()) {
                $model = $this->findModel($messages->id);
                $member = json_decode($messages->send_ob_value, true);
                if (!count($member)) {
                    $data = [
                        'code' => 201,
                        'msg' => '没有对应级别',
                    ];
                    print_r($data);
                }
                if ($messages->send_ob === 0) {
                    foreach ($member as $v) {
                        $memberService = new MemberService();
                        $users = $memberService->findBylevel($v);
                        if ($users) {
                            foreach ($users as $vs) {
                                $table = $vs->id % 1000;                                             //根据用户id%1000表取莫分表
                                if ($vs) {
                                    $mgdb = new MongodbModel();
                                    $mgdb->insertMongo('news_' . $table,
                                        array(
                                            'member_id' => $vs->id,
                                            'news_id' => $model->id,
                                            'is_read' => 0,                                         //0未读
                                            'create_time' => (string)time(),                        //发送时间
                                        )
                                    );
                                }
                            }
                        }
                    }
                }elseif($messages->send_ob === 1) {                                                 //按会员账号 发送消息
                    foreach ($member as $v) {
                        $memberService = new MemberService();
                        $users = $memberService->findMemberByMobile($v);
                        if ($users) {
                            $table = $users->id % 1000;
                            $mgdb = new MongodbModel();
                            $mgdb->insertMongo('news_' . $table,
                                array(
                                    'member_id' => $users->id,
                                    'news_id' => $model->id,
                                    'is_read' => 0,                                         //0未读
                                    'create_time' => (string)time(),                        //发送时间
                                )
                            );
                        }
                    }
                }
                \Yii::$app->set('db', \Yii::$app->components['db']);
                $model->send_type = 1;                                                         //修改消息的发送状态
                $model->send_time = time();
                if ($model->save()) {
                    $data = array(
                        'code' => 200,
                        'msg' => '发送成功',
                    );
                    return print_r($data);
                } else {
                    var_dump($model->errors);
                }
            }else{
                $data = [
                    'code' => 201,
                    'msg' => '未到发送时间',
                ];
                return print_r($data);
            }
        }
        else
        {
            print_r(
                $data = array(
                    'code' => 201,
                    'msg' => '未查询到该消息',
                )
            );
        }
    }
    /**
     * Finds the QfbMessage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbMessage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QfbMessage::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}