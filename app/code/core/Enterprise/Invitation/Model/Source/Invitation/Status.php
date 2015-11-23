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
 * Invitation status source
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Model_Source_Invitation_Status
{
    /**
     * Return list of invitation statuses as options
     *
     * @return array
     */
    public function getOptions()
    {
        return array(
            Enterprise_Invitation_Model_Invitation::STATUS_NEW  => Mage::helper('enterprise_invitation')->__('Not Sent'),
            Enterprise_Invitation_Model_Invitation::STATUS_SENT => Mage::helper('enterprise_invitation')->__('Sent'),
            Enterprise_Invitation_Model_Invitation::STATUS_ACCEPTED => Mage::helper('enterprise_invitation')->__('Accepted'),
            Enterprise_Invitation_Model_Invitation::STATUS_CANCELED => Mage::helper('enterprise_invitation')->__('Discarded')
        );
    }

    /**
     * Return list of invitation statuses as options array.
     * If $useEmpty eq to true, add empty option
     *
     * @param boolean $useEmpty
     * @return array
     */
    public function toOptionsArray($useEmpty = false)
    {
        $result = array();

        if ($useEmpty) {
            $result[] = array('value' => '', 'label' => '');
        }
        foreach ($this->getOptions() as $value=>$label) {
            $result[] = array('value' => $value, 'label' => $label);
        }

        return $result;
    }

    /**
     * Return option text by value
     *
     * @param string $option
     * @return string
     */
    public function getOptionText($option)
    {
        $options = $this->getOptions();
        if (isset($options[$option])) {
            return $options[$option];
        }

        return null;
    }
}
