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
 * @package     Enterprise_Pci
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Encryption key changer controller
 *
 */
class Enterprise_Pci_Adminhtml_Crypt_KeyController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check whether local.xml is writeable
     *
     * @return bool
     */
    protected function _checkIsLocalXmlWriteable()
    {
        $filename = Mage::getBaseDir('etc') . DS . 'local.xml';
        if (!is_writeable($filename)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('enterprise_pci')->__('To make key change possible, make sure the following file is writeable: %s', realpath($filename))
            );
            return false;
        }
        return true;
    }

    /**
     * Render main page with form
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Manage Encription Key'));

        $this->_checkIsLocalXmlWriteable();
        $this->loadLayout();
        $this->_setActiveMenu('system/crypt_key');

        if (($formBlock = $this->getLayout()->getBlock('pci.crypt.key.form'))
            && $data = Mage::getSingleton('adminhtml/session')->getFormData(true)) {
            /* @var Enterprise_Pci_Block_Adminhtml_Crypt_Key_Form $formBlock */
            $formBlock->setFormData($data);
        }

        $this->renderLayout();
    }

    /**
     * Process saving new encryption key
     *
     */
    public function saveAction()
    {
        try {
            $key = null;
            if (!$this->_checkIsLocalXmlWriteable()) {
                throw new Exception('');
            }
            if (0 == $this->getRequest()->getPost('generate_random')) {
                $key = $this->getRequest()->getPost('crypt_key');
                if (empty($key)) {
                    throw new Exception(Mage::helper('enterprise_pci')->__('Please enter an encryption key.'));
                }
                Mage::helper('core')->validateKey($key);
            }
            $newKey = Mage::getResourceSingleton('enterprise_pci/key_change')->changeEncryptionKey($key);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('enterprise_pci')->__('Encryption key has been changed.')
            );
            if (!$key) {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('enterprise_pci')->__('Your new encryption key: <span style="font-family:monospace;">%s</span>. Please make a note of it and make sure you keep it in a safe place.', $newKey)
                );
            }
            Mage::app()->cleanCache();
        }
        catch (Exception $e) {
            if ($message = $e->getMessage()) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            Mage::getSingleton('adminhtml/session')->setFormData(array('crypt_key' => $key));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Check whether current administrator session allows this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/crypt_key');
    }
}
