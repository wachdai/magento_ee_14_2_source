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

namespace Mage\Core\Test\Handler\ConfigData;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Magento\Mtf\Handler\Curl as AbstractCurl;

/**
 * Curl for setting config.
 */
class Curl extends AbstractCurl implements ConfigDataInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'scope' => [
            'Website' => 'website',
            'Store' => 'group',
            'Store View' => 'store',
        ],
    ];

    /**
     * Post request for setting configuration.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return void
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        foreach ($data as $scope => $item) {
            $this->applyConfigSettings($item, $scope);
        }
    }

    /**
     * Prepare POST data for setting configuration.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $result = [];
        $fields = $fixture->getData();
        if (isset($fields['section'])) {
            foreach ($fields['section'] as $key => $itemSection) {
                $item = explode('/', $itemSection['path']);
                $this->prepareResult($itemSection, $item, $result);
            }
        }
        return $result;
    }

    /**
     * Prepare result array according to $item count.
     *
     * @param array $itemSection
     * @param array $item
     * @param array $result
     * @return array
     */
    protected function prepareResult(array $itemSection, array $item, array &$result)
    {
        switch (count($item)) {
            case 3:
                $value = isset($this->mappingData[$item[2]])
                    ? $this->mappingData[$item[2]][$itemSection['value']]
                    : $itemSection['value'];
                $result[$itemSection['scope']]['groups'][$item[1]]['fields'][$item[2]]['value'] = $value;
                break;
            case 5:
                $result[$itemSection['scope']]['groups'][$item[3]]['fields'][$item[4]]['value'] = $itemSection['value'];
                break;
        }
    }

    /**
     * Apply config settings via curl.
     *
     * @param array $data
     * @param string $section
     * @throws \Exception
     */
    protected function applyConfigSettings(array $data, $section)
    {
        $url = $this->getUrl($section);
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (strpos($response, 'class="success-msg"') === false) {
            throw new \Exception("Settings are not applied! Response: $response");
        }
    }

    /**
     * Retrieve URL for request.
     *
     * @param string $section
     * @return string
     */
    protected function getUrl($section)
    {
        return $_ENV['app_backend_url'] . 'system_config/save/section/' . $section;
    }
}
