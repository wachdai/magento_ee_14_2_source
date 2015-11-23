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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Cms Hierarchy Copy Form Container Block
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Manage extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Retrieve Delete Hierarchies Url
     *
     * @return string
     */
    public function getDeleteHierarchiesUrl()
    {
        return $this->getUrl('*/*/delete');
    }

    /**
     * Retrieve Copy Hierarchy Url
     *
     * @return string
     */
    public function getCopyHierarchyUrl()
    {
        return $this->getUrl('*/*/copy');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'manage_form',
            'method'    => 'post'
        ));

        $currentWebsite = $this->getRequest()->getParam('website');
        $currentStore   = $this->getRequest()->getParam('store');
        $excludeScopes = array();
        if ($currentStore) {
            $storeId = Mage::app()->getStore($currentStore)->getId();
            $excludeScopes = array(
                Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_STORE . $storeId
            );
        } elseif ($currentWebsite) {
            $websiteId = Mage::app()->getWebsite($currentWebsite)->getId();
            $excludeScopes = array(
                Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_WEBSITE . $websiteId
            );
        }
        $allStoreViews = $currentStore || $currentWebsite;
        $form->addField('scopes', 'multiselect', array(
            'name'      => 'scopes[]',
            'class'     => 'manage-select',
            'title'     => Mage::helper('enterprise_cms')->__('Manage Hierarchies'),
            'values'    => $this->_prepareOptions($allStoreViews, $excludeScopes)
        ));

        if ($currentWebsite) {
            $form->addField('website', 'hidden', array(
                'name'   => 'website',
                'value' => $currentWebsite,
            ));
        }
        if ($currentStore) {
            $form->addField('store', 'hidden', array(
                'name'   => 'store',
                'value' => $currentStore,
            ));
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare options for Manage select
     *
     * @param boolean $all
     * @param string $excludeScopes
     * @return array
     */
    protected function _prepareOptions($all = false, $excludeScopes)
    {
        $storeStructure = Mage::getSingleton('adminhtml/system_store')
                ->getStoresStructure($all);
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $options = array();

        $cmsHierarchyScopes = new Varien_Object();
        $cmsHierarchyScopes->setStoreStructure($storeStructure);
        $cmsHierarchyScopes->setExclude($excludeScopes);
        Mage::dispatchEvent('cms_hierarchy_manage_prepare_form', array('scopes' => $cmsHierarchyScopes));
        $excludeScopes = $cmsHierarchyScopes->getExclude();

        foreach ($storeStructure as $website) {
            $value = Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_WEBSITE . $website['value'];
            if (isset($website['children'])) {
                $website['value'] = in_array($value, $excludeScopes) ? array() : $value;
                $options[] = array(
                    'label' => $website['label'],
                    'value' => $website['value'],
                    'style' => 'border-bottom: none; font-weight: bold;',
                );
                foreach ($website['children'] as $store) {
                    if (isset($store['children']) && !in_array($store['value'], $excludeScopes)) {
                        $storeViewOptions = array();
                        foreach ($store['children'] as $storeView) {
                            $storeView['value'] = Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_STORE
                                                  . $storeView['value'];
                            if (!in_array($storeView['value'], $excludeScopes)) {
                                $storeView['label'] = str_repeat($nonEscapableNbspChar, 4) . $storeView['label'];
                                $storeViewOptions[] = $storeView;
                            }
                        }
                        if ($storeViewOptions) {
                            $options[] = array(
                                'label' => str_repeat($nonEscapableNbspChar, 4) . $store['label'],
                                'value' => $storeViewOptions
                            );
                        }
                    }
                }
            } elseif ($website['value'] == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                $website['value'] = in_array($website['value'], $excludeScopes)
                    ? array()
                    : Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_STORE
                        . Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
                $options[] = array(
                    'label' => $website['label'],
                    'value' => $website['value'],
                );
            }
        }
        return $options;
    }
}
