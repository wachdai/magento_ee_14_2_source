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

namespace Enterprise\Banner\Test\Block\Adminhtml\Widget\Instance\Edit\Tab;

use Magento\Mtf\Client\Element\SimpleElement as Element;
use Enterprise\Banner\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetOptionsType\BannerRotator;

/**
 * Widget options form for banner widget type.
 */
class WidgetOptions extends \Mage\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetOptions
{
    /**
     * Path for widget options tab.
     *
     * @var string
     */
    protected $path = 'Enterprise\Banner\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetOptionsType\\';

    /**
     * Get data of content tab.
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array|null
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        /**@var BannerRotator $bannerRotatorForm*/
        $bannerRotatorForm = $this->getWidgetOptionsForm($this->path . 'BannerRotator');

        return $bannerRotatorForm->getDataFormTab($fields['widgetOptions'], $element);
    }
}
