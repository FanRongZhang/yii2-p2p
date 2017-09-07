<?php
namespace common\enum;

/**
 * 订单枚举,罗列各种订单状态
 *
 */
class MoneyEnum
{
    /**
     * 零钱
     */
    const MONEY = 1;

    /**
     * 活期
     */
    const LIVE = 2;

    /**
     *定期
     */
    const FIX = 3;

    /**
     * 收入
     */
    const MONEY_IN = 1;

    /**
     * 支出
     */
    const MONEY_OUT = 2;
    
    /**
     * 1管理奖
     */
    const ACTION_MANAGE = 1;
    
    /**
     *3分润
     */
    const ACTION_SHARE = 3;
    
    /**
     * 4活期推荐奖
     */
    const ACTION_FOUR = 4;
    
    /**
     * 5定期推荐奖
     */
    const ACTION_FIVE = 5;
    
    /**
     * 8收益
     */
    const ACTION_PROFIT = 8;
    
    /**
     * 16定期收益,
     */
    const ACTION16 = 16;
    
    /**
     * 17定期管理奖,
     */
    const ACTION17 = 17;
    
    /**
     * 18定期分润
     */
    const ACTION18 = 18;

}
