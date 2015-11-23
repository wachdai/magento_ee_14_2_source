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
 * @package     Mage_GoogleBase
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Google Base Item Types Model
 *
 * @method Mage_GoogleBase_Model_Resource_Type _getResource()
 * @method Mage_GoogleBase_Model_Resource_Type getResource()
 * @method int getAttributeSetId()
 * @method Mage_GoogleBase_Model_Type setAttributeSetId(int $value)
 * @method string getGbaseItemtype()
 * @method Mage_GoogleBase_Model_Type setGbaseItemtype(string $value)
 * @method string getTargetCountry()
 * @method Mage_GoogleBase_Model_Type setTargetCountry(string $value)
 *
 * @deprecated after 1.5.1.0
 * @category   Mage
 * @package    Mage_GoogleBase
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleBase_Model_Type extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('googlebase/type');
    }

    /**
     * Load type model by Attribute Set Id
     *
     * @param int $attributeSetId Attribute Set
     * @param string $targetCountry Two-letters country ISO code
     * @return Mage_GoogleBase_Model_Type
     */
    public function loadByAttributeSetId($attributeSetId, $targetCountry)
    {
        $typeId = $this->getResource()->getTypeIdByAttributeSetId($attributeSetId, $targetCountry);
        return $this->load($typeId);
    }
}
