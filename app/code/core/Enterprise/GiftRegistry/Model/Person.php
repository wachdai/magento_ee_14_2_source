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
 * Entity registrants data model
 *
 * @method Enterprise_GiftRegistry_Model_Resource_Person _getResource()
 * @method Enterprise_GiftRegistry_Model_Resource_Person getResource()
 * @method Enterprise_GiftRegistry_Model_Person setEntityId(int $value)
 * @method string getFirstname()
 * @method Enterprise_GiftRegistry_Model_Person setFirstname(string $value)
 * @method string getMiddlename()
 * @method Enterprise_GiftRegistry_Model_Person setMiddlename(string $value)
 * @method string getLastname()
 * @method Enterprise_GiftRegistry_Model_Person setLastname(string $value)
 * @method string getEmail()
 * @method Enterprise_GiftRegistry_Model_Person setEmail(string $value)
 * @method string getRole()
 * @method Enterprise_GiftRegistry_Model_Person setRole(string $value)
 * @method string getCustomValues()
 * @method Enterprise_GiftRegistry_Model_Person setCustomValues(string $value)
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftRegistry_Model_Person extends Mage_Core_Model_Abstract
{
    function _construct() {
        $this->_init('enterprise_giftregistry/person');
    }

    /**
     * Validate registrant attribute values
     *
     * @return array|bool
     */
    public function validate()
    {
        // not Checking entityId !!!
        $errors = array();

        if (!Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = Mage::helper('enterprise_giftregistry')->__('Please enter the first name.');
        }

        if (!Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $errors[] = Mage::helper('enterprise_giftregistry')->__('Please enter the last name.');
        }

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = Mage::helper('enterprise_giftregistry')->__('"Email" is not a valid email address.');
        }

        $customValues = $this->getCustom();
        $attributes = Mage::getSingleton('enterprise_giftregistry/entity')->getRegistrantAttributes();

        $errorsCustom = Mage::helper('enterprise_giftregistry')->validateCustomAttributes($customValues, $attributes);
        if ($errorsCustom !== true) {
            $errors = empty($errors) ? $errorsCustom : array_merge($errors, $errorsCustom);
        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Unpack "custom" value array
     *
     * @return $this
     */
    public function unserialiseCustom() {
        if (is_string($this->getCustomValues())) {
            $this->setCustom(unserialize($this->getCustomValues()));
        }
        return $this;
    }
}
