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
 * @package     Enterprise_Banner
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Banner Permission Monitor block
 *
 * Removes certain blocks from layout if user do not have required permissions
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Banner_Block_Adminhtml_Permission_Monitor extends Mage_Adminhtml_Block_Template
{
    /**
     * Preparing layout
     *
     * @return Enterprise_Banner_Block_Adminhtml_Permission_Monitor
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();

        if (!Mage::getSingleton('admin/session')->isAllowed('cms/enterprise_banner')) {
            /** @var $layout Mage_Core_Model_Layout */
            $layout = $this->getLayout();
            if ($layout->getBlock('salesrule.related.banners') !== false) {
                /** @var $promoQouteEditTabsBlock Mage_Adminhtml_Block_Widget_Tabs */
                $promoQuoteEditTabsBlock = $layout->getBlock('promo_quote_edit_tabs');
                if ($promoQuoteEditTabsBlock !== false) {
                    $promoQuoteEditTabsBlock->removeTab('banners_section');
                    $layout->unsetBlock('salesrule.related.banners');
                }
            } elseif ($layout->getBlock('catalogrule.related.banners') !== false) {
                /** @var $promoCatalogEditTabsBlock Mage_Adminhtml_Block_Widget_Tabs */
                $promoCatalogEditTabsBlock = $layout->getBlock('promo_catalog_edit_tabs');
                if ($promoCatalogEditTabsBlock !== false) {
                    $promoCatalogEditTabsBlock->removeTab('banners_section');
                    $layout->unsetBlock('catalogrule.related.banners');
                }
            }
        }
        return $this;
    }
}
