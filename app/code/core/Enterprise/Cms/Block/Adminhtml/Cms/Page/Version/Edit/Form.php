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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Form for version edit page
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Cms_Block_Adminhtml_Cms_Page_Version_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Define customized form template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('enterprise/cms/page/version/form.phtml');
    }

    /**
     * Preparing from for version page
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Page_Revision_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('_current' => true)),
                'method' => 'post'
            ));

        $form->setUseContainer(true);

        /* @var $model Mage_Cms_Model_Page */
        $version = Mage::registry('cms_page_version');

        $config = Mage::getSingleton('enterprise_cms/config');
        /* @var $config Enterprise_Cms_Model_Config */

        $isOwner = $config->isCurrentUserOwner($version->getUserId());
        $isPublisher = $config->canCurrentUserPublishRevision();

        $fieldset = $form->addFieldset('version_fieldset',
            array('legend' => Mage::helper('enterprise_cms')->__('Version Information'),
            'class' => 'fieldset-wide'));

        $fieldset->addField('version_id', 'hidden', array(
            'name'      => 'version_id'
        ));

        $fieldset->addField('page_id', 'hidden', array(
            'name'      => 'page_id'
        ));

        $fieldset->addField('label', 'text', array(
            'name'      => 'label',
            'label'     => Mage::helper('enterprise_cms')->__('Version Label'),
            'disabled'  => !$isOwner,
            'required'  => true
        ));

        $fieldset->addField('access_level', 'select', array(
            'label'     => Mage::helper('enterprise_cms')->__('Access Level'),
            'title'     => Mage::helper('enterprise_cms')->__('Access Level'),
            'name'      => 'access_level',
            'options'   => Mage::helper('enterprise_cms')->getVersionAccessLevels(),
            'disabled'  => !$isOwner && !$isPublisher
        ));

        if ($isPublisher) {
            $fieldset->addField('user_id', 'select', array(
                'label'     => Mage::helper('enterprise_cms')->__('Owner'),
                'title'     => Mage::helper('enterprise_cms')->__('Owner'),
                'name'      => 'user_id',
                'options'   => Mage::helper('enterprise_cms')->getUsersArray(!$version->getUserId()),
                'required'  => !$version->getUserId()
            ));
        }

        $form->setValues($version->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
