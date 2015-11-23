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
 * @package     Enterprise_Invitation
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Invitation view block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Adminhtml_Invitation_Add extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_objectId = 'invitation_id';
    protected $_blockGroup = 'enterprise_invitation';
    protected $_controller = 'adminhtml_invitation';
    protected $_mode = 'add';

    /**
     * Prepares form scripts
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Invitation_Add
     */
    protected function _prepareLayout()
    {
        $validationMessage = addcslashes(
            Mage::helper('enterprise_invitation')->__('Please enter valid email addresses, separated by new line.'),
            "\\'\n\r"
        );
        $this->_formInitScripts[] = "
        Validation.addAllThese([
            ['validate-emails', '$validationMessage', function (v) {
                v = v.strip();
                var emails = v.split(/[\\s]+/g);
                for (var i = 0, l = emails.length; i < l; i++) {
                    if (!Validation.get('validate-email').test(emails[i])) {
                        return false;
                    }
                }
                return true;
            }]
        ]);";
        return parent::_prepareLayout();
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('enterprise_invitation')->__('New Invitations');
    }

}
