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

namespace Enterprise\CustomerSegment\Test\Handler\CustomerSegment;

use Mage\Adminhtml\Test\Handler\Conditions;
use Enterprise\CustomerSegment\Test\Fixture\CustomerSegment;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating customer segment through backend.
 */
class Curl extends Conditions implements CustomerSegmentInterface
{
    /**
     * Map of type parameter.
     *
     * @var array
     */
    protected $mapTypeParams = [
        'Conditions combination' => [
            'type' => 'Magento\CustomerSegment\Model\Segment\Condition\Combine\Root',
            'aggregator' => 'all',
            'value' => 1,
        ],
        'Default Billing Address' => [
            'type' => 'Magento\CustomerSegment\Model\Segment\Condition\Customer\Attributes',
            'attribute' => 'default_billing',
        ],
        'Default Shipping Address' => [
            'type' => 'Magento\CustomerSegment\Model\Segment\Condition\Customer\Attributes',
            'attribute' => 'default_shipping',
        ],
    ];

    /**
     * Map of rule parameters.
     *
     * @var array
     */
    protected $mapRuleParams = [
        'value' => [
            'exists' => 'is_exists',
        ],
    ];

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Active' => 1,
            'Inactive' => 0,
        ],
        'apply_to' => [
            'Visitors and Registered Customers' => 0,
            'Registered Customers' => 1,
            'Visitors' => 2,
        ],
        'website_ids' => [
            'Main Website' => 1
        ]
    ];

    /**
     * Post request for creating customer segment in backend.
     *
     * @param FixtureInterface|null $customerSegment
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $customerSegment = null)
    {
        /** @var CustomerSegment $customerSegment */
        $data = $this->prepareData($customerSegment);
        $url = $_ENV['app_backend_url'] . 'customersegment/save';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();
        if (strpos($response, 'class="success-msg"') === false) {
            throw new \Exception(
                "Customer segment entity creating by curl handler was not successful! Response: $response"
            );
        }

        return ['segment_id' => $this->getCustomerSegmentId($customerSegment)];
    }

    /**
     * Prepare data for create.
     *
     * @param CustomerSegment $customerSegment
     * @return array
     */
    protected function prepareData(CustomerSegment $customerSegment)
    {
        $data = $this->replaceMappingData($customerSegment->getData());
        if ($customerSegment->hasData('conditions_serialized')) {
            $data['rule']['conditions'] = $this->prepareCondition($data['conditions_serialized']);
            unset($data['conditions_serialized']);
        }
        if (isset($data['website_ids'])) {
            foreach ($data['website_ids'] as $key => $value) {
                $data['website_ids'][$key] = isset($this->mappingData['website_ids'][$value])
                    ? $this->mappingData['website_ids'][$value]
                    : $value;
            }
        }

        return $data;
    }

    /**
     * Get customer segment id from response.
     *
     * @param CustomerSegment $customerSegment
     * @return int|null
     */
    protected function getCustomerSegmentId(CustomerSegment $customerSegment)
    {
        $filter = ['grid_segment_name' => $customerSegment->getName()];
        $url = $_ENV['app_backend_url'] . 'customersegment/grid/filter/' . $this->encodeFilter($filter);
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);

        $curl->write(CurlInterface::GET, $url, '1.0');
        $response = $curl->read();
        $curl->close();

        preg_match('/edit\/id\/([0-9]+)/', $response, $match);
        return empty($match[1]) ? null : $match[1];
    }

    /**
     * Encoded filter parameters.
     *
     * @param array $filter
     * @return string
     */
    protected function encodeFilter(array $filter)
    {
        $result = [];
        foreach ($filter as $name => $value) {
            $result[] = "{$name}={$value}";
        }
        $result = implode('&', $result);

        return base64_encode($result);
    }
}
