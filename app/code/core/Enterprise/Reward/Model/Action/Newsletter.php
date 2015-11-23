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
 * Reward action for Newsletter Subscription
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Action_Newsletter extends Enterprise_Reward_Model_Action_Abstract
{
    /**
     * Retrieve points delta for action
     *
     * @param int $websiteId
     * @return int
     */
    public function getPoints($websiteId)
    {
        return (int)Mage::helper('enterprise_reward')->getPointsConfig('newsletter', $websiteId);
    }

    /**
     * Check whether rewards can be added for action
     *
     * @return bool
     */
    public function canAddRewardPoints()
    {
        $subscriber = $this->getEntity();
        $subscriberStatuses = array(
            Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED,
            Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED
        );
        if (!in_array($subscriber->getData('subscriber_status'), $subscriberStatuses)) {
            return false;
        }

        /* @var $subscribers Mage_Newsletter_Model_Mysql4_Subscriber_Collection */
        $subscribers = Mage::getResourceModel('newsletter/subscriber_collection')
            ->addFieldToFilter('customer_id', $subscriber->getCustomerId())
            ->load();
        // check for existing customer subscribtions
        $found = false;
        foreach ($subscribers as $item) {
            if ($subscriber->getSubscriberId() != $item->getSubscriberId()) {
                $found = true;
                break;
            }
        }
        $exceeded = $this->isRewardLimitExceeded();
        return !$found && !$exceeded;
    }

    /**
     * Return action message for history log
     *
     * @param array $args Additional history data
     * @return string
     */
    public function getHistoryMessage($args = array())
    {
        $email = isset($args['email']) ? $args['email'] : '';
        return Mage::helper('enterprise_reward')->__('Signed up for newsletter with email %s.', $email);
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
            'email' => $this->getEntity()->getEmail()
        ));
        return $this;
    }
}
