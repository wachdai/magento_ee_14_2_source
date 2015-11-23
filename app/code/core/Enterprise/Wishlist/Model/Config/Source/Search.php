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
 * Search config model
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Model_Config_Source_Search
{
    /**
     * Quick search form types
     */
    const WISHLIST_SEARCH_DISPLAY_ALL_FORMS = 'all';
    const WISHLIST_SEARCH_DISPLAY_NAME_FORM = 'name';
    const WISHLIST_SEARCH_DISPLAY_EMAIL_FORM = 'email';

    /**
     * Retrieve search form types as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = array();
        foreach ($this->getTypes() as $key => $label) {
            $result[] = array('value' => $key, 'label' => $label);
        }
        return $result;
    }

    /**
     * Retrieve array of search form types
     *
     * @return array
     */
    public function getTypes()
    {
        return array(
            self::WISHLIST_SEARCH_DISPLAY_ALL_FORMS => Mage::helper('enterprise_wishlist')->__('All Forms'),
            self::WISHLIST_SEARCH_DISPLAY_NAME_FORM => Mage::helper('enterprise_wishlist')->__('Wishlist Owner Name Search'),
            self::WISHLIST_SEARCH_DISPLAY_EMAIL_FORM => Mage::helper('enterprise_wishlist')->__('Wishlist Owner Email Search')
        );
    }
}
