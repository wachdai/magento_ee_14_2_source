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
 * CMS Hierarchy data helper
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Helper_Hierarchy extends Mage_Core_Helper_Abstract
{
    const XML_PATH_HIERARCHY_ENABLED    = 'cms/hierarchy/enabled';
    const XML_PATH_METADATA_ENABLED     = 'cms/hierarchy/metadata_enabled';

    const METADATA_VISIBILITY_PARENT    = '0';
    const METADATA_VISIBILITY_YES       = '1';
    const METADATA_VISIBILITY_NO        = '2';

    const SCOPE_PREFIX_STORE            = 'store_';
    const SCOPE_PREFIX_WEBSITE          = 'website_';

    /**
     * Check is Enabled Hierarchy Functionality
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_HIERARCHY_ENABLED);
    }

    /**
     * Check is Enabled Hierarchy Metadata
     *
     * @return bool
     */
    public function isMetadataEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_METADATA_ENABLED);
    }

    /**
     * Retrieve metadata fields
     *
     * @return array
     */
    public function getMetadataFields()
    {
        return array(
            'meta_first_last',
            'meta_next_previous',
            'meta_chapter',
            'meta_section',
            'meta_cs_enabled',
            'pager_visibility',
            'pager_frame',
            'pager_jump',
            'menu_visibility',
            'menu_layout',
            'menu_brief',
            'menu_excluded',
            'menu_levels_down',
            'menu_ordered',
            'menu_list_type',
            'top_menu_visibility',
            'top_menu_excluded'
        );
    }

    /**
     * Copy meta data from source array to target
     *
     * @param array $source
     * @param array $target
     * @return array
     */
    public function copyMetaData($source, $target)
    {
        if (!is_array($source)) {
            return $target;
        }

        if (isset($source['pager_visibility'])) {
            $default = $this->_getDefaultMetadataValues('pager_visibility', $source['pager_visibility']);
            if (is_array($default)) {
                $source = array_merge($source, $default);
            }
        }

        if (isset($source['menu_visibility'])) {
            $default = $this->_getDefaultMetadataValues('menu_visibility', $source['menu_visibility']);
            if (is_array($default)) {
                $source = array_merge($source, $default);
            }
        }

        if ($this->isMetadataEnabled()) {
            $fields = $this->getMetadataFields();
            foreach ($fields as $element) {
                if (array_key_exists($element, $source)) {
                    $target[$element] = $source[$element];
                }
            }
        } else {
            $target = $this->_forcedCopyMetaData($source, $target);
        }

        return $target;
    }


    /**
     * Copy metadata fields that don't depend on isMetadataEnabled
     *
     * @param array $source
     * @param array $target
     * @return array
     */
    protected function _forcedCopyMetaData($source, $target)
    {
        if (!is_array($source)) {
            return $target;
        }
        $forced = array(
            'pager_visibility',
            'pager_frame',
            'pager_jump',
            'menu_visibility',
            'menu_layout',
            'menu_brief',
            'menu_excluded',
            'menu_levels_down',
            'menu_ordered',
            'menu_list_type',
        );
        foreach ($forced as $element) {
            if (array_key_exists($element, $source)) {
                $target[$element] = $source[$element];
            }
        }
        return $target;
    }

    /**
     * Return default values for metadata fields based on other field values
     * Ex: if 'pager_visibility' == '0' then set to zeros pagination params
     *
     * @param string $field Field name to search for
     * @param string $value Field value
     * @return array|null
     */
    protected function _getDefaultMetadataValues($field, $value)
    {
        $paginationDefault = array(
            'pager_frame' => '0',
            'pager_jump' => '0',
        );

        $menuDefault = array(
            'menu_levels_down' => '0',
            'menu_brief' => '0',
            'menu_layout' => '',
            'menu_ordered' => '0',
            'menu_list_type' => '',
        );

        $default = array('pager_visibility' => array(self::METADATA_VISIBILITY_PARENT => $paginationDefault,
                                                     self::METADATA_VISIBILITY_NO => $paginationDefault),

                         'menu_visibility' => array('0' => $menuDefault));

        return isset($default[$field][$value]) ? $default[$field][$value] : null;
    }

    /**
     * Get parent scope and scopeId
     *
     * @param string $scope
     * @param int $scopeId
     * @return array|null
     */
    public function getParentScope($scope, $scopeId)
    {
        if ($scope === Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_STORE) {
            return array(
                Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_WEBSITE,
                Mage::app()->getStore($scopeId)->getWebsiteId(),
            );
        } elseif ($scope === Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_WEBSITE) {
            return array(
                Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_DEFAULT,
                Enterprise_Cms_Model_Hierarchy_Node::NODE_SCOPE_DEFAULT_ID,
            );
        }

        return null;
    }
}
