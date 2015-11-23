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
 * @package     Enterprise_UrlRewrite
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * UrlRedirects edit form
 *
 * @category   Enterprise
 * @package    Enterprise_UrlRewrite
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Select_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Set form id and title
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('urlRedirect_select');
    }

    /**
     * Prepare the form layout
     *
     * @return Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset(
            'base_fieldset', array(
                'legend' => $this->__('Select URL Redirect Type')
            )
        );

        $fieldset->addField(
            'redirect_type', 'select', array(
                'label'   => $this->__('Type'),
                'title'   => $this->__('Type'),
                'name'    => 'options',
                'options' => $this->_getRedirectTypeOptions(),
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Provides redirect options
     *
     * @return array
     */
    protected function _getRedirectTypeOptions()
    {
        $defaultValue = array('' => $this->__('-- Please Select --'));
        $types = Mage::getSingleton('enterprise_urlrewrite/source_redirect_option_type')->toOptionArray();
        return array_merge($defaultValue, $types);
    }
}
