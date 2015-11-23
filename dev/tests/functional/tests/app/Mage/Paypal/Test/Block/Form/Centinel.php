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

namespace Mage\Paypal\Test\Block\Form;

use Mage\Payment\Test\Fixture\Cc;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;

/**
 * 3d Secure verification frame block.
 */
class Centinel extends Form
{
    /**
     * 3D secure iFrame locator.
     *
     * @var string
     */
    protected $centinel = "#centinel_authenticate_iframe";

    /**
     * Centinel submit button selector.
     *
     * @var string
     */
    protected $sentinelSubmit = '[name="UsernamePasswordEntry"]';

    /**
     * Body selector.
     *
     * @var string
     */
    protected $body = 'body';

    /**
     * Submit 3d secure verification code.
     *
     * @return void
     */
    public function submitCode()
    {
        $this->browser->find($this->sentinelSubmit)->click();
        $this->browser->acceptAlert();
        $this->waitForElementNotVisible($this->centinel);
        $this->browser->selectWindow();
    }

    /**
     * Fill credit card.
     *
     * @param Cc $creditCard
     * @return void
     */
    public function fill(Cc $creditCard)
    {
        $this->browser->switchToFrame(new Locator($this->centinel));
        $element = $this->getRootElement();
        parent::fill($creditCard, $element);
    }

    /**
     * Get root element.
     *
     * @return ElementInterface
     */
    protected function getRootElement()
    {
        return $this->browser->find($this->body);
    }
}
