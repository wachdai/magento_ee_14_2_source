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
 * @package     Enterprise_Wishlist
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Wishlist search by name and last name strategy
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Model_Search_Strategy_Name implements Enterprise_Wishlist_Model_Search_Strategy_Interface
{
    /**
     * Customer firstname provided for search
     *
     * @var string
     */
    protected $_firstname;

    /**
     * Customer lastname provided for search
     *
     * @var string
     */
    protected $_lastname;

    /**
     * Validate search params
     *
     * @param array $params
     */
    public function setSearchParams(array $params)
    {
        if (empty($params['firstname']) || strlen($params['firstname']) < 2) {
            throw new InvalidArgumentException(
                Mage::helper('enterprise_wishlist')->__('Please enter at least 2 letters of the first name.')
            );
        }
        $this->_firstname = $params['firstname'];
        if (empty($params['lastname']) || strlen($params['lastname']) < 2) {
            throw new InvalidArgumentException(
                Mage::helper('enterprise_wishlist')->__('Please enter at least 2 letters of the last name.')
            );
        }
        $this->_lastname = $params['lastname'];
    }

    /**
     * Filter wishlist collection
     *
     * @param Mage_Wishlist_Model_Resource_Wishlist_Collection $collection
     * @return Mage_Wishlist_Model_Resource_Wishlist_Collection
     */
    public function filterCollection(Mage_Wishlist_Model_Resource_Wishlist_Collection $collection)
    {
        /* @var $customers Mage_Customer_Model_Resource_Customer_Collection */
        $customers = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter(
                array(array('attribute' => 'firstname', 'like' => '%'.$this->_firstname.'%'))
            )
            ->addAttributeToFilter(
                array(array('attribute' => 'lastname', 'like' => '%'.$this->_lastname.'%'))
            );

        $collection->filterByCustomerIds($customers->getAllIds());
        foreach ($collection as $wishlist) {
            $wishlist->setCustomer($customers->getItemById($wishlist->getCustomerId()));
        }
        return $collection;
    }
}
