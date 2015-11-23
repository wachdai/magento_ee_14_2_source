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
 * @category    Tests
 * @package     Tests_Functional
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

namespace Mage\Adminhtml\Test\Block\Customer\Edit;

use Mage\Adminhtml\Test\Block\Widget\FormTabs;
use Mage\Customer\Test\Fixture\Customer;
use Mage\Customer\Test\Fixture\Address;

/**
 * Form for creation of the customer.
 */
class CustomerForm extends FormTabs
{
    /**
     * Fill Customer forms on tabs by customer, addresses data.
     *
     * @param Customer $customer
     * @param Address|Address[]|null $address
     * @return $this
     */
    public function fillCustomer(Customer $customer, $address = null)
    {
        if ($customer->hasData()) {
            parent::fill($customer);
        }
        if (null !== $address) {
            $this->openTab('addresses');
            $this->getTabElement('addresses')->fillAddresses($address);
        }

        return $this;
    }

    /**
     * Get data of Customer information, addresses on tabs.
     *
     * @param Customer $customer
     * @param Address|Address[]|null $address
     * @return array
     */
    public function getDataCustomer(Customer $customer, $address = null)
    {
        $data = ['customer' => $customer->hasData() ? parent::getData($customer) : parent::getData()];

        if (null !== $address) {
            $this->openTab('addresses');
            $data['addresses'] = $this->getTabElement('addresses')->getDataAddresses($address);
        }

        return $data;
    }
}
