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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class OnTap_Merchandiser_Block_Adminhtml_Merchandiser extends Mage_Adminhtml_Block_Catalog
{
    /**
     * _construct
     */
    public function _construct()
    {
        $session = Mage::getSingleton('core/session', array('name' => 'adminhtml'));
        $user = Mage::helper('adminhtml')->getCurrentUserId();
        $this->setUser($user)->setSession($session);
        parent::_construct();
    }

    /**
     * getCategoryId
     *
     * @return int
     */
    public function getCategoryId()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        return is_numeric($categoryId) ? (int)$categoryId : null;
    }

    /**
     * getCategory
     *
     * @return object
     */
    public function getCategory()
    {
        if ($categoryId = $this->getCategoryId()) {
            return Mage::getModel('catalog/category')->load($categoryId);
        } else {
            return null;
        }
    }

    /**
     * getColumnCount
     *
     * @return int
     */
    public function getColumnCount()
    {
        $columnCount = (int)$this->getRequest()->getParam('column_count');
        return (int)Mage::helper('merchandiser')->getColumnCount($columnCount);
    }
}
