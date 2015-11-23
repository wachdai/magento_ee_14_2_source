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
 * Wishlist search by email strategy
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Model_Search_Strategy_Email implements Enterprise_Wishlist_Model_Search_Strategy_Interface
{
    /**
     * Email provided for search
     *
     * @var string
     */
    protected $_email;

    /**
     * Set search fields required by search strategy
     *
     * @param array $params
     */
    public function setSearchParams(array $params)
    {
        if (empty($params['email']) || !Zend_Validate::is($params['email'], 'EmailAddress')) {
            throw new InvalidArgumentException(
                Mage::helper('enterprise_wishlist')->__('Please input a valid email address.')
            );
        }
        $this->_email = $params['email'];
    }

    /**
     * Filter given wishlist collection
     *
     * @param Mage_Wishlist_Model_Resource_Wishlist_Collection $collection
     * @return Mage_Wishlist_Model_Resource_Wishlist_Collection
     */
    public function filterCollection(Mage_Wishlist_Model_Resource_Wishlist_Collection $collection)
    {
        $customer = Mage::getModel("customer/customer")
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($this->_email);

        $collection->filterByCustomer($customer);
        foreach ($collection as $item){
            $item->setCustomer($customer);
        }
        return $collection;
    }
}
