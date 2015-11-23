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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Rma_Block_Adminhtml_Rma_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Variable to store RMA instance
     *
     * @var null|Enterprise_Rma_Model_Rma
     */
    protected $_rma = null;

    /**
     * Initialize RMA edit page. Set management buttons
     *
     */
    public function __construct()
    {
        $this->_objectId    = 'entity_id';
        $this->_controller  = 'adminhtml_rma';
        $this->_blockGroup  = 'enterprise_rma';

        parent::__construct();

        $statusIsClosed = in_array(
            $this->getRma()->getStatus(),
            array(
                Enterprise_Rma_Model_Rma_Source_Status::STATE_CLOSED,
                Enterprise_Rma_Model_Rma_Source_Status::STATE_PROCESSED_CLOSED
            )
        );

        if (!$statusIsClosed) {
            $this->_addButton('save_and_edit_button', array(
                    'label'   => Mage::helper('enterprise_rma')->__('Save and Continue Edit'),
                    'onclick' => 'saveAndContinueEdit()',
                    'class'   => 'save'
                ), 100
            );
            $this->_formScripts[] = 'function saveAndContinueEdit() {
                editForm.submit($(\'edit_form\').action + \'back/edit/\');}';

            $confirmationMessage = Mage::helper('core')->jsQuoteEscape(
                Mage::helper('enterprise_rma')->__('Are you sure you want to close this RMA request?')
            );
            $this->_addButton('close', array(
                'label'     => Mage::helper('enterprise_rma')->__('Close'),
                'onclick'   => 'confirmSetLocation(\'' . $confirmationMessage . '\', \'' . $this->getCloseUrl() . '\')'
                )
            );
        } else {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        }

        $this->_addButton('print', array(
            'label'     => Mage::helper('enterprise_rma')->__('Print'),
            'class'     => 'save',
            'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
            ), 101
        );

        $this->_removeButton('delete');
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        $referer = $this->getRequest()->getServer('HTTP_REFERER');

        if (strpos($referer, 'sales_order') !== false) {
            return $this->getUrl('*/sales_order/view/',
                array(
                    'order_id'  => $this->getRma()->getOrderId(),
                    'active_tab'=> 'order_rma'
                )
            );
        } elseif (strpos($referer, 'customer') !== false) {
            return $this->getUrl('*/customer/edit/',
                array(
                    'id'  => $this->getRma()->getCustomerId(),
                    'active_tab'=> 'customer_edit_tab_rma'
                )
            );
        } else {
            return parent::getBackUrl();
        }
    }

    /**
     * Declare rma instance
     *
     * @return  Enterprise_Rma_Model_Item
     */
    public function getRma()
    {
        if (is_null($this->_rma)) {
            $this->_rma = Mage::registry('current_rma');
        }
        return $this->_rma;
    }

    /**
     * Get header text for RMA edit page
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getRma()->getId()) {
            return Mage::helper('enterprise_rma')->__('RMA #%s - %s', $this->getRma()->getIncrementId(), $this->getRma()->getStatusLabel());
        }

        return '';
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save', array(
            'rma_id' => $this->getRma()->getId()
        ));
    }

    /**
     * Get print RMA action URL
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print', array(
            'rma_id' => $this->getRma()->getId()
        ));
    }

    /**
     * Get close RMA action URL
     *
     * @return string
     */
    public function getCloseUrl()
    {
        return $this->getUrl('*/*/close', array(
            'entity_id' => $this->getRma()->getId()
        ));
    }

}
