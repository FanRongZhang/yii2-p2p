<?php
namespace common\toolbox;
class Tool
{

    public static $periodsDay = 30;

    public static $dayTime = 86400; //每天的秒数

    /**
     * 计算今年是否是闰年
     * @return int
     */
    public static function yearDay()
    {
        $year = date('Y', time());
        $day = 365;
        if(($year%4 == 0 && $year%100 != 0) || $year%400 == 0){
            $day = 366;
        }

        return $day;
    }

    /**
     * 保留俩位小数不四舍五入
     * @param $money
     * @return string
     */
    public static function moneyCalculate($money)
    {
        return floor($money*100)/100;
    }

    /**
     * 保留三位小数不四舍五入
     * @param $money
     * @return string
     */
    public static function moneyThousands($money)
    {
        return floor($money*1000)/1000;
    }

    /**
     * 千分位如果是大于0就进1
     * @param $money
     * @return float
     */
    public static function moneyPlatform($money)
    {
        $moneyFirst = self::moneyCalculate($money);
        if($money-$moneyFirst>0){
            $moneyFirst += 0.01;
        }

        return $moneyFirst;
    }

    /**
     * 每天的利息
     * @param $money 投资金额
     * @param $rate 年利率
     * @return float
     */
    public static function interestDay($money, $rate)
    {
        return $money*$rate/self::yearDay();
    }

    /**
     * 根据日期获取当日24点时间戳
     * @param $date
     * @return int
     */
    public static function endTime($date)
    {
        $endDate = Date('Y-m-d', $date);
        return strtotime($endDate)+24*3600;
    }

}