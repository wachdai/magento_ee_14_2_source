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

namespace Enterprise\GiftCardAccount\Test\TestStep;

use Enterprise\GiftCardAccount\Test\Fixture\GiftCardAccount;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Creating gift card account.
 */
class CreateGiftCardAccountStep implements TestStepInterface
{
    /**
     * Gift card account name in data set.
     *
     * @var string
     */
    protected $giftCardAccount;

    /**
     * Factory for Fixture.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $giftCardAccount [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, $giftCardAccount = null)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->giftCardAccount = $giftCardAccount;
    }

    /**
     * Runs create gift card account step.
     *
     * @return array
     */
    public function run()
    {
        return ['giftCardAccount' => is_null($this->giftCardAccount) ? null : $this->createGiftCardAccount()];
    }

    /**
     * Create gift card account.
     *
     * @return GiftCardAccount
     */
    protected function createGiftCardAccount()
    {
        $giftCardAccount = $this->fixtureFactory->createByCode(
            'giftCardAccount',
            ['dataSet' => $this->giftCardAccount]
        );
        $giftCardAccount->persist();

        return $giftCardAccount;
    }
}
