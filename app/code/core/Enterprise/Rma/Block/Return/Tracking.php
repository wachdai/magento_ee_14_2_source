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

class Enterprise_Rma_Block_Return_Tracking extends Mage_Core_Block_Template
{
    /**
     * Get whether rma is allowed for PSL
     *
     * @var bool|null
     */
    protected $_isRmaAvailableForPrintLabel;

    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('rma/return/tracking.phtml');
        $this->setRma(Mage::registry('current_rma'));
    }

    /**
     * Get collection of tracking numbers of RMA
     *
     * @return Enterprise_Rma_Model_Resource_Shipping_Collection|array
     */
    public function getTrackingNumbers()
    {
        return $this->getRma() ? $this->getRma()->getTrackingNumbers() : array();
    }

    /**
     * Get url for delete label action
     *
     * @return string
     */
    public function getDeleteLabelUrl()
    {
        return $this->getRma()
            ? $this->getUrl('*/*/delLabel/', array('entity_id' => $this->getRma()->getEntityId()))
            : '';
    }

    /**
     * Get messages on AJAX errors
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $message = Mage::getSingleton('core/session')->getErrorMessage();
        Mage::getSingleton('core/session')->unsErrorMessage();
        return $message;
    }

    /**
     * Get whether rma is allowed for PSL
     *
     * @return bool
     */
    public function isPrintShippingLabelAllowed()
    {
        if ($this->_isRmaAvailableForPrintLabel === null) {
            $this->_isRmaAvailableForPrintLabel = $this->getRma() && $this->getRma()->isAvailableForPrintLabel();
        }
        return $this->_isRmaAvailableForPrintLabel;
    }
}
