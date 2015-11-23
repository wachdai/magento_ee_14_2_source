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
 * @package     Enterprise_Checkout
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Admin Checkout main form container
 *
 * @method string                                           getAdditionalJavascript()
 * @method string                                           getListType()
 * @method Enterprise_Checkout_Block_Adminhtml_Sku_Abstract setListType()
 * @method string                                           getDataContainerId()
 * @method Enterprise_Checkout_Block_Adminhtml_Sku_Abstract setDataContainerId()
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Checkout_Block_Adminhtml_Sku_Abstract extends Mage_Adminhtml_Block_Template
{
    /**
     * List type of current block
     */
    const LIST_TYPE = 'add_by_sku';

    /**
     * Initialize SKU container
     */
    public function __construct()
    {
        $this->setTemplate('enterprise/checkout/sku/add.phtml');
        // Used by JS to tell accordions from each other
        $this->setId('sku');
        /* @see Enterprise_Checkout_Adminhtml_CheckoutController::_getListItemInfo() */
        $this->setListType(self::LIST_TYPE);
        $this->setDataContainerId('sku_container');
    }

    /**
     * Define ADD and DEL buttons
     *
     * @return Enterprise_Checkout_Block_Adminhtml_Sku_Abstract
     */
    protected function _prepareLayout()
    {
        /* @var $headBlock Mage_Page_Block_Html_Head */
        $headBlock = parent::_prepareLayout()->getLayout()->getBlock('head');
        if ($headBlock) {
            // Head block is not defined on AJAX request
            $headBlock->addJs('enterprise/adminhtml/addbysku.js');
        }

        $this->setChild('deleteButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => '',
                    'onclick' => 'addBySku.del(this)',
                    'class'   => 'delete'
                ))
        );

        $this->setChild('addButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => '', // Widget button of class 'add' has '+' icon by default
                    'onclick' => 'addBySku.add()',
                    'class'   => 'add'
                ))
        );

        return $this;
    }

    /**
     * HTML of "+" button, which adds new field for SKU and qty
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('addButton');
    }

    /**
     * HTML of "x" button, which removes field with SKU and qty
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('deleteButton');
    }

    /**
     * Returns URL to which CSV file should be submitted
     *
     * @abstract
     * @return string
     */
    abstract public function getFileUploadUrl();

    /**
     * Configuration data for AddBySku instance
     *
     * @return string
     */
    public function getAddBySkuDataJson()
    {
        $data = array(
            'dataContainerId'  => $this->getDataContainerId(),
            'deleteButtonHtml' => $this->getDeleteButtonHtml(),
            'fileUploaded'     => Enterprise_Checkout_Helper_Data::REQUEST_PARAMETER_SKU_FILE_IMPORTED_FLAG,
            // All functions requiring listType affects error grid only
            'listType'         => Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Abstract::LIST_TYPE,
            'errorGridId'      => $this->getErrorGridId(),
            'fileFieldName'    => Enterprise_Checkout_Model_Import::FIELD_NAME_SOURCE_FILE,
            'fileUploadUrl'    => $this->getFileUploadUrl(),
        );

        $json = Mage::helper('core')->jsonEncode($data);
        return $json;
    }

    /**
     * JavaScript instance of AdminOrder or AdminCheckout
     *
     * @abstract
     * @return string
     */
    abstract public function getJsOrderObject();

    /**
     * HTML ID of error grid container
     *
     * @abstract
     * @return string
     */
    abstract public function getErrorGridId();

    /**
     * Retrieve context specific JavaScript
     *
     * @return string
     */
    public function getContextSpecificJs()
    {
        return '';
    }

    /**
     * Retrieve additional JavaScript
     *
     * @return string
     */
    public function getAdditionalJavascript()
    {
        return '';
    }
}
