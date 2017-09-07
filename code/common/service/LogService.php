<?php
namespace common\service;

use Yii;

/**
 * 记录日志的服务
 * @author xiaomalover <xiaomalover@gmail.com>
 */
class LogService
{
    /**
     * 记录回调日志
     * @param String $fileName 要存入的文件名
     * @param String $content 要存入的内容
     */
    public static function log($fileName, $content)
    {
        $logfile = Yii::$app->getRuntimePath() . '/logs/' . $fileName;
        if (!file_exists($logfile)) {
            touch($logfile);
        }
        $fp = fopen($logfile, "aw");
        fwrite($fp, date("Y-m-d H:i:s") . "----" . $content . chr(10));
        fclose($fp);
    }

    /**
     * 海口银行-写日志
     */
    public static function hkyh_write_log($fileName, $content='')
    {

        $notifyLog = \Yii::$app->BasePath.'/../common/extension/hkyh/logs/' .date("Y-m-d",time()).'/'. $fileName;

        $logDir = dirname($notifyLog);

        // $path = \Yii::$app->BasePath.'/../common/extension/hkyh/logs/'.date("Y-m-d",time()).'/';

        if(!is_dir($logDir)) mkdir($logDir, 0777, true);

        // 追加写入
        $fp = @fopen($notifyLog, "a");
        @fwrite($fp, $content);
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }

}
