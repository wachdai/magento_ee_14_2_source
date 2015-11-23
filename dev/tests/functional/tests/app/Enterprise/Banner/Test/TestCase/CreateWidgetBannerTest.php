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

namespace Enterprise\Banner\Test\TestCase;

use Enterprise\Banner\Test\Fixture\BannerWidget;
use Mage\Widget\Test\TestCase\AbstractCreateWidgetEntityTest;

/**
 * Steps:
 * 1. Login to the backend.
 * 2. Open CMS -> Widgets.
 * 3. Click 'Add new Widget Instance' button.
 * 4. Fill settings data for Banner widget type according dataset.
 * 5. Click button Continue.
 * 6. Fill widget data according dataset.
 * 7. Perform all assertions.
 *
 * @group Widget_(PS)
 * @ZephyrId MPERF-6901
 */
class CreateWidgetBannerTest extends AbstractCreateWidgetEntityTest
{
    /**
     * Widget fixture.
     *
     * @var BannerWidget
     */
    protected $widget;

    /**
     * Creation for New Instance of WidgetEntity.
     *
     * @param BannerWidget $widget
     * @return void
     */
    public function test(BannerWidget $widget)
    {
        // Steps:
        $this->widget = $widget;
        $this->widgetInstanceIndex->open();
        $this->widgetInstanceIndex->getPageActionsBlock()->addNew();
        $this->widgetInstanceNew->getWidgetForm()->fill($widget);
        $this->widgetInstanceEdit->getPageActionsBlock()->save();
    }

    /**
     * Removing widget, catalog rules and sales rules.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->widget !== null) {
            $this->objectManager->create('Mage\Widget\Test\TestStep\DeleteAllWidgetsStep')->run();
            if ($this->widget->getWidgetOptions()[0]['entities'][0]->hasData('banner_catalog_rules')) {
                $this->objectManager->create('Mage\CatalogRule\Test\TestStep\DeleteAllCatalogRulesStep')->run();
            }
            if ($this->widget->getWidgetOptions()[0]['entities'][0]->hasData('banner_sales_rules')) {
                $this->objectManager->create('Mage\SalesRule\Test\TestStep\DeleteAllSalesRuleStep')->run();
            }
        }
    }
}
