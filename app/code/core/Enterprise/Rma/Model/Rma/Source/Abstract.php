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
 * @package     Enterprise_Rma
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * RMA Item attribute source abstract model
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Enterprise_Rma_Model_Rma_Source_Abstract extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    /**
     * Getter for all available options
     *
     * @param bool $withLabels
     * @return array
     */
    public function getAllOptions($withLabels = true)
    {
        $values = $this->_getAvailableValues();
        if ($withLabels) {
            $result = array();
            foreach ($values as $item) {
                $result[] = array(
                    'label' => $this->getItemLabel($item),
                    'value' => $item
                );
            }
            return $result;

        }
        return $values;
    }

    /**
     * Getter for all available options for filter in grid
     *
     * @return array
     */
    public function getAllOptionsForGrid()
    {
        $values = $this->_getAvailableValues();
        $result = array();
        foreach ($values as $item) {
            $result[$item] = $this->getItemLabel($item);
        }
        return $result;
    }

    /**
     * Get available keys for entities
     *
     * @return array
     */
    abstract protected function _getAvailableValues();

    /**
     * Get label based on the code
     *
     * @param string $item
     * @return string
     */
    abstract public function getItemLabel($item);
}
