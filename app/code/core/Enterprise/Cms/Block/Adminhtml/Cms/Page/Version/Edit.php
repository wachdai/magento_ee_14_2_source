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
 * Edit version page
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Page_Version_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_objectId   = 'version_id';
    protected $_blockGroup = 'enterprise_cms';
    protected $_controller = 'adminhtml_cms_page_version';

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $version = Mage::registry('cms_page_version');

        $config = Mage::getSingleton('enterprise_cms/config');
        /* @var $config Enterprise_Cms_Model_Config */

        // Add 'new button' depending on permission
        if ($config->canCurrentUserSaveVersion()) {
            $this->_addButton('new', array(
                    'label'     => Mage::helper('enterprise_cms')->__('Save as New Version'),
                    'onclick'   => "editForm.submit('" . $this->getNewUrl() . "');",
                    'class'     => 'new',
                ));

            $this->_addButton('new_revision', array(
                    'label'     => Mage::helper('enterprise_cms')->__('New Revision...'),
                    'onclick'   => "setLocation('" . $this->getNewRevisionUrl() . "');",
                    'class'     => 'new',
                ));
        }

        $isOwner = $config->isCurrentUserOwner($version->getUserId());
        $isPublisher = $config->canCurrentUserPublishRevision();

        // Only owner can remove version if he has such permissions
        if (!$isOwner || !$config->canCurrentUserDeleteVersion()) {
            $this->removeButton('delete');
        }

        // Only owner and publisher can save version
        if (($isOwner || $isPublisher) && $config->canCurrentUserSaveVersion()) {
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('enterprise_cms')->__('Save and Continue Edit'),
                'onclick'   => "editForm.submit($('edit_form').action+'back/edit/');",
                'class'     => 'save',
            ), 1);
        } else {
            $this->removeButton('save');
        }
    }

    /**
     * Retrieve text for header element depending
     * on loaded page and version
     *
     * @return string
     */
    public function getHeaderText()
    {
        $versionLabel = $this->escapeHtml(Mage::registry('cms_page_version')->getLabel());
        $title = $this->escapeHtml(Mage::registry('cms_page')->getTitle());

        if (!$versionLabel) {
            $versionLabel = Mage::helper('enterprise_cms')->__('N/A');
        }

        return Mage::helper('enterprise_cms')->__("Edit Page '%s' Version '%s'", $title, $versionLabel);
    }

    /**
     * Get URL for back button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/cms_page/edit',
             array(
                'page_id' => Mage::registry('cms_page')->getPageId(),
                'tab' => 'versions'
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
     * Get URL for new button
     *
     * @return string
     */
    public function getNewUrl()
    {
        return $this->getUrl('*/*/new', array('_current' => true));
    }

    /**
     * Get Url for new revision button
     *
     * @return string
     */
    public function getNewRevisionUrl()
    {
        return $this->getUrl('*/cms_page_revision/new', array('_current' => true));
    }
}
