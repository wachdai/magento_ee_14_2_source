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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Order shipping address backend
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Order_Attribute_Backend_Shipping
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Perform operation before save
     *
     * @param Varien_Object $object
     */
    public function beforeSave($object)
    {
        $shippingAddressId = $object->getShippingAddressId();
        if (is_null($shippingAddressId)) {
            $object->unsetShippingAddressId();
        }
    }

    /**
     * Perform operation after save
     *
     * @param Varien_Object $object
     */
    public function afterSave($object)
    {
        $shippingAddressId = false;
        foreach ($object->getAddressesCollection() as $address) {
            if ('shipping' == $address->getAddressType()) {
                $shippingAddressId = $address->getId();
            }
        }
        if ($shippingAddressId) {
            $object->setShippingAddressId($shippingAddressId);
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getAttributeCode());
        }
    }
}
