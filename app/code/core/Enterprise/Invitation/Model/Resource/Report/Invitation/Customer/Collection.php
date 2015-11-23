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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reports invitation customer report collection
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Invitation_Model_Resource_Report_Invitation_Customer_Collection
    extends Mage_Reports_Model_Resource_Customer_Collection
{
    /**
     * Joins Invitation report data, and filter by date
     *
     * @param Zend_Date|string $from
     * @param Zend_Date|string $to
     * @return Enterprise_Invitation_Model_Resource_Report_Invitation_Customer_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset();
        $this->getSelect()
            ->join(array('invitation' => $this->getTable('enterprise_invitation/invitation')),
                'invitation.customer_id = e.entity_id',
                array(
                    'sent' => new Zend_Db_Expr('COUNT(invitation.invitation_id)'),
                    'accepted' => new Zend_Db_Expr('COUNT(invitation.referral_id) ')
                )
            )->group('e.entity_id');

        $this->_joinFields['invitation_store_id'] = array('table' =>'invitation', 'field' => 'store_id');
        $this->_joinFields['invitation_date'] = array('table' => 'invitation', 'field' => 'invitation_date');

        // Filter by date range
        $this->addFieldToFilter('invitation_date', array('from' => $from, 'to' => $to, 'time' => true));

        // Add customer name
        $this->addNameToSelect();

        // Add customer group
        $this->addAttributeToSelect('group_id', 'inner');
        $this->joinField('group_name', 'customer/customer_group', 'customer_group_code', 'customer_group_id=group_id');

        $this->orderByCustomerRegistration();

        /**
         * Allow analytic columns usage
         */
        $this->_useAnalyticFunction = true;

        return $this;
    }

    /**
     * Filters report by stores
     *
     * @param array $storeIds
     * @return Enterprise_Invitation_Model_Resource_Report_Invitation_Customer_Collection
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->addFieldToFilter('invitation_store_id', array('in' => (array)$storeIds));
        }
        return $this;
    }
}
