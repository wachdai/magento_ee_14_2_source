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
 * Reward action for updating balance by salesrule
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Action_Salesrule extends Enterprise_Reward_Model_Action_Abstract
{
     /**
     * Quote instance, required for estimating checkout reward (rule defined static value)
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;

     /**
     * Retrieve points delta for action
     *
     * @param int $websiteId
     * @return int
     */
    public function getPoints($websiteId) {
        $pointsDelta = 0;
        if ($this->_quote) {
            // known issue: no support for multishipping quote // copied  comment, not checked
            if ($this->_quote->getAppliedRuleIds()) { 
                $ruleIds = explode(',', $this->_quote->getAppliedRuleIds());
                $ruleIds = array_unique($ruleIds);
                $data = Mage::getResourceModel('enterprise_reward/reward')->getRewardSalesrule($ruleIds);
                foreach ($data as $rule) {
                    $pointsDelta += (int)$rule['points_delta'];
                }
            }
        }
        return $pointsDelta;
    }

    /**
     * Quote setter
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Enterprise_Reward_Model_Action_OrderExtra
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Check whether rewards can be added for action
     *
     * @return bool
     */
    public function canAddRewardPoints()
    {
        return true;
    }

    /**
     * Return action message for history log
     *
     * @param array $args Additional history data
     * @return string
     */
    public function getHistoryMessage($args = array())
    {
        $incrementId = isset($args['increment_id']) ? $args['increment_id'] : '';
        return Mage::helper('enterprise_reward')->__('Earned promotion extra points from order #%s.', $incrementId);
    }

    /**
     * Setter for $_entity and add some extra data to history
     *
     * @param Varien_Object $entity
     * @return Enterprise_Reward_Model_Action_Abstract
     */
    public function setEntity($entity)
    {
        parent::setEntity($entity);
        $this->getHistory()->addAdditionalData(array(
            'increment_id' => $this->getEntity()->getIncrementId()
        ));
        return $this;
    }
}
