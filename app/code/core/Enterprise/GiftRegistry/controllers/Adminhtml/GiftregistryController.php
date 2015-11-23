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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GiftRegistry_Adminhtml_GiftregistryController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init active menu and set breadcrumb
     *
     * @return Enterprise_GiftRegistry_Adminhtml_GiftregistryController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/giftregistry')
            ->_addBreadcrumb(
                Mage::helper('enterprise_giftregistry')->__('Gift Registry'),
                Mage::helper('enterprise_giftregistry')->__('Gift Registry')
            );

        $this->_title($this->__('Customers'))->_title($this->__('Manage Gift Registry Types'));
        return $this;
    }

    /**
     * Initialize model
     *
     * @param string $requestParam
     * @return Enterprise_GiftRegistry_Model_Type
     */
    protected function _initType($requestParam = 'id')
    {
        $type = Mage::getModel('enterprise_giftregistry/type');
        $type->setStoreId($this->getRequest()->getParam('store', 0));

        if ($typeId = $this->getRequest()->getParam($requestParam)) {
            $type->load($typeId);
            if (!$type->getId()) {
                Mage::throwException($this->__('Wrong gift registry type requested.'));
            }
        }
        Mage::register('current_giftregistry_type', $type);
        return $type;
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * Create new gift registry type
     */
    public function newAction()
    {
        try {
            $model = $this->_initType();
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        $this->_initAction();
        $this->_title($this->__('New Gift Registry Type'));

        $block = $this->getLayout()->createBlock('enterprise_giftregistry/adminhtml_giftregistry_edit')
            ->setData('form_action_url', $this->getUrl('*/*/save'));

        $this->_addBreadcrumb($this->__('New Type'), $this->__('New Type'))
            ->_addContent($block)
            ->_addLeft($this->getLayout()->createBlock('enterprise_giftregistry/adminhtml_giftregistry_edit_tabs'))
            ->renderLayout();
    }

    /**
     * Edit gift registry type
     */
    public function editAction()
    {
        try {
            $model = $this->_initType();
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        $this->_initAction();
        $this->_title($this->__("Edit '%s' Gift Registry Type", $model->getLabel()));

        $block = $this->getLayout()->createBlock('enterprise_giftregistry/adminhtml_giftregistry_edit')
            ->setData('form_action_url', $this->getUrl('*/*/save'));

        $this->_addBreadcrumb($this->__('Edit Type'), $this->__('Edit Type'))
            ->_addContent($block)
            ->_addLeft($this->getLayout()->createBlock('enterprise_giftregistry/adminhtml_giftregistry_edit_tabs'))
            ->renderLayout();
    }

    /**
     * Filter post data
     *
     * @param array $data
     * @return array
     */
    protected function _filterPostData($data)
    {
        $helper = $this->_getHelper();
        if (!empty($data['type']['label'])) {
            $data['type']['label'] = $helper->stripTags($data['type']['label']);
        }
        if (!empty($data['attributes']['registry'])) {
            foreach ($data['attributes']['registry'] as &$regItem) {
                if (!empty($regItem['label'])) {
                    $regItem['label'] = $helper->stripTags($regItem['label']);
                }
                if (!empty($regItem['options'])) {
                    foreach ($regItem['options'] as &$option) {
                        $option['label'] = $helper->stripTags($option['label']);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Save gift registry type
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            //filtering
            $data = $this->_filterPostData($data);
            try {
                $model = $this->_initType();
                $model->loadPost($data);
                $model->save();
                Mage::getSingleton('adminhtml/session')
                        ->addSuccess($this->__('The gift registry type has been saved.'));

                if ($redirectBack = $this->getRequest()->getParam('back', false)) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'store' => $model->getStoreId()));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to save gift registry type.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete gift registry type
     */
    public function deleteAction()
    {
        try {
            $model = $this->_initType();
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The gift registry type has been deleted.'));
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $model->getId()));
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Failed to delete gift registry type.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Check the permission
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/enterprise_giftregistry');
    }
}
