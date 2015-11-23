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
 * @package     Enterprise_Mview
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Enterprise_Mview_Model_Factory
 *
 * @category    Enterprise
 * @package     Enterprise_Mview
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Mview_Model_Factory extends Mage_Core_Model_Factory
{
    /**
     * Returns resource helper instance
     *
     * @param string $moduleName
     * @return Mage_Core_Model_Resource_Helper_Abstract
     */
    public function getResourceHelper($moduleName)
    {
        return Mage::getResourceHelper($moduleName);
    }

    /**
     * Creates and returns Magento_Db_Object_Table instance.
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $objectName
     * @param null $schemaName
     * @return Magento_Db_Object_Table
     */
    public function getMagentoDbObjectTable(Varien_Db_Adapter_Interface $adapter, $objectName, $schemaName = null)
    {
        return new Magento_Db_Object_Table($adapter, $objectName, $schemaName);
    }

    /**
     * Creates and returns Magento_Db_Object_View instance.
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $objectName
     * @param null $schemaName
     * @return Magento_Db_Object_View
     */
    public function getMagentoDbObjectView(Varien_Db_Adapter_Interface $adapter, $objectName, $schemaName = null)
    {
        return new Magento_Db_Object_View($adapter, $objectName, $schemaName);
    }

    /**
     * Creates and returns Magento_Db_Sql_Trigger instance
     *
     * @return Magento_Db_Sql_Trigger
     */
    public function getMagentoDbSqlTrigger()
    {
        return new Magento_Db_Sql_Trigger();
    }

    /**
     * Creates and returns Magento_Db_Object_Trigger instance
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param $objectName
     * @param null $schemaName
     * @return Magento_Db_Object_Trigger
     */
    public function getMagentoDbObjectTrigger(Varien_Db_Adapter_Interface $adapter, $objectName, $schemaName = null)
    {
        return new Magento_Db_Object_Trigger($adapter, $objectName, $schemaName);
    }
}
