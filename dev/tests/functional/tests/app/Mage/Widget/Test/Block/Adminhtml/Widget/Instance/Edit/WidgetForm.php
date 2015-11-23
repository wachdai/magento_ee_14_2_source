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

namespace Mage\Widget\Test\Block\Adminhtml\Widget\Instance\Edit;

use Magento\Mtf\Client\Element\SimpleElement as Element;
use Magento\Mtf\Fixture\InjectableFixture;
use Mage\Adminhtml\Test\Block\Widget\FormTabs;

/**
 * Widget Instance edit form.
 */
class WidgetForm extends FormTabs
{
    /**
     * Fill form with tabs.
     *
     * @param InjectableFixture $fixture
     * @param Element|null $element
     * @return FormTabs
     */
    public function fill(InjectableFixture $fixture, Element $element = null)
    {
        $tabs = $this->getFieldsByTabs($fixture);
        $this->fillTabs(['settings' => $tabs['settings']]);
        unset($tabs['settings']);

        return $this->fillTabs($tabs, $element);
    }

    /**
     * Get data of the tabs.
     *
     * @param InjectableFixture|null $fixture
     * @param Element|null $element
     * @return array
     */
    public function getData(InjectableFixture $fixture = null, Element $element = null)
    {
        $widgetType = $fixture->getWidgetOptions()['type_id'];
        if ($this->hasRender($widgetType)) {
            return $this->callRender($widgetType, 'getData', ['InjectableFixture' => $fixture, 'Element' => $element]);
        } else {
            return parent::getData($fixture, $element);
        }
    }
}
