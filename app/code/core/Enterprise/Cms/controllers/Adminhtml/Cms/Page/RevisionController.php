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
 * Manage revision controller
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

require_once('Enterprise/Cms/controllers/Adminhtml/Cms/PageController.php');

class Enterprise_Cms_Adminhtml_Cms_Page_RevisionController extends Enterprise_Cms_Adminhtml_Cms_PageController
{
    /**
     * Init actions
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_RevisionController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('cms/page')
            ->_addBreadcrumb(Mage::helper('cms')->__('CMS'), Mage::helper('cms')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('cms')->__('Manage Pages'), Mage::helper('cms')->__('Manage Pages'))
        ;
        return $this;
    }

    /**
     * Prepare and place revision model into registry
     * with loaded data if id parameter present
     *
     * @param int $revisionId
     * @return Enterprise_Cms_Model_Page_Revision
     */
    protected function _initRevision($revisionId = null)
    {
        if (is_null($revisionId)) {
            $revisionId = (int) $this->getRequest()->getParam('revision_id');
        }

        $revision = Mage::getModel('enterprise_cms/page_revision');
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $accessLevel = Mage::getSingleton('enterprise_cms/config')->getAllowedAccessLevel();

        if ($revisionId) {
            $revision->loadWithRestrictions($accessLevel, $userId, $revisionId);
        } else {
            // loading empty revision
            $versionId = (int) $this->getRequest()->getParam('version_id');
            $pageId = (int) $this->getRequest()->getParam('page_id');

            // loading empty revision but with general data from page and version
            $revision->loadByVersionPageWithRestrictions($versionId, $pageId, $accessLevel, $userId);
            $revision->setUserId($userId);
        }

        //setting in registry as cms_page to make work CE blocks
        Mage::register('cms_page', $revision);
        return $revision;
    }

    /**
     * Edit revision of CMS page
     */
    public function editAction()
    {
        $revisionId = $this->getRequest()->getParam('revision_id');
        $revision = $this->_initRevision($revisionId);

        if ($revisionId && !$revision->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('enterprise_cms')->__('Could not load specified revision.'));

            $this->_redirect('*/cms_page/edit',
                array('page_id' => $this->getRequest()->getParam('page_id')));
            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $_data = $revision->getData();
            $_data = array_merge($_data, $data);
            $revision->setData($_data);
        }

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('enterprise_cms')->__('Edit Revision'),
                Mage::helper('enterprise_cms')->__('Edit Revision'));

        $this->renderLayout();
    }

    /**
     * Save action
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_RevisionController
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            $data = $this->_filterPostData($data);
            // init model and set data
            $revision = $this->_initRevision();
            $revision->setData($data)
                ->setUserId(Mage::getSingleton('admin/session')->getUser()->getId());

            if (!$this->_validatePostData($data)) {
                $this->_redirect('*/*/' . $this->getRequest()->getParam('back'),
                    array(
                        'page_id' => $revision->getPageId(),
                        'revision_id' => $revision->getId()
                    ));
                return;
            }

            // try to save it
            try {
                // save the data
                $revision->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_cms')->__('The revision has been saved.')
                );
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/' . $this->getRequest()->getParam('back'),
                        array(
                            'page_id' => $revision->getPageId(),
                            'revision_id' => $revision->getId()
                        ));
                    return;
                }
                // go to grid
                $this->_redirect('*/cms_page_version/edit', array(
                        'page_id' => $revision->getPageId(),
                        'version_id' => $revision->getVersionId()
                    ));
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit',
                    array(
                        'page_id' => $this->getRequest()->getParam('page_id'),
                        'revision_id' => $this->getRequest()->getParam('revision_id'),
                        ));
                return;
            }
        }
        return $this;
    }

    /**
     * Publishing revision
     */
    public function publishAction()
    {
        $revision = $this->_initRevision();

        try {
            $revision->publish();
            // display success message
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('enterprise_cms')->__('The revision has been published.')
            );
            $this->_redirect('*/cms_page/edit', array('page_id' => $revision->getPageId()));
            return;
        } catch (Exception $e) {
            // display error message
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            // redirect to edit form
            $this->_redirect('*/*/edit', array(
                    'page_id' => $this->getRequest()->getParam('page_id'),
                    'revision_id' => $this->getRequest()->getParam('revision_id')
                    ));
            return;
        }
    }

    /**
     * Prepares page with iframe
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_RevisionController
     */
    public function previewAction()
    {
        // check if data sent
        $data = $this->getRequest()->getPost();
        if ((empty($data) || !isset($data['page_id'])) && !$this->getRequest()->getParam('page_id')) {
            $this->_forward('noRoute');
            return $this;
        }

        $page = $this->_initPage();
        $this->loadLayout();

        if (empty($data)) {
            $data = $page->getData();
        }

        $stores = $page->getStoreId();
        if (isset($data['stores'])) {
            $stores = $data['stores'];
        }

        /*
         * Checking if all stores passed then we should not assign array to block
         */
        $allStores = false;
        if (is_array($stores) && count($stores) == 1 && !array_shift($stores)) {
            $allStores = true;
        }

        if (!$allStores) {
            $this->getLayout()->getBlock('store_switcher')->setStoreIds($stores);
        }

        // Setting default values for selected store and revision
        $data['preview_selected_store'] = 0;
        $data['preview_selected_revision'] = 0;

        $this->getLayout()->getBlock('preview_form')->setFormData($data);

        // Remove revision switcher if page is out of version control
        if (!$page->getUnderVersionControl()) {
            $this->getLayout()->getBlock('tools')->unsetChild('revision_switcher');
        }

        $this->renderLayout();
    }

    /**
     * Generates preview of page
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_RevisionController
     */
    public function dropAction()
    {
        // check if data sent
        $data = $this->getRequest()->getPost();
        if (!empty($data) && isset($data['page_id'])) {
            // init model and set data
            $page = Mage::getSingleton('cms/page')
                ->load($data['page_id']);
            if (!$page->getId()) {
                $this->_forward('noRoute');
                return $this;
            }

            /*
             * If revision was selected load it and get data for preview from it
             */
            $_tempData = null;
            if (isset($data['preview_selected_revision']) && $data['preview_selected_revision']) {
                $revision = $this->_initRevision($data['preview_selected_revision']);
                if ($revision->getId()) {
                    $_tempData = $revision->getData();
                }
            }

            /*
             * If there was no selected revision then use posted data
             */
            if (is_null($_tempData)) {
                $_tempData = $data;
            }

            /*
             * Posting posted data in page model
             */
            $page->addData($_tempData);

            /*
             * Retrieve store id from page model or if it was passed from post
             */
            $selectedStoreId = $page->getStoreId();
            if (is_array($selectedStoreId)) {
                $selectedStoreId = array_shift($selectedStoreId);
            }

            if (isset($data['preview_selected_store']) && $data['preview_selected_store']) {
                $selectedStoreId = $data['preview_selected_store'];
            } else {
                if (!$selectedStoreId) {
                    $selectedStoreId = Mage::app()->getAnyStoreView()->getId();
                }
            }
            $selectedStoreId = (int) $selectedStoreId;

            /*
             * Emulating front environment
             */
            Mage::app()->getLocale()->emulate($selectedStoreId);
            Mage::app()->setCurrentStore(Mage::app()->getStore($selectedStoreId));

            Mage::getDesign()
                ->setArea('frontend')
                ->setStore($selectedStoreId)
                ->setPackageName();

            $designChange = Mage::getSingleton('core/design')
                ->loadChange($selectedStoreId);

            if ($designChange->getData()) {
                Mage::getDesign()->setPackageName($designChange->getPackage())
                    ->setTheme($designChange->getTheme());
            }

            Mage::helper('cms/page')->renderPageExtended($this);
            Mage::app()->getLocale()->revert();

        } else {
            $this->_forward('noRoute');
        }

        return $this;
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('revision_id')) {
            $error = false;
            try {
                // init model and delete
                $revision = $this->_initRevision();
                $revision->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_cms')->__('The revision has been deleted.')
                );
                $this->_redirect('*/cms_page_version/edit', array(
                        'page_id' => $revision->getPageId(),
                        'version_id' => $revision->getVersionId()
                    ));
                return;
            } catch (Mage_Core_Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $error = true;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_cms')->__('An error occurred while deleting the revision.')
                );
                $error = true;
            }

            // go back to edit form
            if ($error) {
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('enterprise_cms')->__('Unable to find a revision to delete.')
        );
        // go to grid
        $this->_redirect('*/cms_page/edit', array('_current' => true));
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'save':
                return Mage::getSingleton('enterprise_cms/config')->canCurrentUserSaveRevision();
                break;
            case 'publish':
                return Mage::getSingleton('enterprise_cms/config')->canCurrentUserPublishRevision();
                break;
            case 'delete':
                return Mage::getSingleton('enterprise_cms/config')->canCurrentUserDeleteRevision();
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('cms/page');
                break;
        }
    }

    /**
     * Controller predispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        if ($this->getRequest()->getActionName() == 'drop') {
            $this->_currentArea = 'frontend';
        }
        parent::preDispatch();
    }

    /**
     * New Revision action
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_RevisionController
     */
    public function newAction()
    {
        $this->_forward('edit');
    }
}
