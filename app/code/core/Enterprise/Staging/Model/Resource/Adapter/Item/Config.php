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
 * @package     Enterprise_Staging
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Enter description here ...
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Model_Resource_Adapter_Item_Config
    extends Enterprise_Staging_Model_Resource_Adapter_Item_Default
{
    /**
     * Prepare simple select by given parameters
     *
     * @param mixed $table
     * @param string $fields
     * @param array | string $where
     * @return string
     */
    protected function _getSimpleSelect($table, $fields, $where = null)
    {
        $_where = array();
        if (!is_null($where)) {
            if (is_array($where)) {
                $_where = $where;
            } else {
                $_where[] = $where;
            }
        }

        $likeOptions = array('position' => 'any');
        if ($this->getEvent()->getCode() !== 'rollback') {
            $itemXmlConfig = $this->getConfig();
            if ($itemXmlConfig->ignore_nodes) {
                foreach ($itemXmlConfig->ignore_nodes->children() as $node) {
                    $path = (string) $node->path;
                    /* $helper Mage_Core_Model_Resource_Helper_Abstract */
                    $helper = Mage::getResourceHelper('core');
                    $_where[] = 'path NOT LIKE ' . $helper->addLikeEscape($path, $likeOptions);
                }
            }
        }

        $select = parent::_getSimpleSelect($table, $fields, $_where);

        return $select;
    }
}
