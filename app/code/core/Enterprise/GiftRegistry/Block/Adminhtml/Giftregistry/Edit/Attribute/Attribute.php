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

class Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Edit_Attribute_Attribute
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Instance of gift registry type model
     */
    protected $_typeInstance;

    /**
     * Initialize edit attribute block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/giftregistry/edit/attributes.phtml');
    }

    /**
     * Preparing block layout
     *
     * @return Enterprise_GiftRegistry_Block_Adminhtml_Giftregistry_Edit_Tab_Registry
     */
    protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('enterprise_giftregistry')->__('Add Attribute'),
                    'class' => 'add',
                    'id'    => $this->getFieldPrefix() . '_add_new_attribute'
                ))
        );

        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('enterprise_giftregistry')->__('Delete Attribute'),
                    'class' => 'delete delete-attribute-option'
                ))
        );
        return parent::_prepareLayout();
    }

    /**
     * Retrieve add button html
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        if (!$this->getTypeStoreId()) {
            return $this->getChildHtml('add_button');
        }
    }

    /**
     * Retrieve id of add button
     *
     * @return int
     */
    public function getAddButtonId()
    {
        return $this->getChild('add_button')->getId();
    }

    /**
     * Retrieve delete button html
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        if (!$this->getTypeStoreId()) {
            return $this->getChildHtml('delete_button');
        }
    }

    /**
     * Get gift registry attribute config model
     *
     * @return Enterprise_GiftRegistry_Model_Attribute_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('enterprise_giftregistry/attribute_config');
    }

    /**
     * Get gift registry attribute type model
     *
     * @return Enterprise_GiftRegistry_Model_Type
     */
    public function getType()
    {
        if (!$this->_typeInstance) {
            if ($type = Mage::registry('current_giftregistry_type')) {
                $this->_typeInstance = $type;
            } else {
                $this->_typeInstance = Mage::getSingleton('enterprise_giftregistry/type');
            }
        }
        return $this->_typeInstance;
    }

    /**
     * Retrieve store id of current registry type model
     *
     * @return int
     */
    public function getTypeStoreId()
    {
        return $this->getType()->getStoreId();
    }

    /**
     * Select element for choosing attribute type
     *
     * @return string
     */
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id'    => $this->getFieldPrefix() . '_attribute_{{id}}_type',
                'class' => 'select required-entry attribute-type global-scope'
            ))
            ->setName('attributes[' . $this->getFieldPrefix() . '][{{id}}][type]')
            ->setOptions($this->getConfig()->getAttributeTypesOptions());

        return $select->getHtml();
    }

    /**
     * Select element for choosing attribute group
     *
     * @return string
     */
    public function getGroupSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id'    => $this->getFieldPrefix() . '_attribute_{{id}}_group',
                'class' => 'select required-entry global-scope'
            ))
            ->setName('attributes[' . $this->getFieldPrefix() . '][{{id}}][group]')
            ->setOptions($this->getConfig()->getAttributeGroupsOptions());

        return $select->getHtml();
    }

    /**
     * Select element for choosing searcheable option
     *
     * @return string
     */
    public function getSearcheableSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                 'id'    => $this->getFieldPrefix() . '_attribute_{{id}}_is_searcheable',
                 'class' => 'select required-entry global-scope'
            ))
            ->setName('attributes[' . $this->getFieldPrefix() . '][{{id}}][frontend][is_searcheable]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

    /**
     * Select element for choosing listed option
     *
     * @return string
     */
    public function getListedSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                 'id'    => $this->getFieldPrefix() . '_attribute_{{id}}_is_listed',
                 'class' => 'select required-entry global-scope'
            ))
            ->setName('attributes[' . $this->getFieldPrefix() . '][{{id}}][frontend][is_listed]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

    /**
     * Select element for choosing required option
     *
     * @return string
     */
    public function getRequiredSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                 'id'    => $this->getFieldPrefix() . '_attribute_{{id}}_is_required',
                 'class' => 'select required-entry global-scope'
            ))
            ->setName('attributes[' . $this->getFieldPrefix() . '][{{id}}][frontend][is_required]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

    /**
     * Prepare and return html of available attribute renderers
     *
     * @return string
     */
    public function getTemplatesHtml()
    {
        $templates = array();
        $types = array('select', 'date', 'country');

        foreach ($types as $type) {
            $renderer = 'enterprise_giftregistry/adminhtml_giftregistry_edit_attribute_type_' . $type;
            $block = $this->getLayout()->createBlock($renderer)->setFieldPrefix($this->getFieldPrefix());
            $templates[] = $block->toHtml();
        }
        return implode("\n", $templates);
    }

    /**
     * Prepare and return attribute values
     *
     * @return array
     */
    public function getAttributeValues()
    {
        $values = array();
        $attributes = array();
        $groups = $this->getType()->getAttributes();
        $innerId = 0;

        if (is_array($groups)) {
            foreach ($groups as $group) {
                $attributes = array_merge($attributes, (array)$group);
            }
            $attributes = array_reverse($attributes, true);
        } else {
            return $values;
        }

        foreach ($attributes as $code => $attribute) {
            $value = $attribute;
            $value['code'] = $code;
            $value['id'] = $innerId;
            $value['prefix'] = $this->getFieldPrefix();

            if ($this->getType()->getStoreId() != '0') {
                $value['checkbox_scope'] = $this->getCheckboxScopeHtml($innerId, 'label', !isset($value['default_label']));
                $value['label_disabled'] = isset($value['default_label']) ? false : true;
            }
            if (isset($value['options']) && is_array($value['options'])) {
                $selectId = 0;
                $defaultCode = (isset($value['default'])) ? $value['default'] : '';
                foreach($value['options'] as $option) {
                    $optionData = array(
                        'code'  => $option['code'],
                        'label' => $option['label'],
                        'id' => $innerId,
                        'select_id' => $selectId,
                        'checked' => ($option['code'] == $defaultCode) ? 'checked="checked"' : ''
                    );

                    if ($this->getType()->getStoreId() != '0') {
                        $optionData['checkbox_scope'] = $this->getCheckboxScopeHtml($innerId, 'label', !isset($option['default_label']), $selectId);
                        $optionData['label_disabled'] = isset($option['default_label']) ? false : true;
                    }
                    $value['items'][] = $optionData;
                    $selectId++;
                }
            }
            if (isset($value['frontend']) && is_array($value['frontend'])) {
                foreach($value['frontend'] as $param => $paramValue) {
                    $value[$param] = $paramValue;
                }
            }

            $values[] = new Varien_Object($value);
            $innerId++;
        }
        return $values;
    }

    /**
     * Checkbox html for store scope
     *
     * @param int $id
     * @param string $name
     * @param bool $checked
     * @param int|null $selectId
     * @return string
     */
    public function getCheckboxScopeHtml($id, $name, $checked=true, $selectId=null)
    {
        $selectNameHtml = '';
        $selectIdHtml = '';
        $checkedHtml = ($checked) ? ' checked="checked"' : '';
        $elementLabel = Mage::helper('enterprise_giftregistry')->__('Use Default Value');

        if (!is_null($selectId)) {
            $selectNameHtml = '[options]['.$selectId.']';
            $selectIdHtml = 'select_'.$selectId.'_';
        }

        $checkbox = '<div><input type="checkbox" id="'.$this->getFieldPrefix().'_attribute_'.$id.'_'.$selectIdHtml.$name.'_use_default" class="attribute-option-scope-checkbox" name="attributes['.$this->getFieldPrefix().']['.$id.']'.$selectNameHtml.'[use_default]['.$name.']" value="1" '.$checkedHtml.'/>';
        $checkbox .= '<label class="normal" for="'.$this->getFieldPrefix().'_attribute_'.$id.'_'.$selectIdHtml.$name.'_use_default"> '.$elementLabel.'</label></div>';
        return $checkbox;
    }

    /**
     * Prepare and return static types as Varien_Object
     *
     * @return array
     */
    public function getStaticTypes()
    {
        return new Varien_Object($this->getConfig()->getStaticTypes());
    }
}
