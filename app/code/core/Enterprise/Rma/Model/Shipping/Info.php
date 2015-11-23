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
 * RMA Shipping Info Model
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Model_Shipping_Info extends Varien_Object
{
    /**
     * Tracking info
     *
     * @var array
     */
    protected $_trackingInfo = array();

    /**
     * Generating tracking info
     *
     * @param string $hash
     * @return Mage_Shipping_Model_Info
     */
    public function loadByHash($hash)
    {
        $data = Mage::helper('enterprise_rma')->decodeTrackingHash($hash);

        if (!empty($data)) {
            $this->setData($data['key'], $data['id']);
            $this->setProtectCode($data['hash']);

            if ($this->getRmaId()>0) {
                $this->getTrackingInfoByRma();
            } else {
                $this->getTrackingInfoByTrackId();
            }
        }
        return $this;
    }

    /**
     * Generating tracking info
     *
     * @param string $hash
     * @return Mage_Shipping_Model_Info
     */
    public function loadPackage($hash)
    {
        $data = Mage::helper('enterprise_rma')->decodeTrackingHash($hash);
        $package = array();
        if (!empty($data)) {
            $this->setData($data['key'], $data['id']);
            $this->setProtectCode($data['hash']);
            if ($rma = $this->_initRma()) {
                $package = $rma->getShippingLabel();
            }
        }
        return $package;
    }

    /**
     * Retrieve tracking info
     *
     * @return array
     */
    public function getTrackingInfo()
    {
        return $this->_trackingInfo;
    }

    /**
     * Instantiate RMA model
     *
     * @return Enterprise_Rma_Model_Rma || false
     */
    protected function _initRma()
    {
        /* @var $model Enterprise_Rma_Model_Rma */
        $model = Mage::getModel('enterprise_rma/rma');
        $rma = $model->load($this->getRmaId());
        if (!$rma->getEntityId() || $this->getProtectCode() != $rma->getProtectCode()) {
            return false;
        }
        return $rma;
    }

    /**
     * Retrieve all tracking by RMA id
     *
     * @return array
     */
    public function getTrackingInfoByRma()
    {
        $shipTrack = array();
        $rma = $this->_initRma();
        if ($rma) {
            $increment_id   = $rma->getIncrementId();
            $tracks         = $rma->getTrackingNumbers();
            $trackingInfos  = array();

            foreach ($tracks as $track){
                $trackingInfos[] = $track->getNumberDetail();
            }
            $shipTrack[$increment_id] = $trackingInfos;

        }
        $this->_trackingInfo = $shipTrack;
        return $this->_trackingInfo;
    }

    /**
     * Retrieve tracking by tracking entity id
     *
     * @return array
     */
    public function getTrackingInfoByTrackId()
    {
        $track = Mage::getModel('enterprise_rma/shipping')->load($this->getTrackId());
        if ($track->getId() && $this->getProtectCode() == $track->getProtectCode()) {
            $this->_trackingInfo = array(array($track->getNumberDetail()));
        }
        return $this->_trackingInfo;
    }
}
