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
 * Edit revision page
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Page_Revision_Edit extends Mage_Adminhtml_Block_Cms_Page_Edit
{

    /**
     * Constructor. Modifying default CE buttons.
     *
     * @return Enterprise_Cms_Block_Adminhtml_Cms_Page_Revision_Edit
     */
    public function __construct()
    {
        parent::__construct();

        $this->removeButton('delete');

        $this->_objectId = 'revision_id';

        $this->_controller = 'adminhtml_cms_page_revision';
        $this->_blockGroup = 'enterprise_cms';

        /* @var $config Enterprise_Cms_Model_Config */
        $config = Mage::getSingleton('enterprise_cms/config');

        $this->setFormActionUrl($this->getUrl('*/cms_page_revision/save'));

        $objId = $this->getRequest()->getParam($this->_objectId);

        if (!empty($objId) && $config->canCurrentUserDeleteRevision()) {
            $this->_addButton('delete_revision', array(
                'label'     => Mage::helper('enterprise_cms')->__('Delete'),
                'class'     => 'delete',
                'onclick'   => 'deleteConfirm(\''
                    . Mage::helper('core')->quoteEscape(
                        Mage::helper('enterprise_cms')->__('Are you sure you want to delete this revision?'),
                        true
                    )
                    . '\', \'' . $this->getDeleteUrl() . '\')',
            ));
        }

        $this->_addButton('preview', array(
            'label'     => Mage::helper('enterprise_cms')->__('Preview'),
            'onclick'   => "previewAction('edit_form', editForm, '" . $this->getPreviewUrl() . "')",
            'class'     => 'preview',
        ));

        if ($config->canCurrentUserPublishRevision()) {
            $this->_addButton('publish', array(
                'id'        => 'publish_button',
                'label'     => Mage::helper('enterprise_cms')->__('Publish'),
                'onclick'   => "publishAction('" . $this->getPublishUrl() . "')",
                'class'     => 'publish' . (Mage::registry('cms_page')->getId()? '' : ' no-display'),
            ), 1);

            if ($config->canCurrentUserSaveRevision()) {
                $this->_addButton('save_publish', array(
                    'id'        => 'save_publish_button',
                    'label'     => Mage::helper('enterprise_cms')->__('Save and Publish'),
                    'onclick'   => "saveAndPublishAction(editForm, '" . $this->getSaveUrl() . "')",
                    'class'     => 'publish no-display',
                ), 1);
            }

            $this->_updateButton('saveandcontinue', 'level', 2);
        }

        if ($config->canCurrentUserSaveRevision()) {
            $this->_updateButton('save', 'label', Mage::helper('enterprise_cms')->__('Save'));
            $this->_updateButton('save', 'onclick', 'editForm.submit(\'' . $this->getSaveUrl() . '\');');
            $this->_updateButton(
                'saveandcontinue',
                'onclick',
                'editForm.submit(\'' . $this->getSaveUrl() . '\'+\'back/edit/\');'
            );

            // Adding button to create new version
            $this->_addButton('new_version', array(
                'id'        => 'new_version',
                'label'     => Mage::helper('enterprise_cms')->__('Save in New Version...'),
                'onclick'   => 'newVersionAction()',
                'class'     => 'new',
            ));

            $versionMessage     = Mage::helper('enterprise_cms')->__('Specify New Version Name (required)');
            $invalidNameMessage = Mage::helper('enterprise_cms')->__('You should specify valid name');
            $this->_formScripts[] = "
                function newVersionAction(){
                    var versionName = prompt('" . Mage::helper('core')->jsQuoteEscape($versionMessage) . "', '')
                    if (versionName == '') {
                        alert('" . Mage::helper('core')->jsQuoteEscape($invalidNameMessage) . "');
                        return false;
                    } else if (versionName == null) {
                        return false;
                    }

                    $('page_label').value = versionName;
                    editForm.submit('" . $this->getNewVersionUrl() . "');
                }
            ";

        } else {
            $this->removeButton('save');
            $this->removeButton('saveandcontinue');
        }

        return $this;
    }

    /**
     * Retrieve text for header element depending
     * on loaded page and revision
     *
     * @return string
     */
    public function getHeaderText()
    {
        $revisionNumber = Mage::registry('cms_page')->getRevisionNumber();
        $title = $this->escapeHtml(Mage::registry('cms_page')->getTitle());

        if ($revisionNumber) {
            return Mage::helper('enterprise_cms')->__(
                "Edit Page '%s' Revision #%s",
                $title,
                $this->escapeHtml($revisionNumber)
            );
        } else {
            return Mage::helper('enterprise_cms')->__("Edit Page '%s' New Revision", $title);
        }
    }

    /**
     * Check permission for passed action
     * Rewrite CE save permission to EE save_revision
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        if ($action == 'save') {
            $action = 'save_revision';
        }
        return parent::_isAllowedAction($action);
    }

    /**
     * Get URL for back button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/cms_page_version/edit',
             array(
                'page_id' => Mage::registry('cms_page')->getPageId(),
                'version_id' => Mage::registry('cms_page')->getVersionId()
             ));
    }

    /**
     * Get URL for delete button
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current' => true));
    }

    /**
     * Get URL for publish button
     *
     * @return string
     */
    public function getPublishUrl()
    {
        return $this->getUrl('*/*/publish', array('_current' => true));
    }

    /**
     * Get URL for preview button
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/preview');
    }

    /**
     * Adding info block html before form html
     *
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getChildHtml('revision_info') . parent::getFormHtml();
    }

    /**
     * Save into new version link
     *
     * @return string
     */
    public function getNewVersionUrl()
    {
        return $this->getUrl('*/cms_page_version/new');
    }
}
