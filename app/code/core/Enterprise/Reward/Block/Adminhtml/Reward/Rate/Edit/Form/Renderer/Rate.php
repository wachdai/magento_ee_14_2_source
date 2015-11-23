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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reward rate form field (element) renderer
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Block_Adminhtml_Reward_Rate_Edit_Form_Renderer_Rate
    extends Mage_Adminhtml_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->setTemplate('enterprise/reward/rate/form/renderer/rate.phtml');
    }

    /**
     * Return HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Getter
     * Return value index in element object
     *
     * @return string
     */
    public function getValueIndex()
    {
        return $this->getElement()->getValueIndex();
    }

    /**
     * Getter
     * Return value by given value index in element object
     *
     * @return float | integer
     */
    public function getValue()
    {
        return $this->getRate()->getData($this->getValueIndex());
    }

    /**
     * Getter
     * Return equal value index in element object
     *
     * @return string
     */
    public function getEqualValueIndex()
    {
        return $this->getElement()->getEqualValueIndex();
    }

    /**
     * Return value by given equal value index in element object
     *
     * @return float | integer
     */
    public function getEqualValue()
    {
        return $this->getRate()->getData($this->getEqualValueIndex());
    }
}
