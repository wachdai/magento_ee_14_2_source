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
 * @package     Enterprise_GiftCardAccount
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_GiftCardAccount_Adminhtml_GiftcardaccountController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Defines if status message of code pool is show
     *
     * @var bool
     */
    protected $_showCodePoolStatusMessage = true;

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Gift Card Accounts'));

        if ($this->_showCodePoolStatusMessage) {
            $usage = Mage::getModel('enterprise_giftcardaccount/pool')->getPoolUsageInfo();

            $function = 'addNotice';
            if ($usage->getPercent() == 100) {
                $function = 'addError';
            }

            $url = Mage::getSingleton('adminhtml/url')->getUrl('*/*/generate');
            Mage::getSingleton('adminhtml/session')->$function(
                Mage::helper('enterprise_giftcardaccount')->__('Code Pool used: <b>%.2f%%</b> (free <b>%d</b> of <b>%d</b> total). Generate new code pool <a href="%s">here</a>.', $usage->getPercent(), $usage->getFree(), $usage->getTotal(), $url)
            );
        }

        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcardaccount');
        $this->renderLayout();
    }


    /**
     * Create new Gift Card Account
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit GiftCardAccount
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_initGca();

        if (!$model->getId() && $id) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('enterprise_giftcardaccount')->__('This Gift Card Account no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($model->getId() ? $model->getCode() : $this->__('New Account'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $label = $id ? Mage::helper('enterprise_giftcardaccount')->__('Edit Gift Card Account')
            : Mage::helper('enterprise_giftcardaccount')->__('New Gift Card Account');
        $this->loadLayout()
            ->_addBreadcrumb($label, $label)
            ->_addContent(
                $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit')
                    ->setData('form_action_url', $this->getUrl('*/*/save'))
            )
            ->_addLeft(
                $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tabs')
            )
            ->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            $data = $this->_filterPostData($data);
            // init model and set data
            $id = $this->getRequest()->getParam('giftcardaccount_id');
            $model = $this->_initGca('giftcardaccount_id');
            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('enterprise_giftcardaccount')->__('This Gift Card Account no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }

            if (!empty($data)) {
                $model->addData($data);
            }

            // try to save it
            try {
                // save the data
                $model->save();
                $sending = null;
                $status = null;

                if ($model->getSendAction()) {
                    try {
                        if($model->getStatus()){
                            $model->sendEmail();
                            $sending = $model->getEmailSent();
                        }
                        else {
                            $status = true;
                        }
                    } catch (Exception $e) {
                        $sending = false;
                    }
                }

                if (!is_null($sending)) {
                    if ($sending) {
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('enterprise_giftcardaccount')->__('The gift card account has been saved and sent.')
                        );
                    } else {
                        Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('enterprise_giftcardaccount')->__('The gift card account has been saved, but email was not sent.')
                        );
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('enterprise_giftcardaccount')->__('The gift card account has been saved.')
                    );

                    if ($status) {
                        Mage::getSingleton('adminhtml/session')->addNotice(
                            Mage::helper('enterprise_giftcardaccount')->__('Email was not sent because the gift card account is not active.')
                        );
                    }
                }

                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                // init model and delete
                $model = Mage::getModel('enterprise_giftcardaccount/giftcardaccount');
                $model->load($id);
                $model->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('enterprise_giftcardaccount')->__('Gift Card Account has been deleted.')
                );
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('enterprise_giftcardaccount')->__('Unable to find a Gift Card Account to delete.')
        );
        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Render GCA grid
     */
    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock(
                'enterprise_giftcardaccount/adminhtml_giftcardaccount_grid',
                'giftcardaccount.grid'
            )
            ->toHtml()
        );
    }

    /**
     * Generate code pool
     */
    public function generateAction()
    {
        try {
            Mage::getModel('enterprise_giftcardaccount/pool')->generatePool();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('enterprise_giftcardaccount')->__('New code pool was generated.')
            );
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addException(
                $e, Mage::helper('enterprise_giftcardaccount')->__('Unable to generate new code pool.')
            );
        }
        $this->_redirectReferer('*/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/giftcardaccount');
    }

    /**
     * Render GCA history grid
     */
    public function gridHistoryAction()
    {
        $model = $this->_initGca();
        $id = (int)$this->getRequest()->getParam('id');
        if ($id && !$model->getId()) {
            return;
        }

        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tab_history')
                ->toHtml()
        );
    }

    /**
     * Load GCA from request
     *
     * @param string $idFieldName
     */
    protected function _initGca($idFieldName = 'id')
    {
        $this->_title($this->__('Customers'))->_title($this->__('Gift Card Accounts'));

        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = Mage::getModel('enterprise_giftcardaccount/giftcardaccount');
        if ($id) {
            $model->load($id);
        }
        Mage::register('current_giftcardaccount', $model);
        return $model;
    }

    /**
     * Export GCA grid to MSXML
     */
    public function exportMsxmlAction()
    {
        $this->_prepareDownloadResponse('giftcardaccounts.xml',
            $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_grid')
                ->getExcelFile($this->__('Gift Card Accounts'))
        );
    }

    /**
     * Export GCA grid to CSV
     */
    public function exportCsvAction()
    {
        $this->_prepareDownloadResponse('giftcardaccounts.csv',
            $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_grid')->getCsvFile()
        );
    }

    /**
     * Delete gift card accounts specified using grid massaction
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('giftcardaccount');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select gift card account(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getSingleton('enterprise_giftcardaccount/giftcardaccount')->load($id);
                    $model->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($ids))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        $data = $this->_filterDates($data, array('date_expires'));

        return $data;
    }

    /**
     * Setter for code pool status message flag
     *
     * @param bool $isShow
     */
    public function setShowCodePoolStatusMessage($isShow)
    {
        $this->_showCodePoolStatusMessage = (bool)$isShow;
    }
}
