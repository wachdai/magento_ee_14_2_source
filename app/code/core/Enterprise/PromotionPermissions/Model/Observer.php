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
 * @package     Enterprise_PromotionPermissions
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Promotion Permissions Observer
 *
 * @category    Enterprise
 * @package     Enterprise_PromotionPermissions
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_PromotionPermissions_Model_Observer
{
    /**
     * Instance of http request
     *
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * Edit Catalog Rules flag
     *
     * @var boolean
     */
    protected $_canEditCatalogRules;

    /**
     * Edit Sales Rules flag
     *
     * @var boolean
     */
    protected $_canEditSalesRules;

    /**
     * Edit Reminder Rules flag
     *
     * @var boolean
     */
    protected $_canEditReminderRules;

    /**
     * Enterprise_Banner flag
     *
     * @var boolean
     */
    protected $_isEnterpriseBannerEnabled;

    /**
     * Enterprise_Reminder flag
     *
     * @var boolean
     */
    protected $_isEnterpriseReminderEnabled;

    /**
     * Promotion Permissions Observer class constructor
     *
     * Sets necessary data
     */
    public function __construct()
    {
        $this->_request = Mage::app()->getRequest();
        // Set necessary flags
        $this->_canEditCatalogRules = Mage::helper('enterprise_promotionpermissions')->getCanAdminEditCatalogRules();
        $this->_canEditSalesRules = Mage::helper('enterprise_promotionpermissions')->getCanAdminEditSalesRules();
        $this->_canEditReminderRules = Mage::helper('enterprise_promotionpermissions')->getCanAdminEditReminderRules();

        $this->_isEnterpriseBannerEnabled = Mage::helper('enterprise_promotionpermissions')
            ->isModuleEnabled('Enterprise_Banner');
        $this->_isEnterpriseReminderEnabled = Mage::helper('enterprise_promotionpermissions')
            ->isModuleEnabled('Enterprise_Reminder');
    }

    /**
     * Handle core_block_abstract_to_html_before event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function coreBlockAbstractToHtmlBefore($observer)
    {
         /** @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        $blockNameInLayout = $block->getNameInLayout();
        switch ($blockNameInLayout) {
            // Handle General Tab on Edit Reminder Rule page
            case 'adminhtml_reminder_edit_tab_general' :
                if (!$this->_canEditReminderRules) {
                    $block->setCanEditReminderRule(false);
                }
                break;
        }
    }

    /**
     * Handle adminhtml_block_html_before event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function adminhtmlBlockHtmlBefore($observer)
    {
        /** @var $block Mage_Adminhtml_Block_Template */
        $block = $observer->getBlock();
        $blockNameInLayout = $block->getNameInLayout();
        switch ($blockNameInLayout) {
            // Handle blocks related to Mage_CatalogRule module
            case 'promo_catalog' :
                if (!$this->_canEditCatalogRules) {
                    $block->removeButton('add');
                    $block->removeButton('apply_rules');
                }
                break;
            case 'promo_catalog_edit' :
                if (!$this->_canEditCatalogRules) {
                    $block->removeButton('delete');
                    $block->removeButton('save');
                    $block->removeButton('save_and_continue_edit');
                    $block->removeButton('save_apply');
                    $block->removeButton('reset');
                }
                break;
            case 'promo_catalog_edit_tab_main' :
            case 'promo_catalog_edit_tab_actions' :
            case 'promo_catalog_edit_tab_conditions' :
                if (!$this->_canEditCatalogRules) {
                    $block->getForm()->setReadonly(true, true);
                }
                break;
            // Handle blocks related to Mage_SalesRule module
            case 'promo_quote' :
                if (!$this->_canEditSalesRules) {
                    $block->removeButton('add');
                }
                break;
            case 'promo_quote_edit' :
                if (!$this->_canEditSalesRules) {
                    $block->removeButton('delete');
                    $block->removeButton('save');
                    $block->removeButton('save_and_continue_edit');
                    $block->removeButton('reset');
                }
                break;
            case 'promo_quote_edit_tab_main':
                if (!$this->_canEditSalesRules) {
                    $block->unsetChild('form_after');
                }
            // no break needed
            case 'promo_quote_edit_tab_actions' :
            case 'promo_quote_edit_tab_conditions' :
            case 'promo_quote_edit_tab_labels' :
                if (!$this->_canEditSalesRules) {
                    $block->getForm()->setReadonly(true, true);
                }
                break;
            // Handle blocks related to Enterprise_Reminder module
            case 'enterprise_reminder' :
                if (!$this->_canEditReminderRules) {
                    $block->removeButton('add');
                }
                break;
            case 'adminhtml_reminder_edit' :
                if (!$this->_canEditReminderRules) {
                    $block->removeButton('save');
                    $block->removeButton('delete');
                    $block->removeButton('reset');
                    $block->removeButton('save_and_continue_edit');
                    $block->removeButton('run_now');
                }
                break;
            case 'adminhtml_reminder_edit_tab_conditions' :
            case 'adminhtml_reminder_edit_tab_templates' :
                if (!$this->_canEditReminderRules) {
                    $block->getForm()->setReadonly(true, true);
                }
                break;
            // Handle blocks related to Enterprise_Banner module
            case 'related_catalogrule_banners_grid' :
                if ($this->_isEnterpriseBannerEnabled && !$this->_canEditCatalogRules) {
                    $block->getColumn('in_banners')
                        ->setDisabledValues(Mage::getModel('enterprise_banner/banner')->getCollection()->getAllIds());
                    $block->getColumn('in_banners')->setDisabled(true);
                }
                break;
            case 'related_salesrule_banners_grid' :
                if ($this->_isEnterpriseBannerEnabled && !$this->_canEditSalesRules) {
                    $block->getColumn('in_banners')
                        ->setDisabledValues(Mage::getModel('enterprise_banner/banner')->getCollection()->getAllIds());
                    $block->getColumn('in_banners')->setDisabled(true);
                }
                break;
            case 'promo_quote_edit_tabs' :
                if ($this->_isEnterpriseBannerEnabled && !$this->_canEditSalesRules) {
                    $relatedBannersBlock = $block->getChild('salesrule.related.banners');
                    if (!is_null($relatedBannersBlock)) {
                        $relatedBannersBlock->unsetChild('banners_grid_serializer');
                    }
                }
                break;
            case 'promo_catalog_edit_tabs' :
                if ($this->_isEnterpriseBannerEnabled && !$this->_canEditCatalogRules) {
                    $relatedBannersBlock = $block->getChild('catalogrule.related.banners');
                    if (!is_null($relatedBannersBlock)) {
                        $relatedBannersBlock->unsetChild('banners_grid_serializer');
                    }
                }
                break;
        }
    }

    /**
     * Handle controller_action_predispatch event
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function controllerActionPredispatch($observer)
    {
        $controllerAction = $observer->getControllerAction();
        $controllerActionName = $this->_request->getActionName();
        $forbiddenActionNames = array('new', 'applyRules', 'save', 'delete', 'run');

        if (in_array($controllerActionName, $forbiddenActionNames)
            && ((!$this->_canEditSalesRules
            && $controllerAction instanceof Mage_Adminhtml_Promo_QuoteController)
            || (!$this->_canEditCatalogRules
            && $controllerAction instanceof Mage_Adminhtml_Promo_CatalogController)
            || ($this->_isEnterpriseReminderEnabled && !$this->_canEditReminderRules
            && $controllerAction instanceof Enterprise_Reminder_Adminhtml_ReminderController))
        ) {
            $this->_forward();
        }
    }

    /**
     * Forward current request
     *
     * @param string $action
     * @param string $module
     * @param string $controller
     * @return void
     */
    protected function _forward($action = 'denied', $module = null, $controller = null)
    {
        if ($this->_request->getActionName() === $action
            && (null === $module || $this->_request->getModuleName() === $module)
            && (null === $controller || $this->_request->getControllerName() === $controller)
        ) {
            return;
        }

        $this->_request->initForward();

        if ($module) {
            $this->_request->setModuleName($module);
        }
        if ($controller) {
            $this->_request->setControllerName($controller);
        }
        $this->_request->setActionName($action)->setDispatched(false);
    }
}
