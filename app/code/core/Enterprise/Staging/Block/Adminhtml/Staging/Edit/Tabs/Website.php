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
 * Staging entities tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Edit_Tabs_Website extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setFieldNameSuffix('staging');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Website
     */
    protected function _prepareForm()
    {
        $form       = new Varien_Data_Form();
        $staging    = $this->getStaging();

        $masterWebsite = $staging->getMasterWebsite();
        if ($masterWebsite) {
            $_id = $masterWebsite->getId();

            $stagingWebsite = $staging->getStagingWebsite();
            if ($stagingWebsite) {
                $stagingWebsiteName = $stagingWebsite->getName();
            } else {
                $stagingWebsiteName = $masterWebsite->getName();
            }

            $fieldset = $form->addFieldset('website_fieldset_'.$_id,
                array('legend' => Mage::helper('enterprise_staging')->__('Staging Website')));

            $fieldset->addField('master_website_code_label_'.$_id, 'label',
                array(
                    'label' => Mage::helper('enterprise_staging')->__('Master Website Code'),
                    'value' => $masterWebsite->getCode()
                )
            );

            $fieldset->addField('master_website_id_'.$_id, 'hidden',
                array(
                    'label' => Mage::helper('enterprise_staging')->__('Master Website ID'),
                    'name'  => "websites[{$_id}][master_website_id]",
                    'value' => $_id
                )
            );

            $fieldset->addField('master_website_code_'.$_id, 'hidden',
                array(
                    'label' => Mage::helper('enterprise_staging')->__('Master Website Code'),
                    'name'  => "websites[{$_id}][master_website_code]",
                    'value' => $masterWebsite->getCode()
                )
            );

            if ($stagingWebsite) {
                $fieldset->addField('staging_website_code_'.$_id, 'label',
                    array(
                        'label' => Mage::helper('enterprise_staging')->__('Staging Website Code'),
                        'name'  => "websites[{$_id}][code]",
                        'value' => $stagingWebsite->getCode()
                    )
                );

                $fieldset->addField('staging_website_name_'.$_id, 'label',
                    array(
                        'label' => Mage::helper('enterprise_staging')->__('Staging Website Name'),
                        'name'  => "websites[{$_id}][name]",
                        'value' => $stagingWebsite->getName()
                    )
                );

                $element = $fieldset->addField('staging_website_base_url_'.$_id, 'label',
                    array(
                        'label' => Mage::helper('enterprise_staging')->__('Base URL'),
                        'name'  => "websites[{$_id}][base_url]",
                        'value' => $stagingWebsite->getConfig('web/unsecure/base_url')
                    )
                );
                if ($stagingWebsite->getStoresCount() > 0) {
                    $element->setRenderer($this->getLayout()->createBlock(
                        'enterprise_staging/adminhtml_widget_form_renderer_fieldset_link'
                    ));
                }

                $element = $fieldset->addField('staging_website_base_secure_url_'.$_id, 'label',
                    array(
                        'label' => Mage::helper('enterprise_staging')->__('Secure Base URL'),
                        'name'  => "websites[{$_id}][base_secure_url]",
                        'value' => $stagingWebsite->getConfig('web/secure/base_url')
                    )
                );
                if ($stagingWebsite->getStoresCount() > 0) {
                    $element->setRenderer($this->getLayout()->createBlock(
                        'enterprise_staging/adminhtml_widget_form_renderer_fieldset_link'
                    ));
                }

                $fieldset->addField('staging_website_id_'.$_id, 'hidden',
                    array(
                        'label' => Mage::helper('enterprise_staging')->__('Staging Website Id'),
                        'name'  => "websites[{$_id}][staging_website_id]",
                        'value' => $stagingWebsite->getId()
                    )
                );
            } else {
                $fieldset->addField('staging_website_code_'.$_id, 'text',
                    array(
                        'label'    => Mage::helper('enterprise_staging')->__('Staging Website Code'),
                        'name'     => "websites[{$_id}][code]",
                        'value'    =>
                            Mage::helper('enterprise_staging/website')->generateWebsiteCode($masterWebsite->getCode()),
                        'required' => true
                    )
                );

                $fieldset->addField('staging_website_name_'.$_id, 'text',
                    array(
                        'label'    => Mage::helper('enterprise_staging')->__('Staging Website Name'),
                        'name'     => "websites[{$_id}][name]",
                        'value'    => $masterWebsite->getName() . ' '
                            . Mage::helper('enterprise_staging')->__('(Staging Copy)'),
                        'required' => true
                    )
                );

                if (!Mage::getSingleton('enterprise_staging/entry')->isAutomatic()) {
                    $fieldset->addField('staging_website_base_url_'.$_id, 'text',
                        array(
                            'label' => Mage::helper('enterprise_staging')->__('Base URL'),
                            'name'  => "websites[{$_id}][base_url]",
                            'value' => '',
                            'class' => 'validate-url',
                            'note'  => Mage::helper('enterprise_staging')->__("Please make sure that Base URL ends with '/' (slash), e.g. http://yourdomain/magento/"),
                            'required' => true
                        )
                    );

                    $fieldset->addField('staging_website_base_secure_url_'.$_id, 'text',
                        array(
                            'label' => Mage::helper('enterprise_staging')->__('Secure Base Url'),
                            'name'  => "websites[{$_id}][base_secure_url]",
                            'value' => '',
                            'class' => 'validate-secure-url',
                            'note'  => Mage::helper('enterprise_staging')->__("Please make sure that Base URL ends with '/' (slash), e.g. https://yourdomain/magento/"),
                            'required' => true
                        )
                    );
                }
            }

            $fieldset->addField('staging_website_visibility_'.$_id, 'select', array(
                'label'     => Mage::helper('enterprise_staging')->__('Frontend Restriction'),
                'title'     => Mage::helper('enterprise_staging')->__('Frontend Restriction'),
                'name'      => "websites[{$_id}][visibility]",
                'value'     => $stagingWebsite ? $stagingWebsite->getVisibility()
                    : Enterprise_Staging_Model_Staging_Config::VISIBILITY_REQUIRE_HTTP_AUTH,
                'options'   => Mage::getSingleton('enterprise_staging/staging_config')->getVisibilityOptionArray()
            ));

            $fieldset->addField('staging_website_master_login_'.$_id, 'text',
                array(
                    'label'    => Mage::helper('enterprise_staging')->__('HTTP Login'),
                    'class'    => 'input-text validate-login',
                    'name'     => "websites[{$_id}][master_login]",
                    'required' => true,
                    'value'    => $stagingWebsite ? $stagingWebsite->getMasterLogin() : ''
                )
            );

            $fieldset->addField('staging_website_master_password_'.$_id, 'text',
                array(
                    'label'    => Mage::helper('enterprise_staging')->__('HTTP Password'),
                    'class'    => 'input-text validate-password',
                    'name'     => "websites[{$_id}][master_password]",
                    'required' => true,
                    'value'    => $stagingWebsite ? Mage::helper('core')->decrypt($stagingWebsite->getMasterPassword())
                        : ''
                )
            );

            if ($stagingWebsite) {
                foreach ($stagingWebsite->getData() as $key => $value) {
                    if ($key == 'master_password') {
                        continue;
                    }
                    $values[$key.'_'.$_id] = $value;
                }
                $form->addValues($values);
            }

            $this->_initWebsiteItems($form , $staging, $_id, $stagingWebsite);

            $this->_initWebsiteStore($form , $masterWebsite, $stagingWebsite);
        }

        $form->addFieldNameSuffix($this->getFieldNameSuffix());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Init Website Item Elements
     *
     * @param Varien_Data_Form $form
     * @param Staging Object $staging
     * @param int $website_id
     * @param Mage_Core_Model_Website $stagingWebsite
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Website
     */
    protected function _initWebsiteItems($form, $staging, $websiteId, $stagingWebsite = null)
    {
        if (empty($staging)) {
            return $this;
        }

        if ($stagingWebsite) {
            $fieldset = $form->addFieldset('staging_website_items',
                array('legend'=>Mage::helper('enterprise_staging')->__('Items to Copy')));
        } else {
            $fieldset = $form->addFieldset('staging_website_items',
                array('legend' => Mage::helper('enterprise_staging')->__('Select Original Website Content to be Copied to the Staging Website')));
        }

        $usedItemCodes = $staging->getStagingItemCodes();

        foreach (Mage::getSingleton('enterprise_staging/staging_config')->getStagingItems() as $stagingItem) {
            if ((int)$stagingItem->is_backend) {
                continue;
            }
            $_code = (string) $stagingItem->getName();

            if ($stagingWebsite) {
                if (in_array($_code, $usedItemCodes)) {
                    $this->_initWebsiteItemsStored($fieldset, $stagingItem, $_code);
                }
            } else {
                $this->_initWebsiteItemsNew($fieldset, $stagingItem, $websiteId, $_code);
            }
        }

        if (!$stagingWebsite) {
            $fieldset->addField('staging_website_item_check' , 'hidden' ,
                array(
                    'lable'     => 'Staging Website Item Check',
                    'name'      => 'item_check',
                    'value'     => 'check',
                    'class'     => 'staging_website_item_check'
                )
            );
        }

        return $this;
    }

    /**
     * Init Website Item New Elements
     *
     * @param Varien_Data_Form $fieldset
     * @param Varien_Simplexml_Element $stagingItem
     * @param int $websiteId
     * @param string $_code
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Website
     */
    protected function _initWebsiteItemsNew($fieldset, $stagingItem, $websiteId, $_code)
    {
        $fieldset->addField('staging_website_items_'.$_code, 'checkbox',
            array(
                'label'    => (string) $stagingItem->label,
                'name'     => "staging_items[$_code][staging_item_code]",
                'value'    => $_code,
                'checked'  => true,
            )
        );

        return $this;
    }

    /**
     * Init Website Item Stores Elements
     *
     * @param Varien_Data_Form $fieldset
     * @param Varien_Simplexml_Element $stagingItem
     * @param string $_code
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Website
     */
    protected function _initWebsiteItemsStored($fieldset, $stagingItem, $_code)
    {
        $fieldset->addField('staging_website_items_'.$_code, 'label',
            array('label' => (string) $stagingItem->label)
        );
        return $this;
    }

    /**
     * Init Website Store Elements
     *
     * @param Varien_Data_Form $form
     * @param Mage_Core_Model_Website $masterWebsite
     * @param Mage_Core_Model_Website $stagingWebsite
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Website
     */
    protected function _initWebsiteStore($form, $masterWebsite, $stagingWebsite = null)
    {
        if (empty($masterWebsite)) {
            return $this;
        }

        if ($stagingWebsite) {
            $fieldset = $form->addFieldset('staging_website_stores',
                array('legend' => Mage::helper('enterprise_staging')->__('Store Views to Copy')));
        } else {
            $fieldset = $form->addFieldset('staging_website_stores',
                array('legend' => Mage::helper('enterprise_staging')->__('Select Original Website Store Views to be Copied to Staging Website')));
        }

        if ($stagingWebsite) {
            $_storeGroups       = $stagingWebsite->getGroups();
            $_storeGroupsCount  = $stagingWebsite->getGroupsCount();
        } else {
            $_storeGroups       = $masterWebsite->getGroups();
            $_storeGroupsCount  = $masterWebsite->getGroupsCount();
        }
        $noStores = true;
        foreach ($_storeGroups as $group) {
            if ($group->getStoresCount()) {
                $noStores = false;
                $_stores = $group->getStores();
                $this->_initStoreGroup($fieldset, $group, $stagingWebsite);
                foreach ($_stores as $storeView) {
                    $this->_initStoreView($fieldset, $storeView, $stagingWebsite);
                }
            }
        }
        if ($noStores) {
            if ($stagingWebsite) {
                $fieldset->addField('staging_no_stores', 'label',
                    array(
                        'label' => Mage::helper('enterprise_staging')->__('There are no store views to be copied.')
                    )
                );
            } else {
                $fieldset->addField('staging_no_stores', 'label',
                    array(
                        'label' => Mage::helper('enterprise_staging')->__('There are no store views for copying.')
                    )
                );
            }
        }
        return $this;
    }

    /**
     * Init Staging Store Group
     *
     * @param Varien_Data_Form $fieldset
     * @param Mage_Core_Model_Store_Group $group
     * @param Mage_Core_Model_Website $stagingWebsite
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Website
     */
    protected function _initStoreGroup($fieldset, $group, $stagingWebsite = null)
    {
        $fieldset->addField('staging_store_group_' . $group->getId(), 'label',
            array('label' => $group->getName())
        );
        return $this;
    }

    /**
     * Init Staging Store Views
     *
     * @param Varien_Data_Form $fieldset
     * @param Mage_Core_Model_Store $storeView
     * @param Mage_Core_Model_Website $stagingWebsite
     * @return Enterprise_Staging_Block_Manage_Staging_Edit_Tabs_Website
     */
    protected function _initStoreView($fieldset, $storeView, $stagingWebsite = null)
    {
        $_shift = str_repeat(' ', 6);
        if (!$stagingWebsite) {
            $_id        = $storeView->getId();
            $websiteId  = $storeView->getWebsiteId();

            $fieldset->addField('master_store_use_'.$_id, 'checkbox',
                array(
                    'label'    => $_shift . $storeView->getName(),
                    'name'     => "websites[{$websiteId}][stores][{$_id}][use]",
                    'value'    => $storeView->getId(),
                    'checked'  => true
                )
            );

            $fieldset->addField('master_store_id_'.$_id, 'hidden',
                array(
                    'label' => Mage::helper('enterprise_staging')->__('Master Store ID'),
                    'name'  => "websites[{$websiteId}][stores][{$_id}][master_store_id]",
                    'value' => $storeView->getId(),
                )
            );

            $fieldset->addField('master_store_code_'.$_id, 'hidden',
                array(
                    'label' => Mage::helper('enterprise_staging')->__('Master Store Code'),
                    'name'  => "websites[{$websiteId}][stores][{$_id}][master_store_code]",
                    'value' => $storeView->getCode()
                )
            );

            $fieldset->addField('staging_store_code_'.$_id, 'hidden',
                array(
                    'label' => Mage::helper('enterprise_staging')->__('Staging Store Code'),
                    'name'  => "websites[{$websiteId}][stores][{$_id}][code]",
                    'value' => Mage::helper('enterprise_staging/store')->generateStoreCode($storeView->getCode())
                )
            );

            $fieldset->addField('staging_store_name_'.$_id, 'hidden',
                array(
                    'label' => Mage::helper('enterprise_staging')->__('Staging Store Name'),
                    'name'  => "websites[{$websiteId}][stores][{$_id}][name]",
                    'value' => $storeView->getName()
                )
            );
        } else {
            $fieldset->addField('staging_store_'.$storeView->getId(), 'label',
                array(
                    'label' => $_shift . $storeView->getName()
                )
            );
        }
        return $this;
    }

    /**
     * Retrive staging object from setted data if not from registry
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
