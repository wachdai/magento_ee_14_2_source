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

namespace Enterprise\Banner\Test\Block\Adminhtml\Widget\Instance\Edit;

use Magento\Mtf\Client\Element\SimpleElement as Element;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Widget Instance edit form.
 */
class WidgetForm extends \Mage\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\WidgetForm
{
    /**
     * Type of widget.
     *
     * @var array
     */
    protected $type = ['enterprise_banner/widget_banner' => 'Banner Rotator'];

    /**
     * Get data of the tabs.
     *
     * @param InjectableFixture|null $fixture
     * @param Element|null $element
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(InjectableFixture $fixture = null, Element $element = null)
    {
        $data = [];
        if (null === $fixture) {
            foreach ($this->tabs as $tabName => $tab) {
                $this->openTab($tabName);
                $tabData = $this->getTabElement($tabName)->getDataFormTab();
                $data = array_merge($data, $tabData);
            }
        } else {
            $tabsFields = $fixture->hasData() ? $this->getFieldsByTabs($fixture) : [];
            $tabsFields['frontend_properties'] = array_merge_recursive(
                $tabsFields['frontend_properties'],
                $tabsFields['settings']
            );
            unset($tabsFields['settings']);
            foreach ($tabsFields as $tabName => $fields) {
                $this->openTab($tabName);
                if (isset($fields['widgetOptions'])) {
                    unset($fields['widgetOptions']['value']['type_id']);
                    $fields['widgetOptions'] = $fields['widgetOptions']['value'];
                } elseif (isset($fields['layout'])){
                    $fields['layout'] = $fields['layout']['value'];
                }
                $tabData = $this->getTabElement($tabName)->getDataFormTab($fields, $this->_rootElement);
                $data = array_merge($data, $tabData);
            }
        }
        $data['type'] = $this->type[$data['type']];

        return $data;
    }
}
