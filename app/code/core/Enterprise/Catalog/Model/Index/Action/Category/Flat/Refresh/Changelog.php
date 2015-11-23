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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Refresh category flat index by changelog action
 *
 * @category    Enterprise
 * @package     Enterprise_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh_Changelog
    extends Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh
{
    /**
     * Refresh entities by changelog
     *
     * @return Enterprise_Catalog_Model_Index_Action_Category_Flat_Refresh_Changelog
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute()
    {
        if (!$this->_isFlatIndexerEnabled()) {
            return $this;
        }
        $this->_validate();
        $changedIds = $this->_selectChangedIds();
        if (is_array($changedIds) && count($changedIds) > 0) {
            $idsBatches = array_chunk($changedIds, Mage::helper('enterprise_index')->getBatchSize());
            foreach ($idsBatches as $changedIds) {
                $this->_reindex($changedIds);
            }
            $this->_setChangelogValid();
        }
        return $this;
    }
}
