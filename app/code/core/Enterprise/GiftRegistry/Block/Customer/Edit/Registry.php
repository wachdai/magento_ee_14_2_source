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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer giftregistry edit block
 *
 * @category   Enterprise
 * @package    Enterprise_GiftRegistry
 */
class Enterprise_GiftRegistry_Block_Customer_Edit_Registry extends  Enterprise_GiftRegistry_Block_Customer_Edit_Abstract
{
    /**
     * Scope Selector 'registry/registrant'
     *
     * @var string
     */
    protected $_prefix = 'registry';

    /**
     * Return array of attributes groupped by group
     *
     * @return array
     */
    public function getGroupedRegistryAttributes()
    {
        return $this->getGroupedAttributes();
    }

    /**
     * Return privacy field selector (input type = select)
     *
     * @return sting
     */
    public function getIsPublicHtml()
    {
        $options[''] = Mage::helper('enterprise_giftregistry')->__('Please Select');
        $options += $this->getEntity()->getOptionsIsPublic();
        $value = $this->getEntity()->getIsPublic();
        return $this->getSelectHtml($options, 'is_public', 'is_public', $value, 'required-entry');
    }

    /**
     * Return status field selector (input type = select)
     *
     * @return sting
     */
    public function getStatusHtml()
    {
        $options = $this->getEntity()->getOptionsStatus();
        $value = $this->getEntity()->getIsActive();
        return $this->getSelectHtml($options, 'is_active', 'is_active', $value, 'required-entry');
    }
}
