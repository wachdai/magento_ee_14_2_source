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
 * Adapter item category resource
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Adapter_Item_Category
    extends Enterprise_Staging_Model_Resource_Adapter_Item_Default
{
    /**
     * List of table codes which shouldn't process if product item were not chosen
     *
     * @var array
     */
    protected $_ignoreIfProductNotChosen     = array('category_product', 'category_product_index');

    /**
     * Create item table and records, run processes in website and store scopes
     *
     * @param string $entityName
     * @return Enterprise_Staging_Model_Resource_Adapter_Item_Category
     */
    protected function _createItem($entityName)
    {
        if (!$this->getStaging()->getMapperInstance()->hasStagingItem('product')) {
            if (strpos($entityName, 'product') !== false) {
                return $this;
            }
        }
        return parent::_createItem($entityName);
    }
}
