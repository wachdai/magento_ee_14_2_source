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
 * Store switcher block
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Block_Adminhtml_Scope_Switcher extends Mage_Adminhtml_Block_System_Config_Switcher
{
    /**
     * Scope switcher options
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Get scope switcher options
     *
     * @return array
     */
    public function getStoreSelectOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = parent::getStoreSelectOptions();
            $this->_options['default']['label'] = Mage::helper('enterprise_cms')->__('All Store Views');
        }

        return $this->_options;
    }

    /**
     * Get switcher default option value
     *
     * @return string
     */
    public function getDefaultValue()
    {
        foreach ($this->getStoreSelectOptions() as $value => $option) {
            if (array_key_exists('selected', $option) && $option['selected']) {
                return $value;
            }
        }

        return '';
    }
}
