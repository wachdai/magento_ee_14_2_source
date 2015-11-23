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

namespace Enterprise\GiftCard\Test\Handler\GiftCardProduct;

use Mage\Catalog\Test\Handler\CatalogProductSimple\Curl as ProductCurl;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Create new gift card product via curl.
 */
class Curl extends ProductCurl implements GiftCardProductInterface
{
    /**
     * Prepare POST data for creating product request.
     *
     * @param FixtureInterface $fixture
     * @param string|null $prefix [optional]
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture, $prefix = null)
    {
        $data = parent::prepareData($fixture, $prefix);
        if (isset($data[$prefix]['giftcard_amounts'])) {
            foreach ($data[$prefix]['giftcard_amounts'] as $key => $amounts) {
                if (!isset($amounts['website_id'])) {
                    $data[$prefix]['giftcard_amounts'][$key]['website_id'] = 0;
                }
            }
        }
        return $data;
    }
}
