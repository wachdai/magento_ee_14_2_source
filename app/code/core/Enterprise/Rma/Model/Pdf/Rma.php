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
 * Rma PDF model
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Pdf_Rma extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * Variable to store store-depended string values of attributes
     *
     * @var null|array
     */
    protected $_attributeOptionValues = null;

    /**
     * Retrieve PDF
     *
     * @param array $rmaArray
     * @throws Mage_Core_Exception
     * @return Zend_Pdf
     */
    public function getPdf($rmaArray = array())
    {
        $this->_beforeGetPdf();

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        if (!(is_array($rmaArray) && (count($rmaArray) == 1))){
            Mage::throwException(Mage::helper('enterprise_rma')->__('Only one RMA is available for printing'));
        }
        $rma = $rmaArray[0];

        $storeId = $rma->getOrder()->getStore()->getId();
        if ($storeId) {
            Mage::app()->getLocale()->emulate($storeId);
            Mage::app()->setCurrentStore($storeId);
        }

        $page = $this->newPage();

        /* Add image */
        $this->insertLogo($page, $storeId);
        /* Add address */
        $this->insertAddress($page, $storeId);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 5);

        /* Add head */
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->drawRectangle(25, $this->y, 570, $this->y - 45);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page);

        $page->drawText(
            Mage::helper('enterprise_rma')->__('Return # ') . $rma->getIncrementId() . ' - ' . $rma->getStatusLabel(),
            35,
            $this->y - 10,
            'UTF-8'
        );

        $page->drawText(
            Mage::helper('enterprise_rma')->__('Return Date: ') .
                Mage::helper('core')->formatDate($rma->getDateRequested(), 'medium', false),
            35,
            $this->y - 20,
            'UTF-8'
        );

        $page->drawText(
            Mage::helper('enterprise_rma')->__('Order # ') . $rma->getOrder()->getIncrementId(),
            35,
            $this->y - 30,
            'UTF-8'
        );

        $page->drawText(
            Mage::helper('enterprise_rma')->__('Order Date: ') .
                Mage::helper('core')->formatDate($rma->getOrder()->getCreatedAtStoreDate(), 'medium', false),
            35,
            $this->y - 40,
            'UTF-8'
        );

        /* start y-position for next block */
        $this->y = $this->y - 50;

        /* add address blocks */
        $shippingAddress = $this->_formatAddress($rma->getOrder()->getShippingAddress()->format('pdf'));
        $returnAddress = $this
            ->_formatAddress(Mage::helper('enterprise_rma')
            ->getReturnAddress('pdf', array(), $this->getStoreId()));

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 290, $this->y - 15);
        $page->drawRectangle(305, $this->y, 570, $this->y - 15);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        $page->drawText(Mage::helper('enterprise_rma')->__('Shipping Address:'), 35, $this->y - 10, 'UTF-8');

        $page->drawText(Mage::helper('enterprise_rma')->__('Return Address:'), 315, $this->y - 10, 'UTF-8');

        $y = $this->y - 15 - (max(count($shippingAddress), count($returnAddress)) * 10 + 5);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $this->y - 15, 290, $y);
        $page->drawRectangle(305, $this->y - 15, 570, $y);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);

        $yStartAddresses = $this->y - 25;
        foreach ($shippingAddress as $value){
            if ($value!=='') {
                $page->drawText(strip_tags(ltrim($value)), 35, $yStartAddresses, 'UTF-8');
                $yStartAddresses -=10;
            }
        }
        $yStartAddresses = $this->y - 25;
        foreach ($returnAddress as $value){
            if ($value!=='') {
                $page->drawText(strip_tags(ltrim($value)), 315, $yStartAddresses, 'UTF-8');
                $yStartAddresses -=10;
            }

        }

        /* start y-position for next block */
        $this->y = $this->y - 20 - (max(count($shippingAddress), count($returnAddress)) * 10 + 5);

        /* Add table */
        $this->_setColumnXs();
        $this->_addItemTableHead($page);

        /* Add body */

        /** @var $collection Enterprise_Rma_Model_Resource_Item_Collection */
        $collection = $rma->getItemsForDisplay();

        foreach ($collection as $item){

            if ($this->y < 15) {
                $page = $this->_addNewPage();
            }

            /* Draw item */
            $this->_drawRmaItem($item, $page);
        }

        if ($storeId) {
            Mage::app()->getLocale()->revert();
        }

        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Create new page, assign to PDF object and repeat table head there
     *
     * @return Zend_Pdf_Page
     */
    protected function _addNewPage()
    {
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        $this->_addItemTableHead($page);
        return $page;
    }

    /**
     * Add items table head
     *
     * @param Zend_Pdf_Page $page
     */
    protected function _addItemTableHead($page)
    {
        $this->_setFontRegular($page);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y-15);
        $this->y -=10;

        $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
        $page->drawText(
            Mage::helper('enterprise_rma')->__('Product Name'),
            $this->getProductNameX(),
            $this->y,
            'UTF-8'
        );
        $page->drawText(
            Mage::helper('enterprise_rma')->__('SKU'),
            $this->getProductSkuX(),
            $this->y,
            'UTF-8'
        );
        $page->drawText(
            Mage::helper('enterprise_rma')->__('Condition'),
            $this->getConditionX(),
            $this->y,
            'UTF-8'
        );
        $page->drawText(
            Mage::helper('enterprise_rma')->__('Resolution'),
            $this->getResolutionX(),
            $this->y,
            'UTF-8'
        );
        $page->drawText(
            Mage::helper('enterprise_rma')->__('Requested Qty'),
            $this->getQtyRequestedX(),
            $this->y,
            'UTF-8'
        );
        $page->drawText(
            Mage::helper('enterprise_rma')->__('Qty'),
            $this->getQtyX(),
            $this->y,
            'UTF-8'
        );
        $page->drawText(
            Mage::helper('enterprise_rma')->__('Status'),
            $this->getStatusX(),
            $this->y,
            'UTF-8'
        );

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        $this->y -=15;
    }

    /**
     * Draw one line - rma item
     *
     * @param Enterprise_Rma_Model_Item $item
     * @param Zend_Pdf_Page $page
     */
    protected function _drawRmaItem($item, $page)
    {
        $shift = 0;
        foreach (Mage::helper('core/string')->str_split($item->getProductName(), 40, true, true) as $key => $part) {
            $page->drawText($part, $this->getProductNameX(), $this->y-$shift, 'UTF-8');
            $shift += 10;
        }

        $shift = 0;
        foreach (Mage::helper('core/string')->str_split($item->getProductSku(), 18) as $key => $part) {
            $page->drawText($part, $this->getProductSkuX(),$this->y-$shift, 'UTF-8');
            $shift += 10;
        }

        $condition = Mage::helper('core/string')->str_split(
            $this->_getOptionAttributeStringValue($item->getCondition()),
            25
        );
        $page->drawText($condition[0], $this->getConditionX(),$this->y, 'UTF-8');

        $resolution = Mage::helper('core/string')->str_split(
            $this->_getOptionAttributeStringValue($item->getResolution()),
            25
        );
        $page->drawText($resolution[0], $this->getResolutionX(),$this->y, 'UTF-8');
        $page->drawText(
            Mage::helper('enterprise_rma')->parseQuantity($item->getQtyRequested(), $item),
            $this->getQtyRequestedX(),
            $this->y,
            'UTF-8'
        );

        $page->drawText(
            Mage::helper('enterprise_rma')->getQty($item),
            $this->getQtyX(),
            $this->y,
            'UTF-8'
        );

        $status = Mage::helper('core/string')->str_split($item->getStatusLabel(), 25);
        $page->drawText($status[0], $this->getStatusX(),$this->y, 'UTF-8');

        $productOptions = $item->getOptions();
        if (is_array($productOptions) && !empty($productOptions)) {
            $this->_drawCustomOptions($productOptions, $page);
        }

        $this->y -= 10;
    }

    /**
     * Draw additional lines for item's custom options
     *
     * @param array $optionsArray
     * @param Zend_Pdf_Page $page
     */
    protected function _drawCustomOptions($optionsArray = array(), $page)
    {
        $this->_setFontRegular($page,6);
        foreach ($optionsArray as $value) {
            $this->y -= 8;
            $optionRowString = $value['label'] . ': ' .
                (isset($value['print_value']) ? $value['print_value'] : $value['value']);
            $productOptions = Mage::helper('core/string')->str_split($optionRowString, 60, true, true);
            $productOptions = isset($productOptions[0]) ? $productOptions[0] : '';
            $page->drawText($productOptions, $this->getProductNameX(), $this->y, 'UTF-8');
        }
        $this->_setFontRegular($page);
    }

    /**
     * Get string label of option-type item attributes
     *
     * @param int $attributeValue
     * @return string
     */
    protected function _getOptionAttributeStringValue($attributeValue)
    {
        if (is_null($this->_attributeOptionValues)) {
            $this->_attributeOptionValues = Mage::helper('enterprise_rma/eav')->getAttributeOptionStringValues();
        }
        if (isset($this->_attributeOptionValues[$attributeValue])) {
            return $this->_attributeOptionValues[$attributeValue];
        } else {
            return '';
        }
    }

    /**
     * Sets X coordinates for columns
     *
     */
    protected function _setColumnXs()
    {
        $this->setProductNameX(35);
        $this->setProductSkuX(200);
        $this->setConditionX(280);
        $this->setResolutionX(360);
        $this->setQtyRequestedX(425);
        $this->setQtyX(490);
        $this->setStatusX(520);
    }
}
