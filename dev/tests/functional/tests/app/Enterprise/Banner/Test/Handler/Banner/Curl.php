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

namespace Enterprise\Banner\Test\Handler\Banner;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl for create banner.
 */
class Curl extends AbstractCurl implements BannerInterface
{
    /**
     * Data mapping.
     *
     * @var array
     */
    protected $mappingData = [
        'is_enabled' => [
            'Yes' => 1,
            'No' => 0,
        ],
        'type' => [
            'Any Banner Type' => 0,
            'Specified Banner Types' => 1,
        ],
        'use_customer_segment' => [
            'All' => 0,
            'Specified' => 1,
        ],
    ];

    /**
     * Url for save rewrite.
     *
     * @var string
     */
    protected $url = 'banner/save';

    /**
     * Post request for creating banner.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . $this->url;
        $data = $this->replaceMappingData($fixture->getData());
        $data['banner_sales_rules'] = isset($data['banner_sales_rules'])
            ? $this->prepareRules($data['banner_sales_rules'])
            : '';
        $data['banner_catalog_rules'] = isset($data['banner_catalog_rules'])
            ? $this->prepareRules($data['banner_catalog_rules'])
            : '';

        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write(CurlInterface::POST, $url, '1.1', [], $data);
        $response = $curl->read();

        if (!strpos($response, 'class="messages"')) {
            throw new \Exception("Banner creation by curl handler was not successful! Response: $response");
        }
        $curl->close();
        $id = $this->getBannerId($response);

        return ['banner_id' => $id];
    }

    /**
     * Prepare rules.
     *
     * @param array $data
     * @return string
     */
    protected function prepareRules(array $data)
    {
        $rule = '';
        foreach ($data as $index => $rule) {
            if ($index > 0) {
                $rule .= '&';
            }
        }
        return $rule;
    }

    /**
     * Return saved banner id.
     *
     * @param string $response
     * @return int|null
     * @throws \Exception
     */
    protected function getBannerId($response)
    {
        preg_match_all('~banner/edit[^\s]*\/id\/(\d+)~', $response, $matches);
        if (empty($matches)) {
            throw new \Exception('Cannot find Banner id');
        }

        return max(empty($matches[1]) ? null : $matches[1]);
    }

    /**
     * Replace mapping data in fixture data.
     *
     * @param array $data
     * @return array
     */
    protected function replaceMappingData(array $data)
    {
        if (isset($data['store_contents'])) {
            $storeContents = $data['store_contents'];
            unset($data['store_contents']);
            $data['store_contents'][] = $storeContents['store_content'];
            $data['store_contents_not_use'][1] = ($storeContents['store_contents_not_use'] === 'Yes') ? 1 : 0;
        }

        if (isset($data['customer_segment_ids'])) {
            foreach ($data['customer_segment_ids'] as $key => $customerSegment) {
                $data["customer_segment_ids[{$key}]"] = $customerSegment;
            }
            unset($data['customer_segment_ids']);
        }

        return parent::replaceMappingData($data);
    }
}
