<?php
namespace common\service;
use common\models\QfbBanner;
use yii;

/**
 * 
 * @author jin
 *
 */
class BannerService extends BaseService
{
    
    /**
     * 
     * @return array
     */
    public function getData()
    {
        $query = QfbBanner::find();
        $query->select(['name','imgurl','type','linkurl','share_type']);
        $query->where([
            'status' => 1,
        ]);
        
        $query->andWhere(['<=','display_start_time',time()]);
        $query->andWhere(['>=','display_end_time',time()]);
        
        $query->orderBy('sortord');
        
        $result = $query->asArray()->all();
        //$aa=$query->createCommand()->getRawSql();
        return $result;
    }
}

?>