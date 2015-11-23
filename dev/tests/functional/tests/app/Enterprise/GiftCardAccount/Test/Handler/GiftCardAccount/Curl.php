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

namespace Enterprise\GiftCardAccount\Test\Handler\GiftCardAccount;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Creates Admin User Entity.
 */
class Curl extends AbstractCurl implements GiftCardAccountInterface
{
    /**
     * Data mapping.
     *
     * @var array
     */
    protected $mappingData = [
        'status' => [
            'Yes' => 1,
            'No' => 1,
        ],
        'is_redeemable' => [
            'Yes' => 1,
            'No' => 1,
        ],
    ];

    /**
     * Saving gift card account POST url.
     *
     * @var string
     */
    protected $activeTabInfo = 'giftcardaccount/save/';

    /**
     * Gift card account generate link.
     *
     * @var string
     */
    protected $generate = 'giftcardaccount/generate/';

    /**
     * Gift card account sort by desc link.
     *
     * @var string
     */
    protected $sortByDesc = 'giftcardaccount/grid/sort/giftcardaccount_id/dir/desc/';

    /**
     * Create gift card account.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->replaceMappingData($fixture->getData());
        $data['website_id'] = $fixture->getDataFieldConfig('website_id')['source']->getWebsite()->getWebsiteId();

        $url = $_ENV['app_backend_url'] . $this->activeTabInfo;
        $generateCode = $_ENV['app_backend_url'] . $this->generate;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::GET, $generateCode);
        $curl->read();
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $content = $curl->read();
        $curl->close();
        if (!strpos($content, 'class="success-msg"')) {
            throw new \Exception("Gift card account creation by curl handler was not successful! Response: $content");
        }

        return ['code' => $this->getCode()];
    }

    /**
     * Retrieve gift card account code from curl response.
     *
     * @return string
     */
    protected function getCode()
    {
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $_ENV['app_backend_url'] . $this->sortByDesc);
        $content = $curl->read();
        $curl->close();

        preg_match('@</thead>.*?td class=" ">(.*?)</td>@siu', $content, $matches);
        return trim($matches[1]);
    }
}
