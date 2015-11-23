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

namespace Enterprise\Banner\Test\Constraint;

use Mage\Widget\Test\Page\Adminhtml\WidgetInstanceEdit;
use Mage\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Enterprise\Banner\Test\Fixture\BannerWidget;
use Enterprise\Banner\Test\Fixture\Banner;

/**
 * Assert that displayed banner data on edit page equals passed from fixture.
 */
class AssertWidgetBannerRotatorForm extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that displayed banner data on edit page equals passed from fixture.
     *
     * @param BannerWidget $widget
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @param WidgetInstanceEdit $widgetInstanceEdit
     * @return void
     */
    public function processAssert(
        BannerWidget $widget,
        WidgetInstanceIndex $widgetInstanceIndex,
        WidgetInstanceEdit $widgetInstanceEdit
    ) {
        $widgetInstanceIndex->open();
        $widgetInstanceIndex->getWidgetGrid()->searchAndOpen(['title' => $widget->getTitle()]);
        $formData = $widgetInstanceEdit->getWidgetForm()->getData($widget);
        $fixtureData = $this->prepareData($widget->getData());
        $errors = $this->verifyData($fixtureData, $formData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Prepare data from fixture.
     *
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data)
    {
        $data['package_theme'] = str_replace(" ", "", $data['package_theme']);
        if (isset($data['widgetOptions'])) {
            $data['widgetOptions'] = $this->prepareWidgetOptions($data['widgetOptions']);
        }
        if (isset($data['layout'])) {
            $data['layout'] = $this->prepareLayout($data['layout']);
        }
        return $data;
    }

    /**
     * Prepare widget options from fixture.
     *
     * @param array $widgetOptions
     * @return array
     */
    protected function prepareWidgetOptions(array $widgetOptions)
    {
        $data = [];
        unset($widgetOptions['type_id']);
        foreach ($widgetOptions as $key => $widgetOption) {
            $data[$key] = $widgetOption;
            if (isset($widgetOption['entities'])) {
                foreach ($widgetOption['entities'] as $index => $entity) {
                    /** @var Banner $entity*/
                    $data[$key]['entities'][$index]= ['name' => $entity->getName()];
                }
            }
        }
        return $data;
    }

    /**
     * Prepare layout from fixture.
     *
     * @param array $layout
     * @return array
     */
    protected function prepareLayout(array $layout)
    {
        $data = [];
        foreach ($layout as $key => $layoutData) {
            $data[$key] = $layoutData;
            if (isset($layoutData['entities'])) {
                foreach ($layoutData['entities'] as $index => $entity) {
                    unset( $data[$key]['entities'][$index]);
                    $data[$key]['entities'][$index] = ['name' => $entity['name']];
                }
            }
        }
        return $data;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Widget\'s data on edit page equals data from fixture.';
    }
}
