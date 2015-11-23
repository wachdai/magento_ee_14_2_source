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

/**
 * Gift registry frontend search controller
 */
class Enterprise_GiftRegistry_SearchController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check if gift registry is enabled on current store before all other actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('enterprise_giftregistry')->isEnabled()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
    }

    /**
     * Get current customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Initialize gift registry type model
     *
     * @param int $typeId
     * @return Enterprise_GiftRegistry_Model_Type
     */
    protected function _initType($typeId)
    {
        $type = Mage::getModel('enterprise_giftregistry/type')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($typeId);

        Mage::register('current_giftregistry_type', $type);
        return $type;
    }

    /**
     * Filter input form data
     *
     * @param  array $params
     * @return array
     */
    protected function _filterInputParams($params)
    {
        foreach ($params as $key => $value) {
            $params[$key] = htmlspecialchars($value);
        }
        if (isset($params['type_id'])) {
            $type = $this->_initType($params['type_id']);
            $dateType = Mage::getSingleton('enterprise_giftregistry/attribute_config')->getStaticDateType();
            if ($dateType) {
                $attribute = $type->getAttributeByCode($dateType);
                $format = (isset($attribute['date_format'])) ? $attribute['date_format'] : null;

                $dateFields = array();
                $fromDate = $dateType . '_from';
                $toDate = $dateType . '_to';

                if (isset($params[$fromDate])) {
                    $dateFields[] = $fromDate;
                }
                if (isset($params[$toDate])) {
                    $dateFields[] = $toDate;
                }
                $params = $this->_filterInputDates($params, $dateFields, $format);
            }
        }
        return $params;
    }

    /**
     * Validate input search params
     *
     * @param array $params
     * @return bool
     */
    protected function _validateSearchParams($params)
    {
        if (empty($params) || !is_array($params) || empty($params['search'])) {
            $this->_getSession()->addNotice(
                Mage::helper('enterprise_giftregistry')->__('Please specify correct search options.')
            );
            return false;
        }

        switch ($params['search']) {
            case 'type':
                if (empty($params['firstname']) || strlen($params['firstname']) < 2) {
                    $this->_getSession()->addNotice(
                        Mage::helper('enterprise_giftregistry')->__('Please enter at least 2 letters of the first name.')
                    );
                    return false;
                }
                if (empty($params['lastname']) || strlen($params['lastname']) < 2) {
                    $this->_getSession()->addNotice(
                        Mage::helper('enterprise_giftregistry')->__('Please enter at least 2 letters of the last name.')
                    );
                    return false;
                }
                break;

            case 'email':
                if (empty($params['email']) || !Zend_Validate::is($params['email'], 'EmailAddress')) {
                    $this->_getSession()->addNotice(
                        Mage::helper('enterprise_giftregistry')->__('Please input a valid email address.')
                    );
                    return false;
                }
                break;

            case 'id':
                if (empty($params['id'])) {
                    $this->_getSession()->addNotice(
                        Mage::helper('enterprise_giftregistry')->__('Please specify gift registry ID.')
                    );
                    return false;
                }
                break;

            default:
                $this->_getSession()->addNotice(
                    Mage::helper('enterprise_giftregistry')->__('Please specify correct search options.')
                );
                return false;
        }
        return true;
    }

    /**
     * Convert dates in array from localized to internal format
     *
     * @param   array $array
     * @param   array $dateFields
     * @return  array
     */
    protected function _filterInputDates($array, $dateFields, $format = null)
    {
        if (empty($dateFields)) {
            return $array;
        }
        if (is_null($format)) {
            $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT;
        }

        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'date_format' => Mage::app()->getLocale()->getDateFormat($format)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('enterprise_giftregistry')->__('Gift Registry Search'));
        }
        $this->renderLayout();
    }

    /**
     * Index action
     */
    public function resultsAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        if ($params = $this->getRequest()->getParam('params')) {
            $this->_getSession()->setRegistrySearchData($params);
        } else {
            $params = $this->_getSession()->getRegistrySearchData();
        }

        if ($this->_validateSearchParams($params)) {
            $results = Mage::getModel('enterprise_giftregistry/entity')->getCollection()
                ->applySearchFilters($this->_filterInputParams($params));

            $this->getLayout()->getBlock('giftregistry.search.results')
                ->setSearchResults($results);
        } else {
            $this->_redirect('*/*/index', array('_current' => true));
            return;
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('enterprise_giftregistry')->__('Gift Registry Search'));
        }
        $this->renderLayout();
    }

    /**
     * Load type specific advanced search attributes
     */
    public function advancedAction()
    {
        $this->_initType($this->getRequest()->getParam('type_id'));
        $this->loadLayout();
        $this->renderLayout();
    }
}
