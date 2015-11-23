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
 * Invitation collection
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Invitation_Model_Resource_Invitation_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Fields mapping 
     *
     * @var array
     */
    protected $_map    = array('fields' => array(
        'invitee_email'    => 'c.email',
        'website_id'       => 'w.website_id',
        'invitation_email' => 'main_table.email',
        'invitee_group_id' => 'main_table.group_id'
    ));

    /**
     * Intialize collection
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_invitation/invitation');
    }

    /**
     * Instantiate select object
     *
     * @return Enterprise_Invitation_Model_Resource_Invitation_Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            array('main_table' => $this->getResource()->getMainTable()),
            array('*', 'invitation_email' => 'email', 'invitee_group_id' => 'group_id')
        );
        return $this;
    }

    /**
     * Load collection where customer id equals passed parameter
     *
     * @param int $id
     * @return Enterprise_Invitation_Model_Resource_Invitation_Collection
     */
    public function loadByCustomerId($id)
    {
        $this->getSelect()->where('main_table.customer_id = ?', $id);
        return $this->load();
    }

    /**
     * Filter by specified store ids
     *
     * @param array|int $storeIds
     * @return Enterprise_Invitation_Model_Resource_Invitation_Collection
     */
    public function addStoreFilter($storeIds)
    {
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }

    /**
     * Join website ID
     *
     * @return Enterprise_Invitation_Model_Resource_Invitation_Collection
     */
    public function addWebsiteInformation()
    {
        $this->getSelect()
            ->joinInner(
                array('w' => $this->getTable('core/store')),
                'main_table.store_id = w.store_id',
                'w.website_id'
            );
        return $this;
    }

    /**
     * Join referrals information (email)
     *
     * @return Enterprise_Invitation_Model_Resource_Invitation_Collection
     */
    public function addInviteeInformation()
    {
        $this->getSelect()->joinLeft(
            array('c' => $this->getTable('customer/entity')),
            'main_table.referral_id = c.entity_id', array('invitee_email' => 'c.email')
        );
        return $this;
    }

    /**
     * Filter collection by items that can be sent
     *
     * @return Enterprise_Invitation_Model_Resource_Invitation_Collection
     */
    public function addCanBeSentFilter()
    {
        return $this->addFieldToFilter('status', Enterprise_Invitation_Model_Invitation::STATUS_NEW);
    }

    /**
     * Filter collection by items that can be cancelled
     *
     * @return Enterprise_Invitation_Model_Resource_Invitation_Collection
     */
    public function addCanBeCanceledFilter()
    {
        return $this->addFieldToFilter('status', array('nin' => array(
            Enterprise_Invitation_Model_Invitation::STATUS_CANCELED,
            Enterprise_Invitation_Model_Invitation::STATUS_ACCEPTED
        )));
    }
}
