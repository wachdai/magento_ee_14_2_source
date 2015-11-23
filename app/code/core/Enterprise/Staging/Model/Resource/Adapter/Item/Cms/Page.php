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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Staging adapter for CMS pages
 *
 * @category   Enterprise
 * @package    Enterprise_Staging
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Adapter_Item_Cms_Page
    extends Enterprise_Staging_Model_Resource_Adapter_Item_Default
{
    /**
     * Executed before merging staging store to master store
     *
     * @param string $entityName
     * @param mixed $fields
     * @param int $masterStoreId
     * @param int $stagingStoreId
     *
     * @return Enterprise_Staging_Model_Mysql4_Adapter_Item_Cms_Page
     */
    protected function _beforeStoreMerge($entityName, $fields, $masterStoreId, $stagingStoreId)
    {
        if ($entityName == 'cms/page_store') {
            $model = Mage::getResourceSingleton('cms/page_service');
            $model->unlinkConflicts($masterStoreId, $stagingStoreId);
        }
        return $this;
    }

    /**
     * Executed before rolling back backup to master store
     *
     * @param string $srcTable
     * @param string $targetTable
     * @param object $connection
     * @param mixed $fields
     * @param int $masterStoreId
     * @param int $stagingStoreId
     *
     * @return Enterprise_Staging_Model_Mysql4_Adapter_Item_Cms_Page
     */
    protected function _beforeStoreRollback($srcTable, $targetTable, $connection, $fields, $masterStoreId, $stagingStoreId)
    {
        if ($targetTable == 'cms/page_store') {
            $model = Mage::getResourceSingleton('cms/page_service');
            $model->unlinkConflicts($masterStoreId, $masterStoreId, $this->getTable($srcTable));
        }
        return $this;
    }
}
