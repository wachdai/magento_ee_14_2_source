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
 * @package     Mage_Core
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Enter description here ...
 *
 * @method Mage_Core_Model_Resource_Layout _getResource()
 * @method Mage_Core_Model_Resource_Layout getResource()
 * @method string getHandle()
 * @method Mage_Core_Model_Layout_Data setHandle(string $value)
 * @method string getXml()
 * @method Mage_Core_Model_Layout_Data setXml(string $value)
 * @method int getSortOrder()
 * @method Mage_Core_Model_Layout_Data setSortOrder(int $value)
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Layout_Data extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('core/layout');
    }
}
