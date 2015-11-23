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
class Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Set form id and title
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct();

        $this->setId('urlRedirect_form');
        $this->setTitle($this->__('Block Information'));

        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
    }

    /**
     * Prepare the form layout
     *
     * @return Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Edit_Form
     */
    protected function _prepareForm()
    {
        $redirect = Mage::registry('current_url_redirect');
        $product = Mage::registry('current_product');
        $category = Mage::registry('current_category');

        $this->_loadRedirectData($redirect);

        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post'
            )
        );

        // set form data either from model values or from session
        $formValues = array(
            'store_id'    => $redirect->getStoreId(),
            'identifier'  => $redirect->getIdentifier(),
            'target_path' => $redirect->getTargetPath(),
            'options'     => $redirect->getOptions(),
            'description' => $redirect->getDescription(),
        );

        $fieldset = $form->addFieldset(
            'base_fieldset', array(
                'legend' => $this->__('URL Redirect Information')
            )
        );

        // Get store switcher or a hidden field with store id.
        if (!Mage::app()->isSingleStoreMode()) {
            $stores  = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm();

            $element = $fieldset->addField('store_id', 'select', array(
                'label'     => $this->__('Store'),
                'title'     => $this->__('Store'),
                'name'      => 'store_id',
                'required'  => true,
                'values'    => $stores,
                'disabled'  => true,
                'value'     => $formValues['store_id'],
            ));

            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $element->setRenderer($renderer);

            if (!$redirect->getIsSystem()) {
                $element->unsetData('disabled');
            }
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
        }

        $fieldset->addField(
            'identifier', 'text', array(
                'label'    => $this->__('Request Path'),
                'title'    => $this->__('Request Path'),
                'name'     => 'identifier',
                'required' => true,
                'value'    => $formValues['identifier']
            )
        );

        $fieldset->addField(
            'target_path', 'text', array(
                'label'    => $this->__('Target Path'),
                'title'    => $this->__('Target Path'),
                'name'     => 'target_path',
                'required' => true,
                'value'    => $formValues['target_path'],
            )
        );

        $fieldset->addField(
            'options', 'select', array(
                'label'   => $this->__('Redirect Type'),
                'title'   => $this->__('Redirect Type'),
                'name'    => 'options',
                'options' => array(
                    ''   => $this->__('No'),
                    'R'  => $this->__('Temporary (302)'),
                    'RP' => $this->__('Permanent (301)'),
                ),
                'value'   => $formValues['options']
            )
        );

        $fieldset->addField(
            'description', 'textarea', array(
                'label' => $this->__('Description'),
                'title' => $this->__('Description'),
                'name'  => 'description',
                'cols'  => 20,
                'rows'  => 5,
                'value' => $formValues['description'],
                'wrap'  => 'soft'
            )
        );

        $productId = $product ? $product->getId() : null;
        $categoryId = $category ? $category->getId() : null;
        $form->setUseContainer(true);
        $form->setAction($this->getUrl('*/*/save', array(
            'id' => $redirect->getId(),
            'product' => $productId,
            'category' => $categoryId,
        )));
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Load additional redirect data
     *
     * @param Enterprise_UrlRewrite_Model_Redirect $redirect
     * @return Enterprise_UrlRewrite_Block_Adminhtml_UrlRedirect_Edit_Form
     */
    protected function _loadRedirectData(Enterprise_UrlRewrite_Model_Redirect $redirect)
    {
        return $this;
    }
}
