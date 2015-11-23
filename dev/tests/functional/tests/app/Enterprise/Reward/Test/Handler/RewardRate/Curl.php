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

namespace Enterprise\Reward\Test\Handler\RewardRate;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Config;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Enterprise\Reward\Test\Fixture\RewardRate;

/**
 * Curl creation of reward points exchange rate.
 */
class Curl extends AbstractCurl implements RewardRateInterface
{
    /**
     * Mapping for reward rate exchange data.
     *
     * @var array
     */
    protected $mappingData = [
        'direction' => [
            'Points to Currency' => 1,
            'Currency to Points' => 2,
        ],
    ];

    /**
     * Post request for creating rate exchange.
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . 'reward_rate/save/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write(CurlInterface::POST, $url, '1.1', [], $this->prepareDara($fixture));
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'class="success-msg"')) {
            throw new \Exception("Exchange Rate creation by curl handler was not successful! Response: $response");
        }

        return ['rate_id' => $this->getRateId()];
    }

    /**
     * Get Reward exchange rate id.
     *
     * @return string|null
     */
    protected function getRateId()
    {
        $url = $_ENV['app_backend_url'] . 'reward_rate/index/sort/rate_id/dir/desc/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write(CurlInterface::GET, $url, '1.0');
        $response = $curl->read();
        $curl->close();
        preg_match('@rate_id/(\d+)@', $response, $match);

        return empty($match[1]) ? null : $match[1];
    }

    /**
     * Prepare fixture data.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareDara(FixtureInterface $fixture)
    {
        /** @var RewardRate $fixture */
        $data['rate'] = $this->replaceMappingData($fixture->getData());
        $data['rate']['customer_group_id'] = $fixture->getDataFieldConfig('customer_group_id')['source']
            ->getCustomerGroup()
            ->getCustomerGroupId();
        $data['rate']['website_id'] = $fixture->getDataFieldConfig('website_id')['source']
            ->getWebsite()
            ->getWebsiteId();

        return $data;
    }
}
