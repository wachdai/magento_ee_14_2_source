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
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Used as singleton for saving prepared layout
 */
class Enterprise_PageCache_Model_Container_Layout_Storage
{

    /**
     * @var array
     */
    protected $_layouts = array();

    /**
     * Store layout by handler as key
     *
     * @param Mage_Core_Model_Layout $layout
     * @param string $handler
     */
    public function addLayout($layout, $handler)
    {
        $this->_layouts[$handler] = $layout;
    }

    /**
     * Get Layout by handler
     *
     * @param string $handler
     * @return Mage_Core_Model_Layout|false
     */
    public function getLayout($handler)
    {
        return isset($this->_layouts[$handler]) ? $this->_layouts[$handler] : false;
    }
}
