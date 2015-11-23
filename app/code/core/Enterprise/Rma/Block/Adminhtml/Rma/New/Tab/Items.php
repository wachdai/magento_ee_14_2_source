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
 * Items Tab in Edit RMA form
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_New_Tab_Items extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Class constructor
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('rma_items_grid');
    }

    /**
     * Get "Add Products" button
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        $addButtonData = array(
            'label' => Mage::helper('enterprise_rma')->__('Add Products'),
            'onclick' => "rma.addProduct()",
            'class' => 'add',
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($addButtonData)->toHtml();
    }

    /**
     * Get "Add products to RMA" button
     *
     * @return string
     */
    public function getAddProductButtonHtml()
    {
        $addButtonData = array(
            'label' => Mage::helper('enterprise_rma')->__('Add Selected Product(s) to RMA'),
            'onclick' => "rma.addSelectedProduct()",
            'class' => 'add',
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($addButtonData)->toHtml();
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_New_Tab_Items
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $htmlIdPrefix = 'rma_properties_';
        $form->setHtmlIdPrefix($htmlIdPrefix);

        $model = Mage::registry('current_rma');

        $fieldset = $form->addFieldset('rma_item_fields', array());

        $fieldset->addField('product_name', 'text', array(
            'label'=> Mage::helper('enterprise_rma')->__('Product Name'),
            'name' => 'product_name',
            'required'  => false
        ));

        $fieldset->addField('product_sku', 'text', array(
            'label'=> Mage::helper('enterprise_rma')->__('SKU'),
            'name' => 'product_sku',
            'required'  => false
        ));

        //Renderer puts available quantity instead of order_item_id
        $fieldset->addField('qty_ordered', 'text', array(
            'label'=> Mage::helper('enterprise_rma')->__('Remaining Qty'),
            'name' => 'qty_ordered',
            'required'  => false,
        ));

        $fieldset->addField('qty_requested', 'text', array(
            'label'=> Mage::helper('enterprise_rma')->__('Requested Qty'),
            'name' => 'qty_requested',
            'required' => false,
            'class' => 'validate-greater-than-zero'
        ));

        $reasonOtherAttribute =
            Mage::getModel('enterprise_rma/item_form')->setFormCode('default')->getAttribute('reason_other');

        $fieldset->addField('reason_other', 'text', array(
            'label'     => $reasonOtherAttribute->getStoreLabel(),
            'name'      => 'reason_other',
            'maxlength' => 255,
            'required'  => false
        ));

        $fieldset->addField('reason', 'select', array(
            'label'=> Mage::helper('enterprise_rma')->__('Reason to Return'),
            'options' => array(''=>'')
                + Mage::helper('enterprise_rma/eav')->getAttributeOptionValues('reason')
                + array('other' => $reasonOtherAttribute->getStoreLabel()),
            'name' => 'reason',
            'required' => false
        ))->setRenderer(
            $this->getLayout()->createBlock('enterprise_rma/adminhtml_rma_new_tab_items_renderer_reason')
        );

        $fieldset->addField('condition', 'select', array(
            'label'=> Mage::helper('enterprise_rma')->__('Item Condition'),
            'options' => array(''=>'') + Mage::helper('enterprise_rma/eav')->getAttributeOptionValues('condition'),
            'name' => 'condition',
            'required' => false,
            'class' => 'action-select'
        ));

        $fieldset->addField('resolution', 'select', array(
            'label'=> Mage::helper('enterprise_rma')->__('Resolution'),
            'options' => array(''=>'') + Mage::helper('enterprise_rma/eav')->getAttributeOptionValues('resolution'),
            'name' => 'resolution',
            'required' => false,
            'class' => 'action-select'
        ));

        $fieldset->addField('delete_link', 'label', array(
            'label'=> Mage::helper('enterprise_rma')->__('Delete'),
            'name' => 'delete_link',
            'required' => false
        ));

        $fieldset->addField('add_details_link', 'label', array(
            'label'=> Mage::helper('enterprise_rma')->__('Add Details'),
            'name' => 'add_details_link',
            'required' => false
        ));

        $this->setForm($form);

        return $this;
    }

    /**
     * Get Header Text for Order Selection
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('enterprise_rma')->__('Items');
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('enterprise_rma')->__('RMA Items');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
}
