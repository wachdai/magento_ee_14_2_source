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
 * Staging History Item View
 *
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Log_View_Information_Staging extends Enterprise_Staging_Block_Adminhtml_Log_View_Information_Default
{
    /**
     * Retrieve staging website name
     *
     * @return string
     */
    public function getStagingWebsiteName()
    {
        return $this->getLog()->getStagingWebsiteName();
    }

    /**
     * Retrieve master website name
     *
     * @return string
     */
    public function getMasterWebsiteName()
    {
        return $this->getLog()->getMasterWebsiteName();
    }

    /**
     * Prepares store view mapping which was used in merge
     * in case store view mapping was not defined returns message
     *
     * @return mixed
     */
    public function getMegreStoreViewsMap()
    {
        $mapData = $this->_mapper->getMergeMapData();

        $map = array();

        if (isset($mapData['stores'])) {
            foreach ($mapData['stores'] as $store) {
                foreach ($store['from'] as $key => $storeViewId) {
                    $_fromStoreView = Mage::app()->getStore($storeViewId);
                    $_toStoreView = Mage::app()->getStore($store['to'][$key]);
                    if ($_fromStoreView && $_toStoreView) {
                        $map[] = array(
                            'from'  => $_fromStoreView,
                            'to'    => $_toStoreView
                        );
                    }
                }
            }
        }

        if (!count($map)) {
            return Mage::helper('enterprise_staging')->__('There was no mapping defined for store views.');
        }

        return $map;
    }

    /**
     * Get creation store view mapping
     *
     * @return mixed array if there are were defined some stores
     */
    public function getCreateStoreViewsMap()
    {
        $map = array();

        $mapData = $this->_mapper->getCreateMapData();
        if (isset($mapData['websites'])) {
            $website = array_shift($mapData['websites']);
            if (isset($website['stores'])) {
                foreach ($website['stores'] as $store) {
                    if (isset($store['use']) && $store['use'] >= 0) {
                        $map[] = $store;
                    }
                }
            }
        }

        if (!count($map)) {
            return Mage::helper('enterprise_staging')->__('There was no mapping defined for store views.');
        }

        return $map;
    }

}
