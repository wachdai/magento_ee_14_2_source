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

namespace Enterprise\CustomerBalance\Test\Handler;

use Mage\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Enterprise\CustomerBalance\Test\Fixture\CustomerBalance;
use Mage\Adminhtml\Test\Fixture\Website;

/**
 * Curl handler for creating customer balance through customer form backend.
 */
class Curl extends AbstractCurl implements CustomerBalanceInterface
{
    /**
     * Post request for creating customer balance on backend.
     *
     * @param FixtureInterface|null $fixture
     * @return void
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url']
            . 'customer/save/back/edit/tab/customer_info_tabs_customerbalance/id/' . $data['customer_id'] . '/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write(CurlInterface::POST, $url, '1.1', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'class="success-msg"')) {
            throw new \Exception(
                "Adding customer balance by curl handler was not successful! Response: $response"
            );
        }
    }

    /**
     * Prepare data from text to values.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData($fixture)
    {
        /** @var CustomerBalance $fixture */
        $customer = $fixture->getDataFieldConfig('customer_id')['source']->getCustomer();
        /** @var Website $website */
        $website = $fixture->getDataFieldConfig('website_id')['source']->getWebsite();
        /** @var Customer $customer */
        return [
            'customer_id' => $customer->getId(),
            'customerbalance' => [
                'amount_delta' => $fixture->getAmountDelta(),
                'website_id' => $website->getWebsiteId(),
                'comment' => $fixture->getComment(),
            ],
        ];
    }
}
