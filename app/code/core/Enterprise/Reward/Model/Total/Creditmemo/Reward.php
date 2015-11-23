<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reward sales order creditmemo total model
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Total_Creditmemo_Reward extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * Collect reward totals for credit memo
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Enterprise_Reward_Model_Total_Creditmemo_Reward
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $rewardCurrecnyAmountLeft = $order->getRewardCurrencyAmountInvoiced() - $order->getRewardCurrencyAmountRefunded();
        $baseRewardCurrecnyAmountLeft = $order->getBaseRewardCurrencyAmountInvoiced() - $order->getBaseRewardCurrencyAmountRefunded();
        if ($order->getBaseRewardCurrencyAmount() && $baseRewardCurrecnyAmountLeft > 0) {
            if ($baseRewardCurrecnyAmountLeft >= $creditmemo->getBaseGrandTotal()) {
                $rewardCurrecnyAmountLeft = $creditmemo->getGrandTotal();
                $baseRewardCurrecnyAmountLeft = $creditmemo->getBaseGrandTotal();
                $creditmemo->setGrandTotal(0);
                $creditmemo->setBaseGrandTotal(0);
                $creditmemo->setAllowZeroGrandTotal(true);
            } else {
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $rewardCurrecnyAmountLeft);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseRewardCurrecnyAmountLeft);
            }
            $pointValue = $order->getRewardPointsBalance() / $order->getBaseRewardCurrencyAmount();
            $rewardPointsBalance = $baseRewardCurrecnyAmountLeft*ceil($pointValue);
            $rewardPointsBalanceLeft = $order->getRewardPointsBalance() - $order->getRewardPointsBalanceRefunded();
            if ($rewardPointsBalance > $rewardPointsBalanceLeft) {
                $rewardPointsBalance = $rewardPointsBalanceLeft;
            }
            $creditmemo->setRewardPointsBalance($rewardPointsBalance);
            $creditmemo->setRewardCurrencyAmount($rewardCurrecnyAmountLeft);
            $creditmemo->setBaseRewardCurrencyAmount($baseRewardCurrecnyAmountLeft);
        }
        return $this;
    }
}
