<?php 
namespace common\service;
use yii;
use common\models\QfbMemberVouchers;

class VouchersService extends BaseService{
    
	/**
	 * @param  int member_id
	 * @param  int type;  //type=0 表示可用，type=1 表示查看历史( 表示已使用 )
     *         int limit;//一页限制个数，默认10
     *         int page;//页数，默认为1
	 * @return array
	 * 
	 * @author jin
	 */
	public static function getVouchersByTypeAndMemberId($member_id = 0 , $type = 0 , $limit = 10 , $page = 1){
		$query = QfbMemberVouchers::find();
		$query->joinWith(['vouchers']);
		$query->select([
		    QfbMemberVouchers::tableName().'.status',
		    QfbMemberVouchers::tableName().'.invalid_time',
		    QfbMemberVouchers::tableName().'.remark',
		    'vouchers.money',
		    'vouchers.use_money'
		]);
		
		$query->where([ QfbMemberVouchers::tableName().'.member_id' => $member_id ]);
		
		if ($type == 0){
		    $query->andWhere([ QfbMemberVouchers::tableName().'.status' => $type ]);
		    $query->andWhere([ '>', QfbMemberVouchers::tableName().'.invalid_time' , time() ]);
		}else {
		    $query->andWhere([ QfbMemberVouchers::tableName().'.status' => $type ]);
		}
		$sql = $query->createCommand()->getRawSql();
		
		$sql .= " order by FROM_UNIXTIME(qfb_member_vouchers.invalid_time,'%Y-%m-%d'),vouchers.money desc " ;
		$sql .= ' limit '.$limit * ($page-1).','.$limit ;
		
		$connection = \Yii::$app->db; //连接
		
		$command = $connection->createCommand($sql);
		
		$result = $command->queryAll();
		return $result;
	}
	
	/**
	 * @param  int member_id
	 * @param  int vochers_id
	 * @return array
	 *
	 * @author jin
	 */
	public static function getVouchersByVouchersIdAndMemberId($member_id =0 ,$vochers_id = 0 ){
	    $query = QfbMemberVouchers::find();
	    $query->joinWith(['vouchers']);
	    
	    $query->where([ 
	        QfbMemberVouchers::tableName().'.vouchers_id' => $vochers_id,
	        QfbMemberVouchers::tableName().'.member_id' => $member_id,
	    ]);
	
	    $result = $query->asArray()->one();
	    return $result;
	}
	
	/**
	 * 用户可用代金券的总张数
	 * @param  int member_id
	 *
	 * @author jin
	 */
	public static function getVouchersNumsByMemberId($member_id =0 ){
	    $query = QfbMemberVouchers::find();
	
	    $query->where([
	        QfbMemberVouchers::tableName().'.member_id' => $member_id,
	        QfbMemberVouchers::tableName().'.status' => 0,
	    ]);
	     
	    $query->andWhere(['>',QfbMemberVouchers::tableName().'.invalid_time',time()]);
	
	    $result = $query->count();
	    return $result;
	}
	
	/**
	 * 用户可用代金券的总金额
	 * @param  int member_id
	 *
	 * @author jin
	 */
	public static function getVouchersMoneysByMemberId($member_id =0 ){
	    $query = QfbMemberVouchers::find();
	    $query->joinWith(['vouchers']);
	     
	    $query->where([
	        QfbMemberVouchers::tableName().'.member_id' => $member_id,
	        QfbMemberVouchers::tableName().'.status' => 0,
	    ]);
	    
	    $query->andWhere(['>',QfbMemberVouchers::tableName().'.invalid_time',time()]);
	
	    $result = $query->sum('vouchers.money');
	    return $result;
	}
	
	/**
	 * 用户可用代金券的总金额
	 * @param  int member_id
	 * @param  int type //当type请求0的时候返回可用代金券总额，当type为1请求返回已用代金券总额
	 * @author jin
	 */
	public static function getVouchersMoneys($member_id =0 , $type = 0 ){
	    $query = QfbMemberVouchers::find();
	    $query->joinWith(['vouchers']);
	
	    $query->where([
	        QfbMemberVouchers::tableName().'.member_id' => $member_id,
	    ]);
	    
	    if ($type == 0){
	        $query->andWhere([QfbMemberVouchers::tableName().'.status' => 0]);
	        $query->andWhere(['>',QfbMemberVouchers::tableName().'.invalid_time',time()]);
	    }else{
	        $query->andWhere([QfbMemberVouchers::tableName().'.status' => 1]);
	    }
	    $result = $query->sum('vouchers.money');
	    return $result;
	}
	
	/**
	 * 用户可用最佳的代金券
	 * @param  int member_id
	 *
	 * @author jin
	 */
	public static function getVouchersByMemberId($member_id =0 ,$product_type = -1,$money = 0){
	    $query = QfbMemberVouchers::find();
	    $query->joinWith(['vouchers']);
	    
	    $query->where([
	        QfbMemberVouchers::tableName().'.member_id' => $member_id,
	        QfbMemberVouchers::tableName().'.status' => 0,
	    ]);
	    
	    if ($product_type > 0){
	        $query->andWhere(['vouchers.use_type' => $product_type]);
	    }
	     
	    $query->andWhere(['>',QfbMemberVouchers::tableName().'.invalid_time',time()]);
	    $query->andWhere(['<=','vouchers.use_money',$money]);
	    
	    $query->orderBy('vouchers.money desc');
	    $query->limit(1);
	    $result = $query->asArray()->one();
	    return $result;
	}
	
}