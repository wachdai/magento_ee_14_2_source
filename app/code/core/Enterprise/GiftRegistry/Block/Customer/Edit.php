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
 * @package     Enterprise_GiftRegistry
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer giftregistry list block
 *
 * @category   Enterprise
 * @package    Enterprise_GiftRegistry
 */
class Enterprise_GiftRegistry_Block_Customer_Edit extends Mage_Directory_Block_Data
{
    /**
     * Template container
     *
     * @var array
     */
    protected $_inputTemplates = array();

    /**
     * Return edit form header
     *
     * @return string
     */
    public function getFormHeader()
    {
        if (Mage::registry('enterprise_giftregistry_entity')->getId()) {
            return Mage::helper('enterprise_giftregistry')->__('Edit Gift Registry');
        } else {
            return Mage::helper('enterprise_giftregistry')->__('Create Gift Registry');
        }
    }

    /**
     * Getter for post data, stored in session
     *
     * @return array|null
     */
    public function getFormDataPost()
    {
        return Mage::getSingleton('customer/session')->getGiftRegistryEntityFormData(true);
    }

    /**
     * Get array of reordered custom registry attributes
     *
     * @return array
     */
    public function getGroupedRegistryAttributes()
    {
        $attributes = $this->getEntity()->getCustomAttributes();
        return empty($attributes['registry']) ? array() : $this->_groupAttributes($attributes['registry']);
    }

    /**
     * Get array of reordered custom registrant attributes
     *
     * @return array
     */
    public function getGroupedRegistrantAttributes()
    {
        $attributes = $this->getEntity()->getCustomAttributes();
        return empty($attributes['registrant']) ? array() : $this->_groupAttributes($attributes['registrant']);
    }

    /**
     * Fetches type list array
     *
     * @return array
     */
    public function getTypeList()
    {
        $storeId = Mage::app()->getStore()->getId();
        $collection = Mage::getModel('enterprise_giftregistry/type')
            ->getCollection()
            ->addStoreData($storeId)
            ->applyListedFilter()
            ->applySortOrder();
        $list = $collection->toOptionArray();
        return $list;
    }

    /**
     * Return "create giftregistry" form Add url
     *
     * @return string
     */
    public function getAddActionUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/edit', array('_secure' => $this->_isSecure()));
    }

    /**
     * Returns "edit giftregistry" form action Url
     *
     * @return string
     */
    public function getEditPostActionUrl()
    {
        return $this->getUrl('*/*/editPost', array('_secure' => $this->_isSecure()));
    }

    /**
     * Return form back link url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('giftregistry');
    }

    /**
     * Return "create giftregistry" form AddPost url
     *
     * @return string
     */
    public function getAddPostActionUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/addPost', array('_secure' => $this->_isSecure()));
    }

    /**
     * Return "create giftregistry" form url
     *
     * @return string
     */
    public function getAddGiftRegistryUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/addselect');
    }

    /**
     * Return "create giftregistry" form url
     *
     * @return string
     */
    public function getSaveActionUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/save');
    }

    /**
     * Setup template from template file as $_inputTemplates['type'] for specified type
     *
     * @param string $type
     * @param string $template
     * @return Enterprise_GiftRegistry_Block_Customer_Edit
     */
    public function addInputTypeTemplate($type, $template)
    {
        $params = array('_relative'=>true);
        $area = $this->getArea();
        if ($area) {
            $params['_area'] = $area;
        }
        $templateName = Mage::getDesign()->getTemplateFilename($template, $params);

        $this->_inputTemplates[$type] = $templateName;
        return $this;
    }

    /**
     * Return presetted template by type
     * @param string $type
     * @return string
     */
    public function getInputTypeTemplate($type)
    {
        if (isset($this->_inputTemplates[$type])) {
            return $this->_inputTemplates[$type];
        }
        return false;
    }
}
