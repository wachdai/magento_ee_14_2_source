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

/**
 * Abstract Fieldset block for RMA view
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Abstract extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Form, created in parent block
     *
     * @var Varien_Data_Form
     */
    protected $_parentForm = null;

    /**
     * Get Form Object Which is Parent to this block
     *
     * @return null|Varien_Data_Form
     */
    public function getParentForm()
    {
        if (is_null($this->_parentForm) && $this->getParentBlock()) {
            $this->_parentForm = $this->getParentBlock()->getForm();
        }
        return $this->_parentForm;
    }

    /**
     * Add specific fieldset block to parent block form
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Absract
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('current_rma');
        $form = $this->getParentForm();

        $this->_addFieldset();

        if ($form && $model) {
            $form->setValues($model->getData());
        }
        if ($form) {
            $this->setForm($form);
        }

        return $this;
    }

    /**
     * Add fieldset with required fields
     */
    protected function _addFieldset()
    {
    }

    /**
     * Getter of model's data
     *
     * @param string $field
     * @return mixed
     */
    public function getRmaData($field)
    {
        $model = Mage::registry('current_rma');
        if ($model) {
            return $model->getData($field);
        } else {
            return null;
        }
    }

    /**
     * Get Order, RMA Attached to
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Get Customer Name (billing name)
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->escapeHtml($this->getOrder()->getCustomerName());
    }
}
