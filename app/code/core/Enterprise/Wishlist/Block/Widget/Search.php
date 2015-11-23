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
 * @package     Enterprise_Wishlist
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Wishlist Search Widget Block
 *
 * @category    Enterprise
 * @package     Enterprise_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Wishlist_Block_Widget_Search extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    /**
     * Search form select options
     *
     * @var array
     */
    protected $_selectOptions;

    /**
     * Retrieve form types list
     *
     * @return array
     */
    protected function _getEnabledFormTypes()
    {
        $types = $this->_getData('types');
        if (is_array($types)) {
            return $types;
        }
        if (empty($types)) {
            $types = array();
        } else {
            $types = explode(',', $types);
        }
        $this->setData('types', $types);

        return $types;
    }

    /**
     * Check whether specified form must be available as part of quick search form
     *
     * @param string $code
     * @return bool
     */
    protected function _checkForm($code)
    {
        return in_array($code, $this->_getEnabledFormTypes());
    }

    /**
     * Check if all quick search forms must be used
     *
     * @return bool
     */
    public function useAllForms()
    {
        $code = Enterprise_Wishlist_Model_Config_Source_Search::WISHLIST_SEARCH_DISPLAY_ALL_FORMS;
        return $this->_checkForm($code);
    }

    /**
     * Check if name quick search form must be used
     *
     * @return bool
     */
    public function useNameForm()
    {
        $code = Enterprise_Wishlist_Model_Config_Source_Search::WISHLIST_SEARCH_DISPLAY_NAME_FORM;
        return $this->useAllForms() || $this->_checkForm($code);
    }

    /**
     * Check if email quick search form must be used
     *
     * @return string
     */
    public function useEmailForm()
    {
        $code = Enterprise_Wishlist_Model_Config_Source_Search::WISHLIST_SEARCH_DISPLAY_EMAIL_FORM;
        return $this->useAllForms() || $this->_checkForm($code);
    }

    /**
     * Retrieve HTML for search form select
     *
     * @return string
     */
    public function getSearchFormSelect()
    {
        $options = array_merge(array(
            array(
                'value' => '',
                'label' => Mage::helper('enterprise_wishlist')->__('Select Search Type'))
            ),
            $this->getSearchFormOptions()
        );

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('search_by')
            ->setId($this->getBlockId() . '-search_by')
            ->setOptions($options);

        return $select->getHtml();
    }

    /**
     * Add current block identifier to dom node id
     *
     * @return string
     */
    public function getBlockId()
    {
        if ($this->getData('id') === null) {
            $this->setData('id', Mage::helper('core')->uniqHash());
        }
        return $this->getData('id');
    }

    /**
     * Retrieve options for search form select
     *
     * @return array
     */
    public function getSearchFormOptions()
    {
        if (is_null($this->_selectOptions)) {
            $allForms = Mage::getSingleton('enterprise_wishlist/config_source_search')->getTypes();
            $useForms = $this->_getEnabledFormTypes();
            $codeAll = Enterprise_Wishlist_Model_Config_Source_Search::WISHLIST_SEARCH_DISPLAY_ALL_FORMS;

            if (in_array($codeAll, $useForms)) {
                unset($allForms[$codeAll]);
            } else {
                 foreach ($allForms as $type => $label) {
                     if (!in_array($type, $useForms)) {
                         unset($allForms[$type]);
                    }
                }
            }
            $options = array();
            foreach ($allForms as $type => $label) {
                $options[] = array(
                    'value' => $type,
                    'label' => $label
                );
            }
            $this->_selectOptions = $options;
        }
        return $this->_selectOptions;
    }

    /**
     * Use search form select in quick search form
     *
     * @return bool
     */
    public function useSearchFormSelect()
    {
        return count($this->getSearchFormOptions()) > 1;
    }

    /**
     * Retrieve Multiple Search URL
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('wishlist/search/results');
    }
}
