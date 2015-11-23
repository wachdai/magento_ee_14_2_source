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
 * Enterprise cms page observer
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Model_Observer
{
    /**
     * Configuration model
     * @var Enterprise_Cms_Model_Config
     */
    protected $_config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_config = Mage::getSingleton('enterprise_cms/config');
    }

    /**
     * Making changes to main tab regarding to custom logic
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function onMainTabPrepareForm($observer)
    {
        $form = $observer->getEvent()->getForm();
        /* @var $baseFieldset Varien_Data_Form_Element_Fieldset */
        $baseFieldset = $form->getElement('base_fieldset');
        /* @var $baseFieldset Varien_Data_Form_Element_Fieldset */

        $isActiveElement = $form->getElement('is_active');
        if ($isActiveElement) {
            // Making is_active as disabled if user does not have publish permission
            if (!$this->_config->canCurrentUserPublishRevision()) {
                    $isActiveElement->setDisabled(true);
            }
        }

        /*
         * Adding link to current published revision
         */
        /* @var $page Enterprise_Cms_Model_Page */
        $page = Mage::registry('cms_page');
        $revisionAvailable = false;
        if ($page) {

            $baseFieldset->addField('under_version_control', 'select', array(
                'label'     => Mage::helper('enterprise_cms')->__('Under Version Control'),
                'title'     => Mage::helper('enterprise_cms')->__('Under Version Control'),
                'name'      => 'under_version_control',
                'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
            ));

            if ($page->getPublishedRevisionId() && $page->getUnderVersionControl()) {
                $userId = Mage::getSingleton('admin/session')->getUser()->getId();
                $accessLevel = Mage::getSingleton('enterprise_cms/config')->getAllowedAccessLevel();

                $revision = Mage::getModel('enterprise_cms/page_revision')
                    ->loadWithRestrictions($accessLevel, $userId, $page->getPublishedRevisionId());

                if ($revision->getId()) {
                    $revisionNumber = $revision->getRevisionNumber();
                    $versionNumber = $revision->getVersionNumber();
                    $versionLabel = $revision->getLabel();

                    $page->setPublishedRevisionLink(
                        Mage::helper('enterprise_cms')->__('%s; rev #%s', $versionLabel, $revisionNumber));

                    $baseFieldset->addField('published_revision_link', 'link', array(
                            'label' => Mage::helper('enterprise_cms')->__('Currently Published Revision'),
                            'href' => Mage::getModel('adminhtml/url')->getUrl('*/cms_page_revision/edit', array(
                                'page_id' => $page->getId(),
                                'revision_id' => $page->getPublishedRevisionId()
                                )),
                        ));

                    $revisionAvailable = true;
                }
            }
        }

        if ($revisionAvailable && !Mage::getSingleton('admin/session')->isAllowed('cms/page/save_revision')) {
            foreach ($baseFieldset->getElements() as $element) {
                $element->setDisabled(true);
            }
        }

        /*
         * User does not have access to revision or revision is no longer available
         */
        if (!$revisionAvailable && $page->getId() && $page->getUnderVersionControl()) {
            $baseFieldset->addField('published_revision_status', 'label', array('bold' => true));
            $page->setPublishedRevisionStatus(Mage::helper('enterprise_cms')->__('Published Revision Unavailable'));
        }

        return $this;
    }

    /**
     * Validate and render Cms hierarchy page
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function cmsControllerRouterMatchBefore(Varien_Event_Observer $observer)
    {
        /* @var $helper Enterprise_Cms_Helper_Hierarchy */
        $helper = Mage::helper('enterprise_cms/hierarchy');
        if (!$helper->isEnabled()) {
            return $this;
        }

        $condition = $observer->getEvent()->getCondition();

        /**
         * Validate Request and modify router match condition
         */
        /* @var $node Enterprise_Cms_Model_Hierarchy_Node */
        $node = Mage::getModel('enterprise_cms/hierarchy_node', array(
            'scope' => Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_STORE,
            'scope_id' => Mage::app()->getStore()->getId(),
        ))->getHeritage();
        $requestUrl = $condition->getIdentifier();
        $node->loadByRequestUrl($requestUrl);

        if ($node->checkIdentifier($requestUrl, Mage::app()->getStore())) {
            $condition->setContinue(false);
            if (!$node->getId()) {
                $collection = $node->getNodesCollection();
                foreach ($collection as $item) {
                    if ($item->getPageIdentifier() == $requestUrl) {
                        $url = Mage::getUrl('', array('_direct' => $item->getRequestUrl()));
                        $condition->setRedirectUrl($url);
                        break;
                    }
                }

            }
        }
        if (!$node->getId()) {
            return $this;
        }

        if (!$node->getPageId()) {
            /* @var $child Enterprise_Cms_Model_Hierarchy_Node */
            $child = Mage::getModel('enterprise_cms/hierarchy_node', array(
                'scope' => $node->getScope(),
                'scope_id' => $node->getScopeId(),
            ));
            $child->loadFirstChildByParent($node->getId());
            if (!$child->getId()) {
                return $this;
            }
            $url   = Mage::getUrl('', array('_direct' => $child->getRequestUrl()));
            $condition->setRedirectUrl($url);
        } else {
            if (!$node->getPageIsActive()) {
                return $this;
            }

            // register hierarchy and node
            Mage::register('current_cms_hierarchy_node', $node);

            $condition->setContinue(true);
            $condition->setIdentifier($node->getPageIdentifier());
        }

        return $this;
    }

    /**
     * Processing extra data after cms page saved
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function cmsPageSaveAfter(Varien_Event_Observer $observer)
    {
        /* @var $page Mage_Cms_Model_Page */
        $page = $observer->getEvent()->getObject();

        // Create new initial version & revision if it
        // is a new page or version control was turned on for this page.
        if ($page->getIsNewPage() || ($page->getUnderVersionControl()
            && $page->dataHasChangedFor('under_version_control'))
        ) {
            $version = Mage::getModel('enterprise_cms/page_version');

            $revisionInitialData = $page->getData();
            $revisionInitialData['copied_from_original'] = true;

            $version->setLabel($page->getTitle())
                ->setAccessLevel(Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC)
                ->setPageId($page->getId())
                ->setUserId(Mage::getSingleton('admin/session')->getUser()->getId())
                ->setInitialRevisionData($revisionInitialData)
                ->save();

            if ($page->getUnderVersionControl()) {
                $revision = $version->getLastRevision();

                if ($revision instanceof Enterprise_Cms_Model_Page_Revision) {
                    $revision->publish();
                }
            }
        }

        if (!Mage::helper('enterprise_cms/hierarchy')->isEnabled()) {
            return $this;
        }

        // rebuild URL rewrites if page has changed for identifier
        if ($page->dataHasChangedFor('identifier')) {
            Mage::getSingleton('enterprise_cms/hierarchy_node')->updateRewriteUrls($page);
        }

        /*
         * Appending page to selected nodes it will remove pages from other nodes
         * which are not specified in array. So should be called even array is empty!
         * Returns array of new ids for page nodes array( oldId => newId ).
         */
        Mage::getSingleton('enterprise_cms/hierarchy_node')->appendPageToNodes($page, $page->getAppendToNodes());

        /*
         * Updating sort order for nodes in parent nodes which have current page as child
         */
        foreach ($page->getNodesSortOrder() as $nodeId => $value) {
            Mage::getResourceSingleton('enterprise_cms/hierarchy_node')->updateSortOrder($nodeId, $value);
        }

        return $this;
    }

    /**
     * Preparing cms page object before it will be saved
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function cmsPageSaveBefore(Varien_Event_Observer $observer)
    {
        /* @var $page Mage_Cms_Model_Page */
        $page = $observer->getEvent()->getObject();
        /*
         * All new pages created by user without permission to publish
         * should be disabled from the beginning.
         */
        if (!$page->getId()) {
            $page->setIsNewPage(true);
            if (!$this->_config->canCurrentUserPublishRevision()) {
                $page->setIsActive(false);
            }
            // newly created page should be auto assigned to website root
            $page->setWebsiteRoot(true);
        } else if (!$page->getUnderVersionControl()) {
            $page->setPublishedRevisionId(null);
        }

        /*
         * Checking if node's data was passed and if yes. Saving new sort order for nodes.
         */
        $nodesData = $page->getNodesData();
        $appendToNodes = array();
        $sortOrder = array();
        if ($nodesData) {
            try{
                $nodesData = Mage::helper('core')->jsonDecode($page->getNodesData());
            } catch (Zend_Json_Exception $e) {
                $nodesData=null;
            }
            if (!empty($nodesData)) {
                foreach ($nodesData as $row) {
                    if (isset($row['page_exists']) && $row['page_exists']) {
                        $appendToNodes[$row['node_id']] = 0;
                    }

                    if (isset($appendToNodes[$row['parent_node_id']])) {
                        if (strpos($row['node_id'], '_') !== FALSE) {
                            $appendToNodes[$row['parent_node_id']] = $row['sort_order'];
                        } else {
                            $sortOrder[$row['node_id']] = $row['sort_order'];
                        }
                    }
                }
            }
        }

        $page->setNodesSortOrder($sortOrder);
        $page->setAppendToNodes($appendToNodes);
        return $this;
    }

    /**
     * Clean up private versions after user deleted.
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function adminUserDeleteAfter(Varien_Event_Observer $observer)
    {
        $version = Mage::getModel('enterprise_cms/page_version');
        $collection = $version->getCollection()
            ->addAccessLevelFilter(Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PRIVATE)
            ->addUserIdFilter();

         Mage::getSingleton('core/resource_iterator')
            ->walk($collection->getSelect(), array(array($this, 'removeVersionCallback')), array('version'=> $version));

         return $this;
    }

    /**
     * Clean up hierarchy tree that belongs to website.
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function deleteWebsite(Varien_Event_Observer $observer)
    {
        /* @var $store Mage_Core_Model_Website */
        $website = $observer->getEvent()->getWebsite();
        $nodeModel = Mage::getModel('enterprise_cms/hierarchy_node');
        $nodeModel->deleteByScope(Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_WEBSITE, $website->getId());

        foreach ($website->getStoreIds() as $storeId) {
            $this->_cleanStoreFootprints($storeId);
        }

        return $this;
    }

    /**
     * Clean up hierarchy tree that belongs to store.
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function deleteStore(Varien_Event_Observer $observer)
    {
        $storeId = $observer->getEvent()->getStore()->getId();
        $this->_cleanStoreFootprints($storeId);
        return $this;
    }

    /**
     * Clean up information about deleted store from the widgets and hierarchy nodes
     *
     * @param int $storeId
     */
    private function _cleanStoreFootprints($storeId)
    {
        $storeScope = Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_STORE;
        $nodeModel = Mage::getModel('enterprise_cms/hierarchy_node');
        $nodeModel->deleteByScope($storeScope, $storeId);

        /* @var $widgetModel Mage_Widget_Model_Widget_Instance */
        $widgetModel = Mage::getModel('widget/widget_instance');
        $widgets = $widgetModel->getResourceCollection()
                ->addStoreFilter(array($storeId, false))
                ->addFieldToFilter('instance_type','enterprise_cms/widget_node');

        /* @var $widgetInstance Mage_Widget_Model_Widget_Instance */
        foreach ($widgets as $widgetInstance) {
            $storeIds = $widgetInstance->getStoreIds();
            foreach ($storeIds as $key => $value) {
                if ($value == $storeId) {
                    unset($storeIds[$key]);
                }
            }
            $widgetInstance->setStoreIds($storeIds);

            $widgetParams = $widgetInstance->getWidgetParameters();
            unset($widgetParams['anchor_text_' . $storeId]);
            unset($widgetParams['title_' . $storeId]);
            unset($widgetParams['node_id_' . $storeId]);
            $widgetInstance->setWidgetParameters($widgetParams);

            $widgetInstance->save();
        }
    }

    /**
     * Callback function to remove version or change access
     * level to protected if we can't remove it.
     *
     * @param array $args
     */
    public function removeVersionCallback($args)
    {
        $version = $args['version'];
        $version->setData($args['row']);

        try {
            $version->delete();
        } catch (Mage_Core_Exception $e) {
            // If we have situation when revision from
            // orphaned private version published we should
            // change its access level to protected so publisher
            // will have chance to see it and assign to some user
            $version->setAccessLevel(Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PROTECTED);
            $version->save();
        }
    }

    /**
     * Modify status's label from 'Enabled' to 'Published'.
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function modifyPageStatuses(Varien_Event_Observer $observer)
    {
        $statuses = $observer->getEvent()->getStatuses();
        $statuses->setData(Mage_Cms_Model_Page::STATUS_ENABLED, Mage::helper('enterprise_cms')->__('Published'));

        return $this;
    }

    /**
     * Removing unneeded data from increment table for removed page.
     *
     * @param $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function cmsPageDeleteAfter(Varien_Event_Observer $observer)
    {
        /* @var $page Mage_Cms_Model_Page */
        $page = $observer->getEvent()->getObject();

        Mage::getResourceSingleton('enterprise_cms/increment')
            ->cleanIncrementRecord(Enterprise_Cms_Model_Increment::TYPE_PAGE,
                $page->getId(),
                Enterprise_Cms_Model_Increment::LEVEL_VERSION);

        return $this;
    }

    /**
     * Handler for cms hierarchy view
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchCmsHierachyView($config, $eventModel)
    {
        return $eventModel->setInfo(Mage::helper('enterprise_cms')->__('Tree Viewed'));
    }

    /**
     * Handler for cms revision preview
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchCmsRevisionPreview($config, $eventModel)
    {
        return $eventModel->setInfo(Mage::app()->getRequest()->getParam('revision_id'));
    }

    /**
     * Handler for cms revision publish
     *
     * @param Varien_Simplexml_Element $config
     * @param Enterprise_Logging_Model_Event $eventModel
     * @return Enterprise_Logging_Model_Event|false
     */
    public function postDispatchCmsRevisionPublish($config, $eventModel)
    {
        return $eventModel->setInfo(Mage::app()->getRequest()->getParam('revision_id'));
    }

    /**
     * Add Hierarchy Menu layout handle to Cms page rendering
     *
     * @param $observer
     * @return Enterprise_Cms_Model_Observer
     */
    public function affectCmsPageRender(Varien_Event_Observer $observer)
    {
        /* @var $helper Enterprise_Cms_Helper_Hierarchy */
        $helper = Mage::helper('enterprise_cms/hierarchy');
        if (!is_object(Mage::registry('current_cms_hierarchy_node')) || !$helper->isEnabled()) {
            return $this;
        }

        /* @var $node Enterprise_Cms_Model_Hierarchy_Node */
        $node = Mage::registry('current_cms_hierarchy_node');

        /* @var $action Mage_Core_Controller_Varien_Action */
        $action = $observer->getEvent()->getControllerAction();

        // collect loaded handles for cms page
        $loadedHandles = $action->getLayout()->getUpdate()->getHandles();

        $menuLayout = $node->getMenuLayout();
        if ($menuLayout === null) {
            return $this;
        }

        // check whether menu handle is compatible with page handles
        $allowedHandles = $menuLayout->getPageLayoutHandles();
        if (is_array($allowedHandles) && count($allowedHandles) > 0) {
            $allowedHandles = array_keys($allowedHandles);
            if (count(array_intersect($allowedHandles, $loadedHandles)) == 0) {
                return $this;
            }
        }

        // add menu handle to layout update
        $action->getLayout()->getUpdate()->addHandle($menuLayout->getLayoutHandle());

        return $this;
    }

    /**
     * Adds CMS page preview url
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareAdmihtmlCmsPageRowPreviewUrl(Varien_Event_Observer $observer)
    {
        $row = $observer->getEvent()->getRow();
        $row->setPreviewUrl(Mage::helper('adminhtml')->getUrl('*/cms_page_revision/preview', array('page_id' => $row->getPageId())));
    }

    /**
     * Adds CMS hierarchy menu item to top menu
     *
     * @param Varien_Event_Observer $observer
     */
    public function addCmsToTopmenuItems(Varien_Event_Observer $observer)
    {
        /**
         * @var $topMenuRootNode Varien_Data_Tree_Node
         */
        $topMenuRootNode = $observer->getMenu();

        $hierarchyModel = Mage::getModel('enterprise_cms/hierarchy_node', array(
            'scope' => Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_STORE,
            'scope_id' => Mage::app()->getStore()->getId(),
        ))->getHeritage();

        $nodes = $hierarchyModel->getNodesData();
        $tree = $topMenuRootNode->getTree();

        $nodesFlatList = array(
            $topMenuRootNode->getId() => $topMenuRootNode
        );

        $nodeModel = Mage::getModel('enterprise_cms/hierarchy_node');

        foreach ($nodes as $node) {

            $nodeData = $nodeModel->load($node['node_id']);

            if (!$nodeData || ($nodeData->getParentNodeId() == null && !$nodeData->getTopMenuVisibility())
                || ($nodeData->getParentNodeId() != null && $nodeData->getTopMenuExcluded())
                || ($nodeData->getPageId() && !$nodeData->getPageIsActive())
            ) {
                continue;
            }

            $menuNodeId = 'cms-hierarchy-node-' . $node['node_id'];
            $menuNodeData = array(
                'name' => $nodeData->getLabel(),
                'id' => $menuNodeId,
                'url' => $nodeData->getUrl(),
                'is_active' => $this->_isCmsNodeActive($nodeData)
            );

            $parentNodeId = !isset($node['parent_node_id']) ? $topMenuRootNode->getId()
                : 'cms-hierarchy-node-' . $node['parent_node_id'];
            $parentNode = isset($nodesFlatList[$parentNodeId]) ? $nodesFlatList[$parentNodeId] : null;

            if (!$parentNode) {
                continue;
            }

            $menuNode = new Varien_Data_Tree_Node($menuNodeData, 'id', $tree, $parentNode);
            $parentNode->addChild($menuNode);

            $nodesFlatList[$menuNodeId] = $menuNode;
        }
    }

    /**
     * Checks whether node belongs to currently active node's path
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $cmsNode
     * @return bool
     */
    protected function _isCmsNodeActive($cmsNode)
    {
        $currentNode = Mage::registry('current_cms_hierarchy_node');

        if (!$currentNode) {
            return false;
        }

        $nodePathIds = explode('/', $currentNode->getXpath());

        return in_array($cmsNode->getId(), $nodePathIds);
    }
}
