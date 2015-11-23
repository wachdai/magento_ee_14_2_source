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
 * @package     Enterprise_Cms
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Cms Pages Hierarchy Widget Radio Block
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Hierarchy_Widget_Radio extends Mage_Adminhtml_Block_Template
{
    /**
     * Unique Hash Id
     *
     * @var null
     */
    protected $_uniqId = null;

    /**
     * Widget Parameters
     *
     * @var array
     */
    protected $_params = array();

    /**
     * All Store Views
     *
     * @var array
     */
    protected $_allStoreViews = array();

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'enterprise/cms/hierarchy/widget/radio.phtml';

    /**
     * Get all Store View labels and ids
     *
     * @return array
     */
    public function getAllStoreViews()
    {
        if (empty($this->_allStoreViews)) {
            $storeValues = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
            foreach ($storeValues as $view) {
                if (is_array($view['value']) && empty($view['value'])) {
                    continue;
                }
                if ($view['value'] == 0) {
                    $view['value'] = array(array('label' => $view['label'],'value' => $view['value']));
                }
                foreach ($view['value'] as $store) {
                    $this->_allStoreViews[] = $store;
                }
            }
        }

        return $this->_allStoreViews;
    }

    /**
     * Get array with Store View labels and ids
     *
     * @return array
     */
    public function getAllStoreViewsList()
    {
        $allStoreViews = $this->getAllStoreViews();
        reset($allStoreViews);
        $storeViews[] = current($allStoreViews);
        unset($allStoreViews);

        $storeValues = Mage::getSingleton('adminhtml/system_store')->getStoreCollection();

        foreach ($storeValues as $store) {
            $storeViews[] = array(
                'label' => $store->getName(),
                'value' => $store->getId()
            );
        }

        return $storeViews;
    }

    /**
     * Get All Store Views Ids array
     *
     * @return array
     */
    public function getAllStoreViewIds()
    {
        $ids = array();
        foreach($this->getAllStoreViews() as $view) {
            $ids[] = $view['value'];
        }

        return $ids;
    }

    /**
     * Get Unique Hash
     *
     * @return null|string
     */
    public function getUniqHash()
    {
        if ($this->getUniqId() !== null) {
            $id = explode('_', $this->getUniqId());
            if (isset($id[1])) {
                return $id[1];
            }
        }
        return null;
    }

    /**
     * Get Widget Parameters
     *
     * @return array
     */
    public function getParameters()
    {
        if (empty($this->_params)) {
            $this->_params = array();
            $widget = Mage::registry('current_widget_instance');
            $block = $this->getLayout()->getBlock('wysiwyg_widget.options');
            if ($widget) {
                $this->_params = $widget->getWidgetParameters();
            } elseif ($block) {
                $this->_params = $block->getWidgetValues();
            }
        }
        return $this->_params;
    }

    /**
     * Get Parameter Value
     *
     * @param int $key
     * @return string
     */
    public function getParamValue($key)
    {
        $params = $this->getParameters();

        return (isset($params[$key])) ? $params[$key] : '';
    }

    /**
     * Get Label Value By Node Id
     *
     * @param int $nodeId
     * @return string
     */
    public function getLabelByNodeId($nodeId)
    {
        if ($nodeId) {
            $node = Mage::getSingleton('enterprise_cms/hierarchy_node')->load($nodeId);
            if ($node->getId()) {
                return $node->getLabel();
            }
        }
        return '';
    }
}
