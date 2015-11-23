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
 * @package     Enterprise_Catalog
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Block for UrlRedirects edit form and selectors container
 *
 * @category   Enterprise
 * @package    Enterprise_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Catalog_Block_Adminhtml_Redirect_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Retrieves url for Back button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->_getCategoryId() && $this->_getProductId()) {
            return $this->getUrl('*/*/select', array('type' => 'category', 'product' => $this->_getProductId()));
        } elseif ($this->_getProductId()) {
            return $this->getUrl('*/*/select', array('type' => 'product'));
        } elseif ($this->_getCategoryId()) {
            return $this->getUrl('*/*/select', array('type' => 'category'));
        }

        return parent::getBackUrl();
    }

    /**
     * Prepares edit form block based on
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if ($this->_getProductId()) {
            $form = $this->getLayout()->createBlock('enterprise_catalog/adminhtml_redirect_edit_form_product');
            $this->setChild('form', $form);
        } elseif ($this->_getCategoryId()) {
            $form = $this->getLayout()->createBlock('enterprise_catalog/adminhtml_redirect_edit_form_category');
            $this->setChild('form', $form);
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieves product_id parameter
     *
     * @return int
     */
    protected function _getProductId()
    {
        return (int)$this->getRequest()->getParam('product');
    }

    /**
     * Retrieves category_id parameter
     *
     * @return int
     */
    protected function _getCategoryId()
    {
        return (int)$this->getRequest()->getParam('category_id');
    }
}
