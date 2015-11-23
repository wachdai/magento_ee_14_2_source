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

class Enterprise_Support_Block_Adminhtml_Backup_Log extends Mage_Adminhtml_Block_Widget_Container
{

    /**
     * @var Enterprise_Support_Model_Backup
     */
    protected $_backup;

    /**
     * Add back button
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        $this->_addButton('back', array(
            'label'   => Mage::helper('enterprise_support')->__('Back'),
            'onclick' => "setLocation('" . Mage::getSingleton('adminhtml/url')->getUrl('*/*/'). "')",
            'class'   => 'back'
        ));
    }

    /**
     * Header text getter
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('enterprise_support')->__('Backup Log Details');
    }

    /**
     * Get Backup
     *
     * @return Enterprise_Support_Model_Backup
     */
    public function getBackup()
    {
        if (!$this->_backup) {
            $this->_backup = Mage::getModel('enterprise_support/backup')->load($this->getRequest()->getParam('id', 0));
        }

        return $this->_backup;
    }

    /**
     * Set Backup
     *
     * @param $backup
     */
    public function setBackup($backup)
    {
        $this->_backup = $backup;
    }
}
