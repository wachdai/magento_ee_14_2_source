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

namespace Mage\Adminhtml\Test\Block\Catalog\Product\Edit\Tab;

use Magento\Mtf\Client\Element\SimpleElement as Element;
use Mage\Adminhtml\Test\Block\Widget\Tab;

/**
 * Websites Tab.
 */
class Websites extends Tab
{
    /**
     * Tab selector.
     *
     * @var string
     */
    protected $tabSelector = '#product_info_tabs_websites';

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        $context = $element ? $element : $this->_rootElement;
        $mapping = $this->dataMapping(['website' => 'Yes']);
        $data = [];
        foreach ($fields as $key => $website) {
            $data[$key] = $mapping['website'];
            $data[$key]['selector'] = sprintf($mapping['website']['selector'], $website);
        }
        $this->_fill($data, $context);

        return $this;
    }

    /**
     * Check if the tab is visible or not.
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->_rootElement->find($this->tabSelector)->isVisible();
    }
}
