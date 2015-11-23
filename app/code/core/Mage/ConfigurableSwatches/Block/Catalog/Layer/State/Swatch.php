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
 * @category    Mage
 * @package     Mage_ConfigurableSwatches
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class Mage_ConfigurableSwatches_Block_Catalog_Layer_State_Swatch extends Mage_Core_Block_Template
{
    protected $_initDone = false;

    /**
     * Determine if we should use this block to render a state filter
     *
     * @param Mage_Catalog_Model_Layer_Filter_Item $filter
     * @return bool
     */
    public function shouldRender($filter)
    {
        $helper = Mage::helper('configurableswatches');
        if ($helper->isEnabled() && $filter->getFilter()->hasAttributeModel()) {
            if ($helper->attrIsSwatchType($filter->getFilter()->getAttributeModel())) {
                $this->_init($filter);
                if ($this->getSwatchUrl()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set one-time data on the renderer
     *
     * @param Mage_Catalog_Model_Layer_Filter_Item $filter
     */
    protected function _init($filter)
    {
        if (!$this->_initDone) {
            $dimHelper = Mage::helper('configurableswatches/swatchdimensions');
            $this->setSwatchInnerWidth(
                $dimHelper->getInnerWidth(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_LAYER));
            $this->setSwatchInnerHeight(
                $dimHelper->getInnerHeight(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_LAYER));
            $this->setSwatchOuterWidth(
                $dimHelper->getOuterWidth(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_LAYER));
            $this->setSwatchOuterHeight(
                $dimHelper->getOuterHeight(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_LAYER));

            $swatchUrl = Mage::helper('configurableswatches/productimg')
                ->getGlobalSwatchUrl(
                    $filter,
                    $this->stripTags($filter->getLabel()),
                    $this->getSwatchInnerWidth(),
                    $this->getSwatchInnerHeight()
                );
            $this->setSwatchUrl($swatchUrl);

            $this->_initDone = true;
        }
    }
}
