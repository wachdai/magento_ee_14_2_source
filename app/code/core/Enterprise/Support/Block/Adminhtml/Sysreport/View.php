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
 * @package     Enterprise_Support
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

class Enterprise_Support_Block_Adminhtml_Sysreport_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Object ID name
     *
     * @var string
     */
    protected $_objectId = 'id';

    /**
     * Add back button
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->_controller = 'adminhtml_sysreport';
        $this->_blockGroup = 'enterprise_support';
        $this->_mode       = 'view';
        parent::__construct();

        $this->removeButton('reset');
        $this->updateButton('save', 'label', Mage::helper('adminhtml')->__('Download'));
        $this->updateButton('save', 'onclick', "setLocation('{$this->getDownloadUrl()}')");
        $this->updateButton(
            'delete',
            'onclick',
            'deleteConfirm(\''
            . Mage::helper('core')->jsQuoteEscape(
                Mage::helper('enterprise_support')->__('Are you sure you want to delete the system report?')
            )
            . '\', \'' . $this->getDeleteUrl() . '\')');
        $this->addButton('go_to_top', array(
            'label'   => Mage::helper('adminhtml')->__('Go to Top'),
            'onclick' => 'setLocation(\'#top\')',
            'class'   => 'go'
        ), 0, 1);
    }

    /**
     * Get header text for system report view page
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getSystemReport() && $this->getSystemReport()->getId()) {
            $dateString = $this->getSystemReport()->getCreatedAt();
            return $this->escapeHtml(
                $dateString . ' ' . Mage::helper('enterprise_support')->getSinceTimeString($dateString)
            );
        } else {
            return Mage::helper('enterprise_support')->__("Requested report doesn't exist");
        }
    }

    /**
     * Retrieve current system report model
     *
     * @return Enterprise_Support_Model_Resource_Sysreport
     */
    public function getSystemReport()
    {
        return Mage::registry('current_sysreport');
    }

    /**
     * Retrieve download URL
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->getUrl(
            '*/*/download',
            array($this->_objectId => $this->getRequest()->getParam($this->_objectId))
        );
    }
}
