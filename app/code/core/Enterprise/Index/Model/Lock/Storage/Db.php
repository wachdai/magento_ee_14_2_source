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
 * @package     Enterprise_Index
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Database lock storage
 *
 * @category    Enterprise
 * @package     Enterprise_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Index_Model_Lock_Storage_Db extends Mage_Index_Model_Lock_Storage_Db
    implements Enterprise_Index_Model_Lock_Storage_Interface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource   = Mage::getSingleton('enterprise_index/resource_lock_resource');
        $this->_connection = $resource->getConnection('enterprise_index_write', 'default_lock');
        $this->_helper = Mage::getResourceHelper('enterprise_index')->setWriteAdapter($this->_connection);
    }
}
