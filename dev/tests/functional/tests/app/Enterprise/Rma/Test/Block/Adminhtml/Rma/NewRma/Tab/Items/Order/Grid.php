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

namespace Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab\Items\Order;

use Mage\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Grid for choose order item.
 */
class Grid extends \Mage\Adminhtml\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name=product_name]',
        ],
    ];

    /**
     * Select order item.
     *
     * @param FixtureInterface $product
     * @reutrn void
     */
    public function selectItem(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $productConfig = $product->getDataConfig();
        $productType = isset($productConfig['type_id']) ? ucfirst($productConfig['type_id']) : '';
        $productGridClass = 'Enterprise\Rma\Test\Block\Adminhtml\Rma\NewRma\Tab\Items\Order\\' . $productType . 'Grid';

        if (class_exists($productGridClass)) {
            $productGrid = $this->blockFactory->create($productGridClass, ['element' => $this->_rootElement]);
            $productGrid->selectItem($product);
        } else {
            $this->searchAndSelect(['name' => $product->getName()]);
        }
    }
}
