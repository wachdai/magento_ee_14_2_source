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
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Enterprise\Banner\Test\Block\Adminhtml\Banner\Edit\Tab;

use Mage\Adminhtml\Test\Block\Widget\Tab;
use Enterprise\Banner\Test\Block\Adminhtml\Promo\CartPriceRulesGrid;
use Enterprise\Banner\Test\Block\Adminhtml\Promo\CatalogPriceRulesGrid;

/**
 * Banner related promotions per store view edit page.
 */
class RelatedPromotions extends Tab
{
    /**
     * Locator for Sales Rule Grid.
     *
     * @var string
     */
    protected $salesRuleGrid = '#related_salesrule_grid';

    /**
     * Locator for Catalog Rule Grid.
     *
     * @var string
     */
    protected $catalogRuleGrid = '#related_catalogrule_grid';

    /**
     * Get Cart Price Rules grid on the Banner New page.
     *
     * @return CartPriceRulesGrid
     */
    public function getCartPriceRulesGrid()
    {
        return $this->blockFactory->create(
            'Enterprise\Banner\Test\Block\Adminhtml\Promo\CartPriceRulesGrid',
            [
                'element' => $this->_rootElement->find($this->salesRuleGrid)
            ]
        );
    }

    /**
     * Get Catalog Price Rules grid on the Banner New page.
     *
     * @return CatalogPriceRulesGrid
     */
    public function getCatalogPriceRulesGrid()
    {
        return $this->blockFactory->create(
            'Enterprise\Banner\Test\Block\Adminhtml\Promo\CatalogPriceRulesGrid',
            [
                'element' => $this->_rootElement->find($this->catalogRuleGrid)
            ]
        );
    }
}
