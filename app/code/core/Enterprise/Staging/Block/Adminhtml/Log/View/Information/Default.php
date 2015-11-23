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
 * Staging information renderer
 *
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Log_View_Information_Default extends Mage_Adminhtml_Block_Widget
{
    protected $_mapper;
    protected $_items;

    protected function _construct()
    {
        $this->getLog()->restoreMap();
        $this->_mapper = $this->getLog()->getStaging()->getMapperInstance();
    }

    /**
     * Retrieve currently viewing log
     *
     * @return Enterprise_Staging_Model_Staging_Log
     */
    public function getLog()
    {
        if (!($this->getData('log') instanceof Enterprise_Staging_Model_Staging_Log)) {
            $this->setData('log', Mage::registry('log'));
        }
        return $this->getData('log');
    }

    /**
     * Prepares array of staging items related to proccess of rollback, create or merge
     *
     * @return array
     */
    public function getItems()
    {
        if (!$this->_items) {
            $stagingItems = $this->_mapper->getStagingItems();
            $items = array();
            if ($stagingItems) {
                foreach ($stagingItems as $code => $item) {
                    $items[$code] = array(
                        'code' => $code,
                        'label' => (string)$item->label
                    );
                }
            } else {
                $items = $this->__('No information available.');
            }
            $this->_items = $items;
        }

        return $this->_items;
    }
}
