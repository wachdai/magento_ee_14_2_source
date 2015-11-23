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
 * @package     Enterprise_Banner
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Banners chooser for Banner Rotator widget
 *
 * @category   Enterprise
 * @package    Enterprise_Banner
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Banner_Block_Adminhtml_Widget_Chooser extends Enterprise_Banner_Block_Adminhtml_Banner_Grid
{
    /**
     * Store selected banner Ids
     * Used in initial setting selected banners
     *
     * @var array
     */
    protected $_selectedBanners = array();

    /**
     * Store hidden banner ids field id
     *
     * @var string
     */
    protected $_elementValueId = '';

    /**
     * Block construction, prepare grid params
     *
     * @param array $arguments Object data
     */
    public function __construct($arguments=array())
    {
        parent::__construct($arguments);
        $this->setDefaultFilter(array('in_banners'=>1));
    }

    /**
     * Prepare chooser element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_elementValueId = "{$element->getId()}";
        $this->_selectedBanners = explode(',', $element->getValue());

        //Create hidden field that store selected banner ids
        $hidden = new Varien_Data_Form_Element_Hidden($element->getData());
        $hidden->setId($this->_elementValueId)->setForm($element->getForm());
        $hiddenHtml = $hidden->getElementHtml();

        $element->setValue('')->setValueClass('value2');
        $element->setData('after_element_html', $hiddenHtml . $this->toHtml());

        return $element;
    }

    /**
     * Grid row init js callback
     *
     * @return string
     */
    public function getRowInitCallback()
    {
        return '
        function(grid, row){
            if(!grid.selBannersIds){
                grid.selBannersIds = {};
                if($(\'' . $this->_elementValueId . '\').value != \'\'){
                    var elementValues = $(\'' . $this->_elementValueId . '\').value.split(\',\');
                    for(var i = 0; i < elementValues.length; i++){
                        grid.selBannersIds[elementValues[i]] = i+1;
                    }
                }
                grid.reloadParams = {};
                grid.reloadParams[\'selected_banners[]\'] = Object.keys(grid.selBannersIds);
            }
            var inputs      = Element.select($(row), \'input\');
            var checkbox    = inputs[0];
            var position    = inputs[1];
            var bannersNum  = grid.selBannersIds.length;
            var bannerId    = checkbox.value;

            inputs[1].checkboxElement = checkbox;

            var indexOf = Object.keys(grid.selBannersIds).indexOf(bannerId);
            if(indexOf >= 0){
                checkbox.checked = true;
                if (!position.value) {
                    position.value = indexOf + 1;
                }
            }

            Event.observe(position,\'change\', function(){
                var checkb = Element.select($(row), \'input\')[0];
                if(checkb.checked){
                    grid.selBannersIds[checkb.value] = this.value;
                    var idsclone = Object.clone(grid.selBannersIds);
                    var bans = Object.keys(grid.selBannersIds);
                    var pos = Object.values(grid.selBannersIds).sort(sortNumeric);
                    var banners = [];
                    var k = 0;

                    for(var j = 0; j < pos.length; j++){
                        for(var i = 0; i < bans.length; i++){
                            if(idsclone[bans[i]] == pos[j]){
                                banners[k] = bans[i];
                                k++;
                                delete(idsclone[bans[i]]);
                                break;
                            }
                        }
                    }
                    $(\'' . $this->_elementValueId . '\').value = banners.join(\',\');
                }
            });
        }
        ';
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        return '
            function (grid, event) {
                if(!grid.selBannersIds){
                    grid.selBannersIds = {};
                }

                var trElement   = Event.findElement(event, "tr");
                var isInput     = Event.element(event).tagName == \'INPUT\';
                var inputs      = Element.select(trElement, \'input\');
                var checkbox    = inputs[0];
                var position    = inputs[1].value || 1;
                var checked     = isInput ? checkbox.checked : !checkbox.checked;
                checkbox.checked = checked;
                var bannerId    = checkbox.value;

                if(checked){
                    if(Object.keys(grid.selBannersIds).indexOf(bannerId) < 0){
                        grid.selBannersIds[bannerId] = position;
                    }
                }
                else{
                    delete(grid.selBannersIds[bannerId]);
                }

                var idsclone = Object.clone(grid.selBannersIds);
                var bans = Object.keys(grid.selBannersIds);
                var pos = Object.values(grid.selBannersIds).sort(sortNumeric);
                var banners = [];
                var k = 0;
                for(var j = 0; j < pos.length; j++){
                    for(var i = 0; i < bans.length; i++){
                        if(idsclone[bans[i]] == pos[j]){
                            banners[k] = bans[i];
                            k++;
                            delete(idsclone[bans[i]]);
                            break;
                        }
                    }
                }
                $(\'' . $this->_elementValueId . '\').value = banners.join(\',\');
                grid.reloadParams = {};
                grid.reloadParams[\'selected_banners[]\'] = banners;
            }
        ';
    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback()
    {
        return 'function (grid, element, checked) {
                    if(!grid.selBannersIds){
                        grid.selBannersIds = {};
                    }
                    var checkbox    = element;

                    checkbox.checked = checked;
                    var bannerId    = checkbox.value;
                    if(bannerId == \'on\'){
                        return;
                    }
                    var trElement   = element.up(\'tr\');
                    var inputs      = Element.select(trElement, \'input\');
                    var position    = inputs[1].value || 1;

                    if(checked){
                        if(Object.keys(grid.selBannersIds).indexOf(bannerId) < 0){
                            grid.selBannersIds[bannerId] = position;
                        }
                    }
                    else{
                        delete(grid.selBannersIds[bannerId]);
                    }

                    var idsclone = Object.clone(grid.selBannersIds);
                    var bans = Object.keys(grid.selBannersIds);
                    var pos = Object.values(grid.selBannersIds).sort(sortNumeric);
                    var banners = [];
                    var k = 0;
                    for(var j = 0; j < pos.length; j++){
                        for(var i = 0; i < bans.length; i++){
                            if(idsclone[bans[i]] == pos[j]){
                                banners[k] = bans[i];
                                k++;
                                delete(idsclone[bans[i]]);
                                break;
                            }
                        }
                    }
                    $(\'' . $this->_elementValueId . '\').value = banners.join(\',\');
                    grid.reloadParams = {};
                    grid.reloadParams[\'selected_banners[]\'] = banners;
                }';
    }

    /**
     * Create grid columns
     *
     * @return Enterprise_Banner_Block_Widget_Chooser
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_banners', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_banners',
            'values'    => $this->getSelectedBanners(),
            'align'     => 'center',
            'index'     => 'banner_id',
        ));

        $this->addColumn('position', array(
            'header'         => Mage::helper('enterprise_banner')->__('Position'),
            'name'           => 'position',
            'type'           => 'number',
            'validate_class' => 'validate-number',
            'index'          => 'position',
            'editable'       => true,
            'filter'         => false,
            'edit_only'      => true,
            'sortable'       => false
        ));
        $this->addColumnsOrder('position', 'banner_is_enabled');

        return parent::_prepareColumns();
    }

    /* Set custom filter for in banner flag
     *
     * @param string $column
     * @return Enterprise_Banner_Block_Widget_Chooser
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_banners') {
            $bannerIds = $this->getSelectedBanners();
            if (empty($bannerIds)) {
                $bannerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addBannerIdsFilter($bannerIds);
            } else {
                if ($bannerIds) {
                    $this->getCollection()->addBannerIdsFilter($bannerIds, true);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Disable massaction functioanality
     *
     * @return Enterprise_Banner_Block_Widget_Chooser
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Adds additional parameter to URL for loading only banners grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/banner_widget/chooser', array(
            'banners_grid' => true,
            '_current' => true,
            'uniq_id' => $this->getId(),
            'selected_banners' => join(',', $this->getSelectedBanners())
        ));
    }

    /**
     * Setter
     *
     * @param array $selectedBanners
     * @return Enterprise_Banner_Block_Widget_Chooser
     */
    public function setSelectedBanners($selectedBanners)
    {
        if (is_string($selectedBanners)) {
            $selectedBanners = explode(',', $selectedBanners);
        }        
        $this->_selectedBanners = $selectedBanners;
        return $this;
    }

    /**
     * Set banners' positions of saved banners
     *
     * @return Enterprise_Banner_Block_Adminhtml_Widget_Chooser
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        
        foreach ($this->getCollection() as $item) {
            foreach ($this->getSelectedBanners() as $pos => $banner) {
                if ($banner == $item->getBannerId()) {
                    $item->setPosition($pos + 1);
                }
            }
        }
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedBanners()
    {
        if ($selectedBanners = $this->getRequest()->getParam('selected_banners', $this->_selectedBanners)) {
            $this->setSelectedBanners($selectedBanners);
        }
        return $this->_selectedBanners;
    }
}
