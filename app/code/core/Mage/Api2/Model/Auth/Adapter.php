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
 * @category    Mage
 * @package     Mage_Api2
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * API Auth Adapter class
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Auth_Adapter
{
    /**
     * Adapter models
     *
     * @var array
     */
    protected $_adapters = array();

    /**
     * Load adapters configuration and create adapters models
     *
     * @return Mage_Api2_Model_Auth_Adapter
     * @throws Exception
     */
    protected function _initAdapters()
    {
        /** @var $helper Mage_Api2_Helper_Data */
        $helper = Mage::helper('api2');

        foreach ($helper->getAuthAdapters(true) as $adapterKey => $adapterParams) {
            $adapterModel = Mage::getModel($adapterParams['model']);

            if (!$adapterModel instanceof Mage_Api2_Model_Auth_Adapter_Abstract) {
                throw new Exception('Authentication adapter must to extend Mage_Api2_Model_Auth_Adapter_Abstract');
            }
            $this->_adapters[$adapterKey] = $adapterModel;
        }
        if (!$this->_adapters) {
            throw new Exception('No active authentication adapters found');
        }
        return $this;
    }

    /**
     * Process request and figure out an API user type and its identifier
     *
     * Returns stdClass object with two properties: type and id
     *
     * @param Mage_Api2_Model_Request $request
     * @return stdClass
     */
    public function getUserParams(Mage_Api2_Model_Request $request)
    {
        $this->_initAdapters();

        foreach ($this->_adapters as $adapterModel) {
            /** @var $adapterModel Mage_Api2_Model_Auth_Adapter_Abstract */
            if ($adapterModel->isApplicableToRequest($request)) {
                $userParams = $adapterModel->getUserParams($request);

                if (null !== $userParams->type) {
                    return $userParams;
                }
                throw new Mage_Api2_Exception('Can not determine user type', Mage_Api2_Model_Server::HTTP_UNAUTHORIZED);
            }
        }
        return (object) array('type' => Mage_Api2_Model_Auth::DEFAULT_USER_TYPE, 'id' => null);
    }
}
