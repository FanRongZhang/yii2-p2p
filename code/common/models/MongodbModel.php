<?php

namespace common\models;

use Yii;
use common\extension\MongoDb;

/**
 * @mongodb基类
 */
class MongodbModel extends \yii\db\ActiveRecord
{

    //连接mongodb
    public function getMongoConnection($dbserver, $db)
    {
        $dbKey = "mongodb-" . $dbserver;
        global $_SGLOBAL;
        if ( empty($_SGLOBAL[$dbKey]) ) {
            //配置文件
            $db_host 	= Yii::$app->params[$dbKey]['db_host'];
            $db_port 	= Yii::$app->params[$dbKey]['db_port'];
            $db_user 	= Yii::$app->params[$dbKey]['db_user'];
            $db_pass 	= Yii::$app->params[$dbKey]['db_pass'];

            $mongo_server = $db_host.":".$db_port;
            $mongo_options = [
                'connect'=>TRUE,
                'username'=>$db_user,
                'password'=>$db_pass
            ];

            //引用mongodb类
            $mongo = new Mongodb($mongo_server,$mongo_options);

            $_SGLOBAL[$dbKey] = $mongo;
        }
        else {
            $mongo = $_SGLOBAL[$dbKey];
        }

        $mongo->selectDb($db);

        return $mongo;
    }

    //连接mongodb d_wallet库
    public function mongo_dw()
    {
        $mongo = $this->getMongoConnection( '153', 'd_wallet' );

        return $mongo;
    }

    /**
     * 统计mongodb中表中符合添加记录数
     * @param string $tbl 表名
     * @param array $condition 条件数组
     * @return int   成功则返回记录数目
     */
    public function countMongo($tbl, $condition, $dbserver="dw")
    {
        $fn = "mongo_".$dbserver;
        $mongo = $this->$fn();
        $count = $mongo->count($tbl, $condition);
        return $count;
    }

    /**
     * 查询记录
     * @param string $tbl 数据表名称
     * @param array $condition 查询条件
     * @param array $result_limit 记录条件控制
     * @return 成功则返回记录集，失败返回false
     * */
    public function findMongoList($tbl, array $condition, $result_limit = array(), $fields = array(), $dbserver="dw")
    {
        $fn = "mongo_".$dbserver;
        $mongo = $this->$fn();
        $res = $mongo->find($tbl, $condition, $result_limit, $fields);
        return $res;
    }

    /**
     * 查询一条记录
     * @param string $tbl 数据表名称
     * @param array $condition 查询条件
     * @return 成功则返回一条记录，失败返回false
     * */
    public function findMongo($tbl, array $condition, $fields = array(), $dbserver="dw")
    {
        $fn = "mongo_".$dbserver;
        $mongo = $this->$fn();
        $res = $mongo->findOne($tbl, $condition, $fields);

        return $res;
    }

    /**
     * 添加数据到数据表
     * @param string $tbl 数据表名称
     * @param array $params 添加的数据字段
     * @return 成功则返回最新记录id，失败返回false
     * */
    public function insertMongo($tbl, array $params, $dbserver="dw")
    {
        $fn = "mongo_".$dbserver;
        $mongo = $this->$fn();
        $res = $mongo->insert($tbl, $params);
        return $res;
    }

    /**
     * 更新数据到数据表
     * @param string $tbl 数据表名称
     * @param array $condition 更新条件
     * @param array $params 更新的数据字段
     * @return 成功则返回最新记录id，失败返回false
     * */
    public function updateMongo($tbl, array $condition, array $params, $options = array(), $dbserver="dw")
    {
        $fn = "mongo_".$dbserver;
        $mongo = $this->$fn();
        $res = $mongo->update($tbl, $condition, $params, $options);
        return $res;
    }

    /**
     * 根据条件删除数据
     * @param string $tbl 数据表名称
     * @param array $condition 删除条件
     * @return array 返回结果
     * */
    public function removeMongo($tbl, array $condition, $options = array(), $dbserver="dw")
    {
        $fn = "mongo_".$dbserver;
        $mongo = $this->$fn();
        $res = $mongo->remove($tbl, $condition, $options);
        return $res;
    }

    /**
     * 根据条件分组mongo数据
     * @param string $tbl 表名称
     * @param array $condition 查询条件
     * @param array $group 分组条件
     * @return array
     */
    public function groupMongo($tbl, $condition = array(), $group = array(), $dbserver="dw")
    {
        $fn = "mongo_".$dbserver;
        $mongo = $this->$fn();
        $res = $mongo->group($tbl, $condition, $group);
        return $res;
    }

}