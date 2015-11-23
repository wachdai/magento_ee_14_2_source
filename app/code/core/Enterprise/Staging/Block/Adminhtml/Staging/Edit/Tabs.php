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
 * Staging edit tabs
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('enterprise_staging_tabs');
        $this->setDestElementId('enterprise_staging_form');
        $this->setTitle(Mage::helper('enterprise_staging')->__('Staging Website Information'));
    }

    /**
     * Preparing global layout
     *
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs
     */
    protected function _prepareLayout()
    {
        $type = $this->getStaging()->getType();
        if (!$type) {
            // try to find staging type in request parameters
            $type = $this->getRequest()->getParam('type', null);
        }

        if ($type) {
            $this->addTab('website', array(
                'label'     => Mage::helper('enterprise_staging')->__('General Information'),
                'content'   => $this->getLayout()
                    ->createBlock('enterprise_staging/adminhtml_staging_edit_tabs_website')
                    ->toHtml(),
            ));
        } else {
            $this->addTab('set', array(
                'label'     => Mage::helper('enterprise_staging')->__('Settings'),
                'content'   => $this->getLayout()
                    ->createBlock('enterprise_staging/adminhtml_staging_edit_tabs_settings')
                    ->toHtml(),
                'active'    => true
            ));
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrive current staging
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
}
