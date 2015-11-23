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
 * Staging item xml collection
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Staging_Item_Xml_Collection extends Varien_Data_Collection
{
    /**
     * As this collection will be filled manually and there will be no call for load method
     * geSize will return result of count instead of original functionality.
     * This is done to avoid replacing of template.
     * return int
     *
     * @return int
     */
    public function getSize()
    {
        return $this->count();
    }

    /**
     * Adding staging items from configuration into collection as objects
     *
     * @param array $extendInfo
     * @return Enterprise_Staging_Model_Resource_Staging_Item_Xml_Collection
     */
    public function fillCollectionWithStagingItems($extendInfo = array())
    {
        $this->setExtendInfo($extendInfo);

        foreach (Mage::getSingleton('enterprise_staging/staging_config')->getStagingItems() as $stagingItem) {
            if ((int)$stagingItem->is_backend) {
                continue;
            }

            $this->addStagingItemToCollection($stagingItem);

            if ($stagingItem->extends) {
                foreach ($stagingItem->extends->children() as $extendItem) {
                    if (!Mage::getSingleton('enterprise_staging/staging_config')->isItemModuleActive($extendItem)) {
                         continue;
                    }
                    $this->addStagingItemToCollection($extendItem);
                }
            }
        }

        return $this;
    }

    /**
     * Add items into collection object
     *
     * @param Varien_Simplexml_Element $stagingItem
     * @return Enterprise_Staging_Model_Resource_Staging_Item_Xml_Collection
     */
    public function addStagingItemToCollection($stagingItem)
    {
        $extendInfo = $this->getExtendInfo();

        $_code = (string) $stagingItem->getName();

        $item = Mage::getModel('enterprise_staging/staging_item')
            ->loadFromXmlStagingItem($stagingItem);

        $disabled = false;
        $checked = true;
        $availabilityText = "";
        //process extend information
        if (!empty($extendInfo) && is_array($extendInfo) && isset($extendInfo[$_code])) {
            $item->addData($extendInfo[$_code]);
            if ($extendInfo[$_code]["disabled"]==true) {
                $disabled = true;
                $checked = false;
                $availabilityText = $extendInfo[$_code]["reason"];
            } else {
                $availabilityText = Mage::helper('enterprise_staging')->__('available');
            }
        }
        $item->setData('id', $_code);
        $item->setData('code', $_code);
        $item->setData('checked', $checked);
        $item->setData('disabled', $disabled);
        $item->setData('availability_text', $availabilityText);

        $this->addItem($item);

        return $this;
    }

    /**
     * Set extend info
     *
     * @param array $info
     * @return Enterprise_Staging_Model_Resource_Staging_Item_Xml_Collection
     */
    public function setExtendInfo($info)
    {
        $this->_extendInfo = $info;
        return $this;
    }

    /**
     * Get extend info
     *
     * @return array
     */
    public function getExtendInfo()
    {
        return $this->_extendInfo;
    }

    /**
     * Prepares array of codes of disabled items
     *
     * @return array
     */
    public function getDisabledItemCodes()
    {
        $rows = array();

        foreach ($this->getItems() as $item) {
            if ($item->getDisabled()) {
                $rows[] = $item->getCode();
            }
        }

        return $rows;
    }

    /**
     * Prepares array of codes of all items
     *
     * @return array
     */
    public function getItemCodes()
    {
        $rows = array();
        foreach ($this->getItems() as $item) {
            $rows[] = $item->getCode();
        }
        return $rows;
    }
}
