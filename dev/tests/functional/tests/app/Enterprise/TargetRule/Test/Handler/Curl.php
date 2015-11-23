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

namespace Enterprise\TargetRule\Test\Handler;

use Mage\Adminhtml\Test\Handler\Conditions;
use Enterprise\TargetRule\Test\Fixture\TargetRule;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating target rule through backend.
 */
class Curl extends Conditions implements TargetRuleInterface
{
    /**
     * Map of type parameter.
     *
     * @var array
     */
    protected $mapTypeParams = [
        'Conditions combination' => [
            'type' => 'enterprise_targetrule/actions_condition_combine',
            'aggregator' => 'all',
            'value' => 1,
        ],
        'Attribute Set' => [
            'type' => 'enterprise_targetrule/rule_condition_product_attributes',
            'attribute' => 'attribute_set_id',
        ],
        'Price (percentage)' => [
            'type' => 'enterprise_targetrule/rule_condition_product_special_price',
        ],
        'Category' => [
            'type' => 'enterprise_targetrule/rule_condition_product_attributes',
            'attribute' => 'category_ids',
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
            'Related Products' => 1,
            'Up-sells' => 2,
            'Cross-sells' => 3,
        ],
        'use_customer_segment' => [
            'All' => 0,
            'Specified' => 1,
        ],
    ];

    /**
     * Post request for creating target rule in backend.
     *
     * @param FixtureInterface|null $targetRule
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $targetRule = null)
    {
        /** @var TargetRule $targetRule */
        $url = $_ENV['app_backend_url'] . 'targetrule/save/back/edit/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $data = $this->prepareData($targetRule);
        $curl->write(CurlInterface::POST, $url, '1.1', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'class="success-msg"')) {
            throw new \Exception("TargetRule entity creating by curl handler was not successful! Response: $response");
        }

        return ['id' => $this->getTargetRuleId($response)];
    }

    /**
     * Prepare target rule data.
     *
     * @param TargetRule $targetRule
     * @return array
     */
    protected function prepareData(TargetRule $targetRule)
    {
        $data = $this->replaceMappingData($targetRule->getData());
        if (!isset($data['conditions_serialized'])) {
            $data['rule']['conditions'] = '';
        } else {
            $data['rule']['conditions'] = $this->prepareCondition($data['conditions_serialized']);
        }
        unset($data['conditions_serialized']);
        if (!isset($data['actions_serialized'])) {
            $data['actions_serialized'] = '';
        }
        $data['rule']['actions'] = $this->prepareCondition($data['actions_serialized']);
        unset($data['actions_serialized']);

        return $data;
    }

    /**
     * Get target rule id from response.
     *
     * @param string $response
     * @return int|null
     */
    protected function getTargetRuleId($response)
    {
        preg_match('/targetrule\/delete\/id\/([0-9]+)/', $response, $match);
        return empty($match[1]) ? null : $match[1];
    }
}
