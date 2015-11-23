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

namespace Enterprise\CustomerBalance\Test\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Mtf\Client\Element\SimpleElement as Element;
use Magento\Mtf\Client\Locator;
use Mage\Adminhtml\Test\Block\Template;

/**
 * Store credit tab.
 */
class Tab extends \Mage\Adminhtml\Test\Block\Widget\Tab
{
    /**
     * Backend abstract block selector.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    // @codingStandardsIgnoreStart
    /**
     * Store credit balance XPath.
     *
     * @var string
     */
    protected $storeCreditBalance = '//*[contains(@id,"customerbalance_content")]//*[@id="balanceGrid"]//td[contains(.,"%s")]';
    // @codingStandardsIgnoreEnd

    /**
     * Field set.
     *
     * @var string
     */
    protected $fieldSetStoreCredit = '#_customerbalancestorecreidt_fieldset';

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        $this->waitForElementVisible($this->fieldSetStoreCredit);
        $data = $this->dataMapping($fields);
        $this->_fill($data, $element);

        return $this;
    }

    /**
     * Check store credit balance history.
     *
     * @param string $value
     * @return bool
     */
    public function isStoreCreditBalanceVisible($value)
    {
        $this->getTemplateBlock()->waitLoader();
        return $this->_rootElement
            ->find(sprintf($this->storeCreditBalance, $value), Locator::SELECTOR_XPATH)
            ->isVisible();
    }

    /**
     * Get backend abstract block.
     *
     * @return Template
     */
    protected function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Mage\Adminhtml\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
