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
 * @package     Mage_XmlConnect
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Dashboard order graph helper
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Helper_Adminhtml_Dashboard_Order extends Mage_Adminhtml_Helper_Dashboard_Order
{
    /**
     * Re-init product collection
     *
     * @return null
     */
    public function initCollection()
    {
        $this->_collection = Mage::getResourceModel('reports/order_collection')
            ->prepareSummary($this->getParam('period'), 0, 0, (bool)$this->getParam('store'));

        if ($this->getParam('store')) {
            $this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
        } elseif (!$this->_collection->isLive()) {
            $this->_collection->addFieldToFilter('store_id', array(
                'eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId()
            ));
        }
        $this->_collection->load();
    }

    /**
     * Prepare price to display
     *
     * @param null|string $price
     * @param null|string $storeId
     * @return string
     */
    public function preparePrice($price, $storeId)
    {
        $baseCurrencyCode = (string)Mage::app()->getStore($storeId)->getBaseCurrencyCode();
        return Mage::app()->getLocale()->currency($baseCurrencyCode)->toCurrency($price);
    }
}
