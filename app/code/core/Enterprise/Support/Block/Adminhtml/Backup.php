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

class Enterprise_Support_Block_Adminhtml_Backup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize backup grid container
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_controller = 'adminhtml_backup';
        $this->_blockGroup = 'enterprise_support';
        $this->_headerText = Mage::helper('enterprise_support')->__('Manage System Backups');
        $this->_addButtonLabel = Mage::helper('enterprise_support')->__('New Backup');
        $this->_addButton('refresh', array(
            'label' => Mage::helper('enterprise_support')->__('Refresh Status'),
            'onclick'   => 'setLocation(\'' . $this->getUrl("*/*/index") .'\')',
        ), 0);
        parent::__construct($args);
    }
}
