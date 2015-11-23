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

namespace Enterprise\GiftCardAccount\Test\Block\Adminhtml\Sales\Order\Create;

use Magento\Mtf\Block\Form;
use Mage\Adminhtml\Test\Block\Template;
use Enterprise\GiftCardAccount\Test\Fixture\GiftCardAccount;
use Magento\Mtf\Client\Locator;

/**
 * Sales order gift cards form on backend.
 */
class GiftCards extends Form
{
    /**
     * Add gift card account button selector.
     *
     * @var string
     */
    protected $addGiftCardButton = '[onclick="applyGiftCard()"]';

    /**
     * Backend abstract block.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Apply gift card account.
     *
     * @param GiftCardAccount $giftCardAccount
     * @return void
     */
    public function applyGiftCardAccount(GiftCardAccount $giftCardAccount)
    {
        parent::fill($giftCardAccount);
        $this->_rootElement->find($this->addGiftCardButton)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get backend abstract block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Mage\Adminhtml\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
