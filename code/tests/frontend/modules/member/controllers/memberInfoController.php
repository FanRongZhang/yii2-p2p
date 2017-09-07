<?php

namespace frontend\modules\member\controllers;

use frontend\models\MemberInfo;
use common\service\MemberInfoService;
use common\service\MemberService;
use frontend\controllers\WebController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use common\service\CommonService;

class MemberInfoController extends WebController
{
//    public function behaviors()
//    {
//        return [
//            'access' => [
//                'class' => AccessControl::className(),
//                'rules' => [
//                    [
//                        'allow' => true,
//                        'roles' => ['@']
//                    ]
//                ]
//            ],
//            'verbs' => [
//                'class' => VerbFilter::className(),
//                'actions' => [
//                    'delete' => ['post'],
//                ],
//            ],
//        ];
//    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'upload' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                'deleteRoute' => 'upload-delete',
            ],
            'upload-delete' => [
                'class' => 'trntv\filekit\actions\DeleteAction'
            ],
        ];
    }

    /**
     * 会员中心--基本信息
     * @return string
     */
    public function actionIndex()
    {
        $this->mid = 65;
        $memberService = new MemberService();
        $memberInfo = $memberService->getMemberInfo($this->mid);

        return $this->render('index', ['memberInfo'=>$memberInfo]);
    }

    public function actionUpdate($member_id)
    {
        $model = $this->findModel($member_id);

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 更新密码
     */
    public function actionMemberPassword()
    {
        return $this->render('password');
    }


    /**
     * ---------------------------------------
     * 忘记密码
     * @author lijunwei
     * ---------------------------------------
     */
    public function actionResetPassword()
    {
        $params = $this->post('UMember');
        foreach ($params as $value) {
            if ($value == null) {
                $this->error('参数缺失!');
            }
        }

        $memberService = new MemberService();
        $mobile = $memberService->findMemberByMobile($params);
        if (!$mobile) {
            $this->error('手机账号不存在!');
        }

        $member_id = $mobile->id;
        if (!CommonService::checkVerify($params['code'], $params['mobile'],CommonService::VERIFY_TYPE_FORGET)) {
            $this->error('验证码错误!');
        }
        $member = new MemberService();
        $result = $member->updateLoginPassword($member_id,$params);
        if ($result) {
            return $this->redirect(['/login/login/login']);
            // $this->success('修改成功!');
        } else {
            $this->error('修改失败!');
        }
    }


    /**
     * Finds the QfbAgreement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QfbMemberInfo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MemberInfo::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


}
