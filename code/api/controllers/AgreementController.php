<?php
namespace api\controllers;

use yii;
use mPDF;
use yii\web\Controller;
use api\common\BaseController;
use common\models\QfbBank;
use common\models\QfbMember;
use common\models\QfbProduct;
use common\models\QfbOrderFix;
use common\models\QfbWarranty;
use common\models\QfbAgreement;
use common\models\QfbOrderRepayment;
use common\helpers\AdCommon;

/**
 * 协议h5页面控制器
 * @author steve
 */
class AgreementController extends Controller
{
    /**
     * 协议详情
     */
    public function actionIndex()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;

        if(Yii::$app->request->isGet){
            $params = Yii::$app->request->get();
            $member_id = (int)trim($params['member_id']);
            $id = (int)trim($params['product_id']);
            $agreement_id = (int)trim($params['id']);

            if($agreement_id == 6){  //当id为6的时候才是借款协议
                $mpdf = new mPDF('zh-CN','A4'); //new mPDF('zh-CN','A4','','',23,23,20)创建mpdf对象，‘zh-CN’:对应中文，‘23，23‘,页眉和页脚的距离。
                $mpdf->useAdobeCJK = true;
                $mpdf->SetWatermarkText('钱富宝',0.1);
                $mpdf->showWatermarkText = true;
                $mpdf->SetHTMLHeader( '钱富宝Pro平台借款协议<hr/>' );

                //获取该产品相关信息
                $product = QfbProduct::find()->where(['id' => $id])->asArray()->one();

                if (!empty($product) && $product['status'] >= '6') {
                    //获取用户信息
                    $memgerinfo = QfbMember::find()->joinWith('memberInfo')
                        ->select(['qfb_member.member_type', 'qfb_member.account', 'qfb_member_info.realname', 'qfb_member_info.card_no'])
                        ->where(['id'=>$member_id])
                        ->asArray()
                        ->one();

                    // if(!empty($memgerinfo['member_type']) && $memgerinfo['member_type'] == 1){
                        $where = 'product_id = '.$id.' and member_id = '.$member_id.' and status != 4';
                        //获取投资人员相关信息
                        $touzi = QfbOrderFix::find()->select(['sum(pay_money) as pay_money', 'member_id', 'create_time'])->where($where)->asArray()->one();
                    // }

                    // var_dump($touzi);die;
                    //获取借款人相关信息
                    $jiekuanren = QfbMember::find()->joinWith('memberInfo')
                        ->select(['qfb_member.account', 'qfb_member.mobile', 'qfb_member_info.realname', 'qfb_member_info.card_no'])
                        ->where(['id'=>$product['member_id']])
                        ->asArray()
                        ->one();
                    $jiekuanren['account'] = AdCommon::hidtel($jiekuanren['account']);
                    $jiekuanren['mobile'] = AdCommon::hidtel($jiekuanren['mobile']);
                    $jiekuanren['card_no'] = AdCommon::cut_str($jiekuanren['card_no'], 6, 0).'**** ****'.AdCommon::cut_str($jiekuanren['card_no'], 4, -4);

                    $memberbank = QfbBank::find()->where(['member_id'=>$product['member_id']])->asArray()->one();
                    $memberbank['no'] = '**** **** ****'.AdCommon::cut_str($memberbank['no'], 4, -4);
                    
                    //获取保证方式
                    $baozheng = QfbWarranty::find()->where(['product_id'=>$product['id']])->asArray()->one();

                    //还款计划表
                    $huankuan = QfbOrderRepayment::find()->where(['product_id'=>$product['id']])->asArray()->all();

                    foreach ($huankuan as $key => $value) {
                        $ben = $value['money'];
                        $total += $value['interest'];
                    }

                    //协议编号
                    $bianhao = date("YmdHis");

                    return $this->render('index', [
                        'product' => $product,
                        'touzi' => $touzi,
                        'memgerinfo' => $memgerinfo,
                        'jiekuanren' => $jiekuanren,
                        'memberbank' => $memberbank,
                        'baozheng' => $baozheng,
                        'jiekuanjine' => $jiekuanjine,
                        'huankuan' => $huankuan,
                        'ben' => $ben,
                        'total' => $total,
                        'bianhao' => $bianhao,
                    ]);
                } else {  //如果标的没有满则用空的模板展示
                    return $this->render('template');
                }
            } else {
                //不是6的话展示其他协议类型
                $data = QfbAgreement::findOne(['id'=>$agreement_id]);
                return $this->render('xieyi',[
                    'data'   => $data
                ]);
            }
        }
    }



}
