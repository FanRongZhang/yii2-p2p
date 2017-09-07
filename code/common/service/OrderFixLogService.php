<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/11
 * Time: 15:05
 */
namespace common\service;
use common\models\QfbOrderFixLog;
use yii;
class OrderFixLogService extends BaseService
{

    protected $_className = "common\models\QfbOrderFixLog";

    public function createList($fixModel,$rateData,$productModel){
        $memberData = yii::$app->db->createCommand(sprintf("select `qfb_member`.relations,`qfb_member`.level,`qfb_member`.mobile,qfb_member_info.realname
            from qfb_member
            join qfb_member_info on qfb_member.id=qfb_member_info.member_id
            where qfb_member.id = %d limit 1",$fixModel['member_id']))->queryOne();


        $parent=ToolService::findPerson($memberData['relations'],3);
        if(count($parent)==0) return true;

        $parentRe= array_flip($parent);

        $levels=yii::$app->db->createCommand(sprintf("select id,level from qfb_member where id in (%s) ",implode(',',$parent)))->queryAll();

        $parentLevel=[];
        foreach ($levels as $key => $value) {
            $parentLevel[$value['id']]=$value['level'];
        }
        $logData=[];
        $remark=ToolService::setEncrypt($memberData['mobile'],3,4,'*',4).'('.
                ToolService::setEncryptSubString($memberData['realname'],0,'')
                .')投资【'.$productModel['product_name'].'】'.bcadd($fixModel['money'],0,2).'元';

        //计算给上级的分润
        foreach ($parentLevel as $key => $value) {
            $money = 0;
            if($parentLevel[$key]!= 15){
                switch ($parentRe[$key]) {
                    case 0:
                        $money = bcdiv($fixModel['money']*$rateData['recommond_rate']*$productModel['invest_day']*$rateData['direct_rate'],3650000,2);
                        break;
                    case 1:
                        $money = bcdiv($fixModel['money']*$rateData['recommond_rate']*$productModel['invest_day']*$rateData['indirect_rate'],3650000,2);
                        break;
                    case 2:
                        $money = bcdiv($fixModel['money']*$rateData['recommond_rate']*$productModel['invest_day']*$rateData['share_rate'],3650000,2);
                        break;
                    default:
                        $money=0;
                        break;
                }
            }
            if($money-0>0){
                $logData[]=sprintf("(%d,%.2f,%d,%d,'%s')",$fixModel['id'],$money,$fixModel['member_id'],$key,$remark);
            }
        }

        try{
            if(count($logData)>0){
                $num=$fixModel['member_id']%10;
                $sql= sprintf("INSERT INTO qfb_order_fix_log%d (order_id,`money`,from_member,to_member,remark)VALUES %s ;",$num,implode(',',$logData));
                yii::$app->db->createCommand($sql)->execute();
            }
            return true;
        }catch (\Exception $e){
            $this->addMessage('log',$e->getMessage());
            return false;
        }
    }


    /**
     * 根据用户ID查询待发放推荐奖励明细信息
     */
    public static function getOrderFixLogByMemberId($member_id,$page=null,$limit=null)
    {
        $orderFixLog = new QfbOrderFixLog();
        $result =  $orderFixLog->find()
            ->select(['order_id','money','remark'])
            ->offset(($page-1)*$limit)
            ->limit($limit)
            ->where(['=','to_member',$member_id])
            ->all();
        if (count($result) > 0)
        {
            return $result;
        }
        else
        {
            return false;
        }
    }
}