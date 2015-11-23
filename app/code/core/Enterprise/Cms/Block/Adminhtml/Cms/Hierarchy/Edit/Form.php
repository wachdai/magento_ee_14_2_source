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
 * Cms Pages Tree Edit Form Block
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Currently selected store in store switcher
     * @var null|int
     */
    protected $_currentStore = null;

    /**
     * Define custom form template for block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/cms/hierarchy/edit.phtml');
        $this->_currentStore = $this->getRequest()->getParam('store');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save'),
            'method'    => 'post'
        ));

        /**
         * Define general properties for each node
         */
        $fieldset   = $form->addFieldset('node_properties_fieldset', array(
            'legend'    => Mage::helper('enterprise_cms')->__('Page Properties')
        ));

        $fieldset->addField('nodes_data', 'hidden', array(
            'name'      => 'nodes_data'
        ));

        $fieldset->addField('use_default_scope_property', 'hidden', array(
            'name'      => 'use_default_scope_property'
        ));

        $currentWebsite = $this->getRequest()->getParam('website');
        $currentStore   = $this->getRequest()->getParam('store');
        if ($currentStore) {
            $fieldset->addField('store', 'hidden', array(
                'name'   => 'store',
                'value' => $currentStore,
            ));
        }
        if ($currentWebsite) {
            $fieldset->addField('website', 'hidden', array(
                'name'   => 'website',
                'value' => $currentWebsite,
            ));
        }

        $fieldset->addField('removed_nodes', 'hidden', array(
            'name'      => 'removed_nodes'
        ));

        $fieldset->addField('node_id', 'hidden', array(
            'name'      => 'node_id'
        ));

        $fieldset->addField('node_page_id', 'hidden', array(
            'name'      => 'node_page_id'
        ));

        $fieldset->addField('node_label', 'text', array(
            'name'      => 'label',
            'label'     => Mage::helper('enterprise_cms')->__('Title'),
            'required'  => true,
            'onchange'   => 'hierarchyNodes.nodeChanged()',
            'tabindex'   => '10'
        ));

        $fieldset->addField('node_identifier', 'text', array(
            'name'      => 'identifier',
            'label'     => Mage::helper('enterprise_cms')->__('URL Key'),
            'required'  => true,
            'class'     => 'validate-identifier',
            'onchange'   => 'hierarchyNodes.nodeChanged()',
            'tabindex'   => '20'
        ));

        $fieldset->addField('node_label_text', 'note', array(
            'label'     => Mage::helper('enterprise_cms')->__('Title')
        ));

        $fieldset->addField('node_identifier_text', 'note', array(
            'label'     => Mage::helper('enterprise_cms')->__('URL Key')
        ));

        $fieldset->addField('node_preview', 'link', array(
            'label'     => Mage::helper('enterprise_cms')->__('Preview'),
            'href'      => '#',
            'value'     => Mage::helper('enterprise_cms')->__('No preview available'),
        ));

        $yesNoOptions = Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray();

        /**
         * Define field set with elements for root nodes
         */
        if (Mage::helper('enterprise_cms/hierarchy')->isMetadataEnabled()) {
            $fieldset   = $form->addFieldset('metadata_fieldset', array(
                'legend'    => Mage::helper('enterprise_cms')->__('Render Metadata in HTML Head')
            ));


            $fieldset->addField('meta_first_last', 'select', array(
                'label'     => Mage::helper('enterprise_cms')->__('First'),
                'title'     => Mage::helper('enterprise_cms')->__('First'),
                'name'      => 'meta_first_last',
                'values'   => $yesNoOptions,
                'onchange'   => 'hierarchyNodes.nodeChanged()',
                'container_id' => 'field_meta_first_last',
                'tabindex'   => '30',
            ));

            $fieldset->addField('meta_next_previous', 'select', array(
                'label'     => Mage::helper('enterprise_cms')->__('Next/Previous'),
                'title'     => Mage::helper('enterprise_cms')->__('Next/Previous'),
                'name'      => 'meta_next_previous',
                'values'   => $yesNoOptions,
                'onchange'   => 'hierarchyNodes.nodeChanged()',
                'container_id' => 'field_meta_next_previous',
                'tabindex'   => '40'
            ));

            $fieldset->addField('meta_cs_enabled', 'select', array(
                'label'     => Mage::helper('enterprise_cms')->__('Enable Chapter/Section'),
                'title'     => Mage::helper('enterprise_cms')->__('Enable Chapter/Section'),
                'name'      => 'meta_cs_enabled',
                'values'    => $yesNoOptions,
                'onchange'  => 'hierarchyNodes.nodeChanged()',
                'container_id' => 'field_meta_cs_enabled',
                'note'      => Mage::helper('enterprise_cms')->__('Enables Chapter/Section functionality for this node, its sub-nodes and pages'),
                'tabindex'  => '45'
            ));

            $fieldset->addField('meta_chapter_section', 'select', array(
                'label'     => Mage::helper('enterprise_cms')->__('Chapter/Section'),
                'title'     => Mage::helper('enterprise_cms')->__('Chapter/Section'),
                'name'      => 'meta_chapter_section',
                'values'    => Mage::getSingleton('enterprise_cms/source_hierarchy_menu_chapter')->toOptionArray(),
                'onchange'  => 'hierarchyNodes.nodeChanged()',
                'container_id' => 'field_meta_chapter_section',
                'note'      => Mage::helper('enterprise_cms')->__('Defines this node as Chapter/Section'),
                'tabindex'  => '50'
            ));
        }

        /**
         * Pagination options
         */
        $pagerFieldset   = $form->addFieldset('pager_fieldset', array(
            'legend'    => Mage::helper('enterprise_cms')->__('Pagination Options for Nested Pages')
        ));

        $pagerFieldset->addField('pager_visibility', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('Enable Pagination'),
            'name'      => 'pager_visibility',
            'values'    => Mage::getSingleton('enterprise_cms/source_hierarchy_visibility')->toOptionArray(),
            'value'     => Enterprise_Cms_Helper_Hierarchy::METADATA_VISIBILITY_PARENT,
            'onchange'  => "hierarchyNodes.metadataChanged('pager_visibility', 'pager_fieldset')",
            'tabindex'  => '70'
        ));
        $pagerFieldset->addField('pager_frame', 'text', array(
            'name'      => 'pager_frame',
            'label'     => Mage::helper('enterprise_cms')->__('Frame'),
            'class'     => 'validate-digits',
            'onchange'  => 'hierarchyNodes.nodeChanged()',
            'container_id' => 'field_pager_frame',
            'note'      => Mage::helper('enterprise_cms')->__('How many Links to display at once'),
            'tabindex'  => '80'
        ));
        $pagerFieldset->addField('pager_jump', 'text', array(
            'name'      => 'pager_jump',
            'label'     => Mage::helper('enterprise_cms')->__('Frame Skip'),
            'class'     => 'validate-digits',
            'onchange'  => 'hierarchyNodes.nodeChanged()',
            'container_id' => 'field_pager_jump',
            'note'      => Mage::helper('enterprise_cms')->__('If the Current Frame Position does not cover Utmost Pages, will render Link to Current Position plus/minus this Value'),
            'tabindex'  => '90'
        ));

        /**
         * Context menu options
         */
        $menuFieldset   = $form->addFieldset('menu_fieldset', array(
            'legend'    => Mage::helper('enterprise_cms')->__('Page Navigation Menu Options')
        ));

        $menuFieldset->addField('menu_excluded', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('Exclude from Navigation Menu'),
            'name'      => 'menu_excluded',
            'values'    => $yesNoOptions,
            'onchange'   => "hierarchyNodes.nodeChanged()",
            'container_id' => 'field_menu_excluded',
            'tabindex'  => '100'
        ));

        $menuFieldset->addField('menu_visibility', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('Show in Navigation Menu'),
            'name'      => 'menu_visibility',
            'values'    => $yesNoOptions,
            'onchange'   => "hierarchyNodes.metadataChanged('menu_visibility', 'menu_fieldset')",
            'container_id' => 'field_menu_visibility',
            'tabindex'  => '110'
        ));

        $menuFieldset->addField('menu_layout', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('Menu Layout'),
            'name'      => 'menu_layout',
            'values'    => Mage::getSingleton('enterprise_cms/source_hierarchy_menu_layout')->toOptionArray(true),
            'onchange'   => "hierarchyNodes.nodeChanged()",
            'container_id' => 'field_menu_layout',
            'tabindex'  => '115'
        ));

        $menuBriefOptions = array(
            array('value' => 1, 'label' => Mage::helper('enterprise_cms')->__('Only Children')),
            array('value' => 0, 'label' => Mage::helper('enterprise_cms')->__('Neighbours and Children')),
        );
        $menuFieldset->addField('menu_brief', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('Menu Detalization'),
            'name'      => 'menu_brief',
            'values'    => $menuBriefOptions,
            'onchange'   => "hierarchyNodes.nodeChanged()",
            'container_id' => 'field_menu_brief',
            'tabindex'  => '120'
        ));
        $menuFieldset->addField('menu_levels_down', 'text', array(
            'name'      => 'menu_levels_down',
            'label'     => Mage::helper('enterprise_cms')->__('Maximal Depth'),
            'class'     => 'validate-digits',
            'onchange'  => 'hierarchyNodes.nodeChanged()',
            'container_id' => 'field_menu_levels_down',
            'note'      => Mage::helper('enterprise_cms')->__('Node Levels to Include'),
            'tabindex'  => '130'
        ));
        $menuFieldset->addField('menu_ordered', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('List Type'),
            'title'     => Mage::helper('enterprise_cms')->__('List Type'),
            'name'      => 'menu_ordered',
            'values'    => Mage::getSingleton('enterprise_cms/source_hierarchy_menu_listtype')->toOptionArray(),
            'onchange'  => 'hierarchyNodes.menuListTypeChanged()',
            'container_id' => 'field_menu_ordered',
            'tabindex'  => '140'
        ));
        $menuFieldset->addField('menu_list_type', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('List Style'),
            'title'     => Mage::helper('enterprise_cms')->__('List Style'),
            'name'      => 'menu_list_type',
            'values'    => Mage::getSingleton('enterprise_cms/source_hierarchy_menu_listmode')->toOptionArray(),
            'onchange'  => 'hierarchyNodes.nodeChanged()',
            'container_id' => 'field_menu_list_type',
            'tabindex'  => '150'
        ));

        /**
         * Top menu options
         */
        $menuFieldset   = $form->addFieldset('top_menu_fieldset', array(
            'legend'    => Mage::helper('enterprise_cms')->__('Main Navigation Menu Options')
        ));

        $menuFieldset->addField('top_menu_excluded', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('Exclude from Navigation Menu'),
            'name'      => 'top_menu_excluded',
            'values'    => $yesNoOptions,
            'onchange'   => "hierarchyNodes.nodeChanged()",
            'container_id' => 'field_top_menu_excluded',
            'tabindex'  => '170'
        ));

        $menuFieldset->addField('top_menu_visibility', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('Show in Navigation Menu'),
            'name'      => 'top_menu_visibility',
            'values'    => $yesNoOptions,
            'onchange'   => "hierarchyNodes.metadataChanged('top_menu_visibility', 'top_menu_fieldset')",
            'container_id' => 'field_top_menu_visibility',
            'tabindex'  => '160'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve buttons HTML for Cms Page Grid
     *
     * @return string
     */
    public function getPageGridButtonsHtml()
    {
        $addButtonData = array(
            'id'        => 'add_cms_pages',
            'label'     => Mage::helper('enterprise_cms')->__('Add Selected Page(s) to Tree'),
            'onclick'   => 'hierarchyNodes.pageGridAddSelected()',
            'class'     => 'add'
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData($addButtonData)->toHtml();
    }

    /**
     * Retrieve Buttons HTML for Page Properties form
     *
     * @return string
     */
    public function getPagePropertiesButtons()
    {
        $buttons = array();
        $buttons[] = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'        => 'delete_node_button',
            'label'     => Mage::helper('enterprise_cms')->__('Remove From Tree'),
            'onclick'   => 'hierarchyNodes.deleteNodePage()',
            'class'     => 'delete'
        ))->toHtml();
        $buttons[] = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'        => 'cancel_node_button',
            'label'     => Mage::helper('enterprise_cms')->__('Cancel'),
            'onclick'   => 'hierarchyNodes.cancelNodePage()',
            'class'     => 'delete'
        ))->toHtml();
        $buttons[] = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'        => 'save_node_button',
            'label'     => Mage::helper('enterprise_cms')->__('Save'),
            'onclick'   => 'hierarchyNodes.saveNodePage()',
            'class'     => 'save'
        ))->toHtml();

        return join(' ', $buttons);
    }

    /**
     * Retrieve buttons HTML for Pages Tree
     *
     * @return string
     */
    public function getTreeButtonsHtml()
    {
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'        => 'new_node_button',
            'label'     => Mage::helper('enterprise_cms')->__('Add Node...'),
            'onclick'   => 'hierarchyNodes.newNodePage()',
            'class'     => 'add'
        ))->toHtml();
    }

    /**
     * Retrieve current nodes Json basing on data loaded from
     * DB or from model in case we had error in save process.
     *
     * @return string
     */
    public function getNodesJson()
    {
        /** @var $nodeModel Enterprise_Cms_Model_Hierarchy_Node */
        $nodeModel = Mage::registry('current_hierarchy_node');
        $this->setData('current_scope', $nodeModel->getScope());
        $this->setData('current_scope_id', $nodeModel->getScopeId());

        $this->setData('use_default_scope', $nodeModel->getIsInherited());
        $nodeHeritageModel = $nodeModel->getHeritage();
        $nodes = $nodeHeritageModel->getNodesData();
        unset($nodeModel);
        unset($nodeHeritageModel);

        foreach ($nodes as &$node) {
            $node['assigned_to_store'] = !$this->getData('use_default_scope');
        }

        // fill in custom meta_chapter_section field
        $c = count($nodes);
        for ($i = 0; $i < $c; $i++) {
            if (isset($nodes[$i]['meta_chapter']) && isset($nodes[$i]['meta_section'])
                && $nodes[$i]['meta_chapter'] && $nodes[$i]['meta_section'])
            {
                $nodes[$i]['meta_chapter_section'] = 'both';
            } elseif (isset($nodes[$i]['meta_chapter']) && $nodes[$i]['meta_chapter']) {
                $nodes[$i]['meta_chapter_section'] = 'chapter';
            } elseif (isset($nodes[$i]['meta_section']) && $nodes[$i]['meta_section']) {
                $nodes[$i]['meta_chapter_section'] = 'section';
            } else {
                $nodes[$i]['meta_chapter_section'] = '';
            }
        }

        return Mage::helper('core')->jsonEncode($nodes);
    }

    /**
     * Check if passed node available for store in case this node representation of page.
     * If node does not represent page then method will return true.
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $node
     * @param null|int $store
     * @return bool
     */
    public function isNodeAvailableForStore($node, $store)
    {
        if (!$node->getPageId()) {
            return true;
        }

        if (!$store) {
            return true;
        }

        if ($node->getPageInStores() == '0') {
            return true;
        }

        $stores = explode(',', $node->getPageInStores());
        if (in_array($store, $stores)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve Grid JavaScript object name
     *
     * @return string
     */
    public function getGridJsObject()
    {
        return $this->getParentBlock()->getChild('cms_page_grid')->getJsObjectName();
    }

    /**
     * Prepare translated label 'Save' for button used in Js.
     *
     * @return string
     */
    public function getButtonSaveLabel()
    {
        return Mage::helper('enterprise_cms')->__('Add To Tree');
    }

    /**
     * Prepare translated label 'Update' for button used in Js
     *
     * @return string
     */
    public function getButtonUpdateLabel()
    {
        return Mage::helper('enterprise_cms')->__('Update');
    }

    /**
     * Return legend for Hierarchy node fieldset
     *
     * @return string
     */
    public function getNodeFieldsetLegend()
    {
        return Mage::helper('enterprise_cms')->__('Node Properties');
    }

    /**
     * Return legend for Hierarchy page fieldset
     *
     * @return string
     */
    public function getPageFieldsetLegend()
    {
        return Mage::helper('enterprise_cms')->__('Page Properties');
    }

    /**
     * Getter for protected _currentStore
     *
     * @return null|int
     */
    public function getCurrentStore()
    {
        return $this->_currentStore;
    }

    /**
     * Get current store view if available, or get any in current scope
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        $store = null;
        if ($this->_currentStore) {
            $store = Mage::app()->getStore($this->_currentStore);
        } elseif ($this->getCurrentScope() == Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_WEBSITE) {
            $store = Mage::app()->getWebsite($this->getCurrentScopeId())->getDefaultStore();
        }

        if (!$store) {
            $store = Mage::app()->getAnyStoreView();
        }

        return $store;
    }

    /**
     * Return URL query param for current store
     *
     * @return string
     */
    public function getCurrentStoreUrlParam()
    {
        return '?___store=' . $this->_getStore()->getCode();
    }

    /**
     * Return Base URL for current Store
     *
     * @return string
     */
    public function getStoreBaseUrl()
    {
        return $this->_getStore()->getBaseUrl();
    }

    /**
     * Retrieve html of store switcher added from layout
     *
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getLayout()->getBlock('scope_switcher')->toHtml();
    }

    /**
     * Return List styles separately for unordered/ordererd list as json
     *
     * @return string
     */
    public function getListModesJson()
    {
        $listModes = Mage::getSingleton('enterprise_cms/source_hierarchy_menu_listmode')->toOptionArray();
        $result = array();
        foreach ($listModes as $type => $label) {
            if ($type == '') {
                continue;
            }
            $listType = in_array($type, array('circle', 'disc', 'square')) ? '0' : '1';
            $result[$listType][$type] = $label;
        }

        return Mage::helper('core')->jsonEncode($result);
    }

    /**
     * Check whether current user can drag nodes
     *
     * @deprecated since 1.12.0.0
     * @return bool
     */
    public function canDragNodes()
    {
        return !$this->isLockedByOther();
    }

    /**
     * Check whether page is locked by other user
     *
     * @deprecated since 1.12.0.0
     * @return bool
     */
    public function isLockedByOther()
    {
        if (!$this->hasData('locked_by_other')) {
            $this->setData('locked_by_other', $this->_getLockModel()->isLockedByOther());
        }
        return $this->_getData('locked_by_other');
    }

    /**
     * Check whether page is locked by editor
     *
     * @deprecated since 1.12.0.0
     * @return bool
     */
    public function isLockedByMe()
    {
        if (!$this->hasData('locked_by_me')) {
            $this->setData('locked_by_me', $this->_getLockModel()->isLockedByMe());
        }
        return $this->_getData('locked_by_me');
    }

    /**
     * Retrieve lock lifetime
     *
     * @deprecated since 1.12.0.0
     * @return int
     */
    public function getLockLifetime()
    {
        return $this->_getLockModel()->getLockLifeTime();
    }

    /**
     * Retrieve lock message for js alert
     *
     * @deprecated since 1.12.0.0
     * @return string
     */
    public function getLockAlertMessage()
    {
        return Mage::helper('enterprise_cms')->__('Page lock expires in 60 seconds. Save changes to avoid possible data loss.');
    }

    /**
     * Retrieve Url to Hierarchy delete action
     *
     * @return string
     */
    public function getDeleteHierarchyUrl()
    {
        $params = array(
            'website'=> $this->getRequest()->getParam('website'),
            'store'  => $this->getRequest()->getParam('store'),
            'scopes' => $this->getData('current_scope') . '_' . $this->getData('current_scope_id'),
        );
        return $this->getUrl('*/*/delete', $params);
    }

    /**
     * Retrieve lock model
     *
     * @deprecated since 1.12.0.0
     * @return Enterprise_Cms_Model_Hierarchy_Lock
     */
    protected function _getLockModel()
    {
        return Mage::getSingleton('enterprise_cms/hierarchy_lock');
    }
}
