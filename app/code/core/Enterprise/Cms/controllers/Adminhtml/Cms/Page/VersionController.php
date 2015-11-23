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
 * Manage version controller
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

require_once('Enterprise/Cms/controllers/Adminhtml/Cms/PageController.php');

class Enterprise_Cms_Adminhtml_Cms_Page_VersionController extends Enterprise_Cms_Adminhtml_Cms_PageController
{
    /**
     * Init actions
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_VersionController
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
     * Prepare and place version's model into registry
     * with loaded data if id parameter present
     *
     * @param int $versionId
     * @return Enterprise_Cms_Model_Page_Version
     */
    protected function _initVersion($versionId = null)
    {
        if (is_null($versionId)) {
            $versionId = (int) $this->getRequest()->getParam('version_id');
        }

        $version = Mage::getModel('enterprise_cms/page_version');
        /* @var $version Enterprise_Cms_Model_Page_Version */

        if ($versionId) {
            $userId = Mage::getSingleton('admin/session')->getUser()->getId();
            $accessLevel = Mage::getSingleton('enterprise_cms/config')->getAllowedAccessLevel();
            $version->loadWithRestrictions($accessLevel, $userId, $versionId);
        }

        Mage::register('cms_page_version', $version);
        return $version;
    }

    /**
     * Edit version of CMS page
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_VersionController
     */
    public function editAction()
    {
        $version = $this->_initVersion();

        if (!$version->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('enterprise_cms')->__('Could not load specified version.'));

            $this->_redirect('*/cms_page/edit',
                array('page_id' => $this->getRequest()->getParam('page_id')));
            return;
        }

        $page = $this->_initPage();

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $_data = $version->getData();
            $_data = array_merge($_data, $data);
            $version->setData($_data);
        }

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('enterprise_cms')->__('Edit Version'),
                Mage::helper('enterprise_cms')->__('Edit Version'));

        $this->renderLayout();

        return $this;
    }

    /**
     * Save Action
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_VersionController
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            // init model and set data
            $version = $this->_initVersion();

            // if current user not publisher he can't change owner
            if (!Mage::getSingleton('enterprise_cms/config')->canCurrentUserPublishRevision()) {
                unset($data['user_id']);
            }
            $version->addData($data);

            // try to save it
            try {
                // save the data
                $version->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_cms')->__('The version has been saved.')
                );
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/' . $this->getRequest()->getParam('back'),
                        array(
                            'page_id' => $version->getPageId(),
                            'version_id' => $version->getId()
                        ));
                    return;
                }
                // go to grid
                $this->_redirect('*/cms_page/edit', array('page_id' => $version->getPageId()));
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
                        'version_id' => $this->getRequest()->getParam('version_id'),
                        ));
                return;
            }
        }
        return $this;
    }

    /**
     * Action for ajax grid with revisions
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_VersionController
     */
    public function revisionsAction()
    {
        $this->_initVersion();
        $this->_initPage();

        $this->loadLayout();
        $this->renderLayout();

        return $this;
    }

    /**
     * Mass deletion for revisions
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_VersionController
     */
    public function massDeleteRevisionsAction()
    {
        $ids = $this->getRequest()->getParam('revision');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select revision(s).'));
        }
        else {
            try {
                $userId = Mage::getSingleton('admin/session')->getUser()->getId();
                $accessLevel = Mage::getSingleton('enterprise_cms/config')->getAllowedAccessLevel();

                foreach ($ids as $id) {
                    $revision = Mage::getSingleton('enterprise_cms/page_revision')
                        ->loadWithRestrictions($accessLevel, $userId, $id);

                    if ($revision->getId()) {
                        $revision->delete();
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    Mage::helper('enterprise_cms')->__('An error occurred while deleting revisions.')
                );
            }
        }
        $this->_redirect('*/*/edit', array('_current' => true, 'tab' => 'revisions'));

        return $this;
    }

    /**
     * Delete action
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_VersionController
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('version_id')) {
             // init model
            $version = $this->_initVersion();
            $error = false;
            try {
                $version->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_cms')->__('The version has been deleted.')
                );
                $this->_redirect('*/cms_page/edit', array('page_id' => $version->getPageId()));
                return;
            } catch (Mage_Core_Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $error = true;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_cms')->__('An error occurred while deleting the version.')
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
            Mage::helper('enterprise_cms')->__('Unable to find a version to delete.')
        );
        // go to grid
        $this->_redirect('*/cms_page/edit', array('_current' => true));
        return $this;
    }

    /**
     * New Version
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_VersionController
     */
    public function newAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            // init model and set data
            $version = $this->_initVersion();

            $version->addData($data)
                ->unsetData($version->getIdFieldName());

            // only if user not specified we set current user as owner
            if (!$version->getUserId()) {
                $version->setUserId(Mage::getSingleton('admin/session')->getUser()->getId());
            }

            if (isset($data['revision_id'])) {
                $data = $this->_filterPostData($data);
                $version->setInitialRevisionData($data);
            }

            // try to save it
            try {
                $version->save();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_cms')->__('The new version has been created.')
                );
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if (isset($data['revision_id'])) {
                    $this->_redirect('*/cms_page_revision/edit', array(
                        'page_id' => $version->getPageId(),
                        'revision_id' => $version->getLastRevision()->getId()
                    ));
                } else {
                    $this->_redirect('*/cms_page_version/edit', array(
                        'page_id' => $version->getPageId(),
                        'version_id' => $version->getId()
                    ));
                }
                return;
            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($this->_getRefererUrl()) {
                    // save data in session
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                }
                // redirect to edit form
                $this->_redirectReferer($this->getUrl('*/cms_page/edit',
                    array('page_id' => $this->getRequest()->getParam('page_id'))));
                return;
            }
        }
        return $this;
    }

    /**
     * Check the permission to run it
     * May be in future there will be separate permissions for operations with version
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'new':
            case 'save':
                return Mage::getSingleton('enterprise_cms/config')->canCurrentUserSaveVersion();
                break;
            case 'delete':
                return Mage::getSingleton('enterprise_cms/config')->canCurrentUserDeleteVersion();
                break;
            case 'massDeleteRevisions':
                return Mage::getSingleton('enterprise_cms/config')->canCurrentUserDeleteRevision();
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('cms/page');
                break;
        }
    }
}
