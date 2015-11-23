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
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


/**
 * Reward resource setup model
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Resource_Setup extends Mage_Sales_Model_Resource_Setup
{
    /**
     * Current entity type id
     *
     * @var string
     */
    protected $_currentEntityTypeId;

    /**
     * Add attribute to an entity type
     * If attribute is system will add to all existing attribute sets
     *
     * @param string|integer $entityTypeId
     * @param string $code
     * @param array $attr
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function addAttribute($entityTypeId, $code, array $attr)
    {
        $this->_currentEntityTypeId = $entityTypeId;
        return parent::addAttribute($entityTypeId, $code, $attr);
    }

    /**
     * Prepare attribute values to save
     *
     * @param array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        if ($this->_currentEntityTypeId == 'customer') {
            $data = array_merge($data, array(
                'is_visible'                => $this->_getValue($attr, 'visible', 1),
                'is_visible_on_front'       => $this->_getValue($attr, 'visible_on_front', 0),
                'input_filter'              => $this->_getValue($attr, 'input_filter', ''),
                'lines_to_divide_multiline' => $this->_getValue($attr, 'lines_to_divide', 0),
                'min_text_length'           => $this->_getValue($attr, 'min_text_length', 0),
                'max_text_length'           => $this->_getValue($attr, 'max_text_length', 0)
            ));
        }
        return $data;
    }
}
