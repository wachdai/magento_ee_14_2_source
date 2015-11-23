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
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Customer newsletter subscription
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Customer_Newsletter
    extends Enterprise_CustomerSegment_Model_Condition_Abstract
{
    protected $_inputType = 'select';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_customer_newsletter');
        $this->setValue(1);
    }

    /**
     * Set data with filtering
     *
     * @param mixed $key
     * @param mixed $value
     * @return Enterprise_CustomerSegment_Model_Segment_Condition_Customer_Newsletter
     */
    public function setData($key, $value = null)
    {
        //filter key "value"
        if (is_array($key) && isset($key['value']) && $key['value'] !== null) {
            $key['value'] = (int) $key['value'];
        } elseif ($key == 'value' && $value !== null) {
            $value = (int) $value;
        }

        return parent::setData($key, $value);
    }

    /**
     * Get array of event names where segment with such conditions combine can be matched
     *
     * @return array
     */
    public function getMatchedEvents()
    {
        return array('customer_save_commit_after', 'newsletter_subscriber_save_commit_after');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return array(array('value' => $this->getType(),
            'label'=>Mage::helper('enterprise_customersegment')->__('Newsletter Subscription')));
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        $operator = $this->getOperatorElementHtml();
        $element = $this->getValueElementHtml();
        return $this->getTypeElementHtml()
            .Mage::helper('enterprise_customersegment')->__('Customer is %s to newsletter.', $element)
            .$this->getRemoveLinkHtml();
    }

    /**
     * Get element type for value select
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Init list of available values
     *
     * @return array
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array(
            '1'  => Mage::helper('enterprise_customersegment')->__('subscribed'),
            '0' => Mage::helper('enterprise_customersegment')->__('not subscribed'),
        ));
        return $this;
    }

    /**
     * Get condition query for customer balance
     *
     * @param $customer
     * @param int | Zend_Db_Expr $website
     * @return Varien_Db_Select
     */
    public function getConditionsSql($customer, $website)
    {
        $table = $this->getResource()->getTable('newsletter/subscriber');
        $value = (int) $this->getValue();

        $select = $this->getResource()->createSelect()
            ->from(array('main' => $table), array(new Zend_Db_Expr($value)))
            ->where($this->_createCustomerFilter($customer, 'main.customer_id'))
            ->where('main.subscriber_status = ?', Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);

        Mage::getResourceHelper('enterprise_customersegment')->setOneRowLimit($select);

        $this->_limitByStoreWebsite($select, $website, 'main.store_id');
        if (!$value) {
            $select = $this->getResource()->getReadConnection()
                    ->getIfNullSql($select, 1);
        }
        return $select;
    }
}
