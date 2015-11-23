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
 * "Add by SKU" error block
 *
 * @method Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Abstract setListType()
 * @method string                                                  getListType()
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Checkout_Block_Adminhtml_Sku_Errors_Abstract extends Mage_Adminhtml_Block_Widget
{
    /*
     * JS listType of the error grid
     */
    const LIST_TYPE = 'errors';

    /**
     * List of failed items
     *
     * @var null|array
     */
    protected $_failedItems;

    /**
     * Cart instance
     *
     * @var Enterprise_Checkout_Model_Cart|null
     */
    protected $_cart;

    /**
     * Define ID
     */
    public function __construct()
    {
        $this->setListType(self::LIST_TYPE);
        $this->setTemplate('enterprise/checkout/sku/errors.phtml');
    }

    /**
     * Accordion header
     *
     * @return string
     */
    public function getHeaderText()
    {
        return $this->__('<span id="sku-attention-num">%s</span> product(s) require attention', count($this->getFailedItems()));
    }

    /**
     * Retrieve CSS class for header
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'sku-errors';
    }

    /**
     * Retrieve "Add to order" button
     *
     * @return mixed
     */
    public function getButtonsHtml()
    {
        $buttonData = array(
            'label'   => $this->__('Remove All'),
            'onclick' => 'addBySku.removeAllFailed()',
            'class'   => 'delete',
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($buttonData)->toHtml();
    }

    /**
     * Retrieve items marked as unsuccessful after prepareAddProductsBySku()
     *
     * @return array
     */
    public function getFailedItems()
    {
        if (is_null($this->_failedItems)) {
            $this->_failedItems = $this->getCart()->getFailedItems();
        }
        return $this->_failedItems;
    }

    /**
     * Retrieve url to configure item
     *
     * @return string
     */
    abstract public function getConfigureUrl();

    /**
     * Disable output of error grid in case no errors occurred
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->getFailedItems();
        if (empty($this->_failedItems)) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Implementation-specific JavaScript to be inserted into template
     *
     * @return string
     */
    public function getAdditionalJavascript()
    {
        return '';
    }

    /**
     * Retrieve cart instance
     *
     * @return Enterprise_Checkout_Model_Cart
     */
    public function getCart()
    {
        if (!isset($this->_cart)) {
            $this->_cart = Mage::getSingleton('enterprise_checkout/cart');
        }
        return $this->_cart;
    }

    /**
     * Retrieve current store instance
     *
     * @abstract
     * @return Mage_Core_Model_Store
     */
    abstract public function getStore();

    /**
     * Get title of button, that adds products from grid
     *
     * @abstract
     * @return string
     */
    abstract public function getAddButtonTitle();
}
