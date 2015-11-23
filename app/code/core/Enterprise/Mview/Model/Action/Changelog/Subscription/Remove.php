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
 * @package     Enterprise_Mview
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Changelog remove action class
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Action_Changelog_Subscription_Remove
    extends Enterprise_Mview_Model_Action_Changelog_Subscription_Abstract
        implements Enterprise_Mview_Model_Action_Interface
{
    /**
     * Remove trigger from target_table table
     *
     * @return Enterprise_Mview_Model_Action_Changelog_Subscription_Remove
     */
    public function execute()
    {
        $subscriber = $this->_getSubscriber();

        // If subscription exists remove record from 'enterprise_mview_subscriber' table
        if ($subscriber->getId()) {
            $subscriber->delete();
        }
        unset($subscriber);

        $this->_createTriggers();
        return $this;
    }

    /**
     * Load subscriber instance by unique metadata_id and target_table fields.
     *
     * @return Enterprise_Mview_Model_Subscriber
     */
    protected function _getSubscriber()
    {
        return $this->_getSubscriberCollection()
            ->addFieldToFilter('metadata_id', $this->_metadata->getId())
            ->addFieldToFilter('target_table', $this->_targetTable)
            ->getFirstItem();
    }
}
