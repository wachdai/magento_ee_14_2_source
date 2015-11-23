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
 * Staging merge settings of staging website type block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Merge_Settings_Website extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/staging/staging/merge/settings/website.phtml');
        $this->setId('staging_website_mapper');
        $this->setUseAjax(true);
        $this->setRowInitCallback($this->getJsObjectName().'.stagingWebsiteMapperRowInit');

        $this->setIsReadyForMerge(true);
    }

    /**
     * prepare layout
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareLayout()
    {
        $this->setChild('items',
            $this->getLayout()
                ->createBlock('enterprise_staging/adminhtml_staging_edit_tabs_item')
                ->setFieldNameSuffix('map[staging_items]')
        );

        $this->setChild('schedule',
            $this->getLayout()
                ->createBlock('enterprise_staging/adminhtml_staging_edit_tabs_schedule')
                ->setFieldNameSuffix('map[schedule]')
                ->setStagingJsObjectName($this->getJsObjectName())
        );
        return parent::_prepareLayout();
    }

    /**
     * Retrieve currently edited staging object
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function getStaging()
    {
        if (!($this->getData('staging') instanceof Enterprise_Staging_Model_Staging)) {
            $this->setData('staging', Mage::registry('staging'));
        }
        return $this->getData('staging');
    }

    /**
     * Return save url
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/mergePost', array('_current'=>true));
    }

    /**
     * Retrieve website collection
     *
     * @return Varien_Object
     */
    public function getWebsiteCollection()
    {
        $collection = Mage::getModel('core/website')
            ->getResourceCollection()
            ->addFieldToFilter('website_id',array('nin'=>array(0, $this->getStaging()->getStagingWebsiteId())));

        return $collection;
    }

    /**
     * Retrieve Staging Website Collection
     *
     * @return array
     */
    public function getStagingWebsiteCollection()
    {
        $staging = $this->getStaging();
        if ($staging) {
            $stagingWebsite = $this->getStaging()->getStagingWebsite();
            if ($stagingWebsite) {
                return array($stagingWebsite);
            }
        }
        return array();
    }

    /**
     * Retrieve stores collection
     *
     * @return Varien_Object
     */
    public function getAllStoresCollection()
    {
        return Mage::app()->getStores();
    }

    /**
     * Retrieve stores collection Json
     *
     * @return string (Json)
     */
    public function getAllStoresJson()
    {
        $stores = array();
        foreach ($this->getAllStoresCollection() as $store) {
            $stores[$store->getWebsiteId()][] = $store->getData();
        }
        if (!$stores) {
            return '{}';
        } else {
            return Mage::helper('core')->jsonEncode($stores);
        }
    }

    /**
     * Retrieve Main buttons html
     */
    public function getMainButtonsHtml()
    {
        $html = '';
        //$html = parent::getMainButtonsHtml();
        if($this->getIsReadyForMerge()){
            $html.= $this->getChildHtml('merge_button');
        }
        return $html;
    }
}
