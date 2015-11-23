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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Search source model
 */
class Enterprise_GiftRegistry_Model_Source_Search
{
    /**
     * Quick search form types
     */
    const SEARCH_ALL_FORM   = 'all';
    const SEARCH_NAME_FORM  = 'name';
    const SEARCH_EMAIL_FORM = 'email';
    const SEARCH_ID_FORM    = 'id';

    /**
     * Return search form types as option array
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
     * Return array of search form types
     *
     * @return array
     */
    public function getTypes()
    {
        return array(
            self::SEARCH_ALL_FORM => Mage::helper('enterprise_giftregistry')->__('All Forms'),
            self::SEARCH_NAME_FORM => Mage::helper('enterprise_giftregistry')->__('Recipient Name Search'),
            self::SEARCH_EMAIL_FORM => Mage::helper('enterprise_giftregistry')->__('Recipient Email Search'),
            self::SEARCH_ID_FORM => Mage::helper('enterprise_giftregistry')->__('Gift Registry ID Search')
        );
    }
}
