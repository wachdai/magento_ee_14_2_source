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

namespace Mage\Widget\Test\Block;

use Mage\Widget\Test\Fixture\Widget;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Widget block on the frontend.
 */
class WidgetView extends Block
{
    /**
     * Widgets selectors.
     *
     * @var array
     */
    protected $widgetSelectors = [];

    /**
     * Check is visible widget selector.
     *
     * @param Widget $widget
     * @param string $pageName
     * @return array
     * @throws \Exception
     */
    public function isWidgetVisible(Widget $widget, $pageName)
    {
        $error = [];
        $widgetType = $widget->getWidgetOptions()['type_id'];
        if ($this->hasRender($widgetType)) {
            return $this->callRender($widgetType, 'isWidgetVisible', ['widget' => $widget, 'pageName' => $pageName]);
        } else {
            if (isset($this->widgetSelectors[$widgetType])) {
                $widgetOptions = $widget->getWidgetOptions();
                unset($widgetOptions['type_id']);
                foreach ($widgetOptions as $widgetOption) {
                    foreach ($widgetOption['entities'] as $entity) {
                        $widgetText = $entity->getStoreContents()['store_content'];
                        $isWidgetVisible = $this->_rootElement->find(
                            sprintf($this->widgetSelectors[$widgetType], $widgetText),
                            Locator::SELECTOR_XPATH
                        )->isVisible();
                        if (!$isWidgetVisible) {
                            $error[] = "Widget with title {$widget->getTitle()} is absent on {$pageName}  page.";
                        }
                    }
                }
                return $error;
            } else {
                throw new \Exception('Determine how to find the widget on the page.');
            }
        }
    }

    /**
     * Click to widget selector.
     *
     * @param Widget $widget
     * @param string $widgetText
     * @return void
     * @throws \Exception
     */
    public function clickToWidget(Widget $widget, $widgetText)
    {
        $widgetType = $widget->getWidgetOptions()['type_id'];
        if ($this->hasRender($widgetType)) {
            $this->callRender($widgetType, 'clickToWidget', ['widget' => $widget, 'widgetText' => $widgetText]);
        } else {
            if (isset($this->widgetSelectors[$widgetType])) {
                $this->_rootElement->find(
                    sprintf($this->widgetSelectors[$widgetType], $widgetText),
                    Locator::SELECTOR_XPATH
                )->click();
            } else {
                throw new \Exception('Determine how to find the widget on the page.');
            }
        }
    }
}
