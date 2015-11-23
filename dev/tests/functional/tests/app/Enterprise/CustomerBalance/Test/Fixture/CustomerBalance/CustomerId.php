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

namespace Enterprise\CustomerBalance\Test\Fixture\CustomerBalance;

use Mage\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Prepare data for customer_id field in customer balance fixture.
 *
 * Data keys:
 *  - dataSet
 *  - customer
 */
class CustomerId implements FixtureInterface
{
    /**
     * Customer id.
     *
     * @var string
     */
    protected $data;

    /**
     * Data set configuration settings.
     *
     * @var array
     */
    protected $params;

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $customer = null;
        $this->params = $params;
        if (isset($data['customer']) && $data['customer'] instanceof Customer) {
            $customer = $data['customer'];
        } elseif (isset($data['dataSet'])) {
            /** @var Customer $customer */
            $customer = $fixtureFactory->createByCode('customer', ['dataSet' => $data['dataSet']]);
            if (!$customer->hasData('id')) {
                $customer->persist();
            }
        }

        $this->customer = $customer;
        $this->data = $customer->getId();
    }

    /**
     * Persists prepared data into application.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set.
     *
     * @param string|null $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return string
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return customer fixture.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
