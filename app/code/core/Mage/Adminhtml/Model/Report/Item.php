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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class Mage_Adminhtml_Model_Report_Item extends Varien_Object
{
    protected $_isEmpty  = false;
    protected $_children = array();

    public function setIsEmpty($flag = true)
    {
        $this->_isEmpty = $flag;
        return $this;
    }

    public function getIsEmpty()
    {
        return $this->_isEmpty;
    }

    public function hasIsEmpty()
    {}

    public function getChildren()
    {
        return $this->_children;
    }

    public function setChildren($children)
    {
        $this->_children = $children;
        return $this;
    }

    public function hasChildren()
    {
        return (count($this->_children) > 0) ? true : false;
    }

    public function addChild($child)
    {
        $this->_children[] = $child;
        return $this;
    }
}
