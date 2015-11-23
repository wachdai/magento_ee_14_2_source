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
 * Admihtml Manage Cms Hierarchy Controller
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Adminhtml_Cms_HierarchyController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Current Scope
     *
     * @var string
     */
    protected $_scope = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_DEFAULT;

    /**
     * Current ScopeId
     *
     * @var int
     */
    protected $_scopeId = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_DEFAULT_ID;

    /**
     * Current Website
     *
     * @var string
     */
    protected $_website = '';

    /**
     * Current Store
     *
     * @var string
     */
    protected $_store = '';

    /**
     * Controller pre dispatch method
     *
     * @return Enterprise_Cms_HierarchyController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('enterprise_cms/hierarchy')->isEnabled()) {
            if ($this->getRequest()->getActionName() != 'noroute') {
                $this->_forward('noroute');
            }
        }
        return $this;
    }

    /**
     * Init scope and scope code by website and store for actions
     *
     * @return null
     */
    protected function _initScope()
    {
        $this->_website = $this->getRequest()->getParam('website');
        $this->_store   = $this->getRequest()->getParam('store');

        if (!is_null($this->_website)) {
            $this->_scope = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_WEBSITE;
            $website = Mage::app()->getWebsite($this->_website);
            $this->_scopeId = $website->getId();
            $this->_website = $website->getCode();
        }

        if (!is_null($this->_store)) {
            $this->_scope = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_STORE;
            $store = Mage::app()->getStore($this->_store);
            $this->_scopeId = $store->getId();
            $this->_store = $store->getCode();
        }
    }

    /**
     * Load layout, set active menu and breadcrumbs
     *
     * @return Enterprise_Cms_HierarchyController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/hierarchy')
            ->_addBreadcrumb(Mage::helper('enterprise_cms')->__('CMS'),
                Mage::helper('enterprise_cms')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('enterprise_cms')->__('CMS Page Trees'),
                Mage::helper('enterprise_cms')->__('CMS Page Trees'));
        return $this;
    }

    /**
     * Retrieve Scope and ScopeId from string with prefix
     *
     * @param string $value
     * @return array
     */
    protected function _getScopeData($value)
    {
        $scopeId = false;
        $scope = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_DEFAULT;
        if (0 === strpos($value, Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_WEBSITE)) {
            $scopeId = (int)str_replace(Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_WEBSITE, '', $value);
            $scope = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_WEBSITE;
        } elseif (0 === strpos($value, Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_STORE)) {
            $scopeId = (int)str_replace(Enterprise_Cms_Helper_Hierarchy::SCOPE_PREFIX_STORE, '', $value);
            $scope = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_STORE;
        }
        if (!$scopeId || $scopeId == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            $scopeId = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_DEFAULT_ID;
            $scope = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_DEFAULT;
        }
        return array($scope, $scopeId);
    }

    /**
     * Show Tree Edit Page
     *
     * @return null
     */
    public function indexAction()
    {
        $this->_title($this->__('CMS'))
             ->_title($this->__('Pages'))
             ->_title($this->__('Manage Hierarchy'));

        $this->_initScope();

        $nodeModel = Mage::getModel('enterprise_cms/hierarchy_node',
                array('scope' => $this->_scope, 'scope_id' => $this->_scopeId));

        // restore data if exists
        $formData = $this->_getSession()->getFormData(true);
        if (!empty($formData)) {
            $nodeModel->addData($formData);
            unset($formData);
        }

        Mage::register('current_hierarchy_node', $nodeModel);

        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Delete hierarchy from one or several scopes
     *
     * @return null
     */
    public function deleteAction()
    {
        $this->_initScope();
        $scopes = $this->getRequest()->getParam('scopes');
        if (empty($scopes) || ($this->getRequest()->isPost() && !is_array($scopes))
            || $this->getRequest()->isGet() && !is_string($scopes)
        ) {
            $this->_getSession()->addError($this->__('Invalid Scope.'));
        } else {
            if (!is_array($scopes)) {
                $scopes = array($scopes);
            }
            try {
                /* @var $nodeModel Enterprise_Cms_Model_Hierarchy_Node */
                $nodeModel = Mage::getModel('enterprise_cms/hierarchy_node');
                foreach (array_unique($scopes) as $value) {
                    list ($scope, $scopeId) = $this->_getScopeData($value);
                    $nodeModel->setScope($scope);
                    $nodeModel->setScopeId($scopeId);
                    $nodeModel->deleteByScope($scope, $scopeId);
                    $nodeModel->collectTree(array(), array());
                }
                $this->_getSession()->addSuccess(
                    $this->__('Pages hierarchy has been deleted from the selected scopes.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('enterprise_cms')->__('There has been an error deleting hierarchy.')
                );
            }
        }

        $this->_redirect('*/*/index', array('website' => $this->_website, 'store' => $this->_store));
        return;
    }

    /**
     * Copy hierarchy from one scope to other scopes
     *
     * @return null
     */
    public function copyAction()
    {
        $this->_initScope();
        $scopes = $this->getRequest()->getParam('scopes');
        if ($this->getRequest()->isPost() && is_array($scopes) && !empty($scopes)) {
            /** @var $nodeModel Enterprise_Cms_Model_Hierarchy_Node */
            $nodeModel = Mage::getModel('enterprise_cms/hierarchy_node', array(
                'scope' =>  $this->_scope,
                'scope_id' => $this->_scopeId,
            ));
            $nodeHeritageModel = $nodeModel->getHeritage();
            try {
                foreach (array_unique($scopes) as $value) {
                    list ($scope, $scopeId) = $this->_getScopeData($value);
                    $nodeHeritageModel->copyTo($scope, $scopeId);
                }
                $this->_getSession()->addSuccess($this->__('Pages hierarchy has been copied to the selected scopes.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('enterprise_cms')->__('There has been an error copying hierarchy.')
                );
            }
        }

        $this->_redirect('*/*/index', array('website' => $this->_website, 'store' => $this->_store));
        return;
    }

    /**
     * Lock page
     * @deprecated since 1.12.0.0
     */
    public function lockAction()
    {
        $this->_redirect('*/*/');
    }

    /**
     * Save changes
     *
     * @return null
     */
    public function saveAction()
    {
        $this->_initScope();
        if ($this->getRequest()->isPost()) {
            /** @var $node Enterprise_Cms_Model_Hierarchy_Node */
            $node       = Mage::getModel('enterprise_cms/hierarchy_node', array(
                'scope' =>  $this->_scope,
                'scope_id' => $this->_scopeId
            ));
            $data       = $this->getRequest()->getPost();
            $hasError   = true;

            try {
                if (isset($data['use_default_scope_property']) && $data['use_default_scope_property']) {
                    $node->deleteByScope($this->_scope, $this->_scopeId);
                } else {
                    if (!empty($data['nodes_data'])) {
                        try{
                            $nodesData = Mage::helper('core')->jsonDecode($data['nodes_data']);
                        }catch (Zend_Json_Exception $e){
                            $nodesData = array();
                        }
                    } else {
                        $nodesData = array();
                    }
                    if (!empty($data['removed_nodes'])) {
                        $removedNodes = explode(',', $data['removed_nodes']);
                    } else {
                        $removedNodes = array();
                    }

                    // fill in meta_chapter and meta_section based on meta_chapter_section
                    foreach ($nodesData as &$n) {
                        $n['meta_chapter'] = 0;
                        $n['meta_section'] = 0;
                        if (!isset($n['meta_chapter_section'])) {
                            continue;
                        }
                        if ($n['meta_chapter_section'] == 'both' || $n['meta_chapter_section'] == 'chapter') {
                            $n['meta_chapter'] = 1;
                        }
                        if ($n['meta_chapter_section'] == 'both' || $n['meta_chapter_section'] == 'section') {
                            $n['meta_section'] = 1;
                        }
                    }

                    $node->collectTree($nodesData, $removedNodes);
                }

                $hasError = false;
                $this->_getSession()->addSuccess(
                    Mage::helper('enterprise_cms')->__('The hierarchy has been saved.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('enterprise_cms')->__('There has been an error saving hierarchy.')
                );
            }

            if ($hasError) {
                //save data in session
                $this->_getSession()->setFormData($data);
            }
        }

        $this->_redirect('*/*/index', array('website' => $this->_website, 'store' => $this->_store));
        return;
    }

    /**
     * Cms Pages Ajax Grid
     *
     * @return null
     */
    public function pageGridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Return lock model instance
     *
     * @deprecated since 1.12.0.0
     * @return Enterprise_Cms_Model_Hierarchy_Lock
     */
    protected function _getLockModel()
    {
        return Mage::getSingleton('enterprise_cms/hierarchy_lock');
    }

    /**
     * Check is allowed access to action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/hierarchy');
    }
}
