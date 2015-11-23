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

namespace Enterprise\GiftCard\Test\Block\Catalog\Product;

use Enterprise\GiftCard\Test\Block\Catalog\Product\View\Type\GiftCard;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Product view block on the gift card product page.
 */
class View extends \Mage\Catalog\Test\Block\Product\View
{
    /**
     * Gift card stock availability control.
     *
     * @var string
     */
    protected $giftCardStockAvailability = '.availability span';

    /**
     * Get text of Stock Availability control.
     *
     * @return string
     */
    public function getGiftcardStockAvailability()
    {
        return strtolower($this->_rootElement->find($this->giftCardStockAvailability)->getText());
    }

    /**
     * Get Gift Card form.
     *
     * @return GiftCard
     */
    public function getGiftCardForm()
    {
        return $this->blockFactory->create(
            'Enterprise\GiftCard\Test\Block\Catalog\Product\View\Type\GiftCard',
            ['element' => $this->_rootElement]
        );
    }

    /**
     * Add product to shopping cart.
     *
     * @param InjectableFixture $product
     * @return void
     */
    public function fillOptions(InjectableFixture $product)
    {
        $this->getGiftCardForm()->fill($product);
        parent::fillOptions($product);
    }
}
