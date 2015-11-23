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

namespace Mage\Adminhtml\Test\Constraint;

use Mage\Adminhtml\Test\Fixture\Store;
use Mage\Adminhtml\Test\Fixture\StoreGroup;
use Mage\Adminhtml\Test\Fixture\Website;
use Mage\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\Browser;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Mage\CatalogSearch\Test\Page\CatalogsearchResult;

/**
 * Assert that product is present on custom website.
 */
class AssertProductIsPresentOnCustomWebsite extends AbstractConstraint
{
    /**
     * Website fixture.
     *
     * @var Website
     */
    protected $website;

    /**
     * Path to magento root.
     *
     * @var string
     */
    protected $magentoRoot;

    /**
     * Path to website folder.
     *
     * @var string
     */
    protected $websiteFolder;

    /**
     * Base dir path.
     *
     * @var string
     */
    protected $baseDir;

    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that product is present on custom website.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductView $catalogProductView
     * @param Store $storeView
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        CatalogProductView $catalogProductView,
        Store $storeView,
        Browser $browser
    ) {
        /** @var StoreGroup $store */
        $store = $storeView->getDataFieldConfig('group_id')['source']->getStoreGroup();
        $this->website = $store->getDataFieldConfig('website_id')['source']->getWebsite();
        $this->setupPaths();
        $this->createWebsiteFolder();
        $this->placeFiles();
        $this->enableWebsiteConfiguration($fixtureFactory);

        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataSet' => 'default', 'data' => ['website_ids' => ['websites' => [$this->website]]]]
        );
        $product->persist();

        $code = $this->website->getCode();
        $productUrl = $_ENV['app_frontend_url'] . "websites/$code/" . $product->getUrlKey() . ".html";
        $browser->open(str_replace("index.php/", "", $productUrl));
        \PHPUnit_Framework_Assert::assertTrue(
            $catalogProductView->getViewBlock()->isVisible(),
            "Searched product is not visible."
        );
    }

    /**
     * Setup paths for assert.
     *
     * @throws \Exception
     * @return void
     */
    protected function setupPaths()
    {
        $code = $this->website->getCode();
        if (isset($_ENV['basedir'])) {
            $this->baseDir = $_ENV['basedir'];
            $this->magentoRoot = isset($_ENV['product_root_dir'])
                ? $_ENV['product_root_dir'] . DIRECTORY_SEPARATOR . $_ENV['instance']
                : $_ENV['basedir'];
            $this->websiteFolder = $this->magentoRoot . DIRECTORY_SEPARATOR . "websites" . DIRECTORY_SEPARATOR . $code;
        } else {
            throw new \Exception("\$_ENV['basedir'] variable should be set in phpunit.xml.");
        }
    }

    /**
     * Create Website folder in magento root.
     *
     * @return void
     */
    protected function createWebsiteFolder()
    {
        $oldmask = umask(0);
        if (!is_dir($this->magentoRoot . DIRECTORY_SEPARATOR . 'websites')) {

            mkdir($this->magentoRoot . DIRECTORY_SEPARATOR . 'websites', 0777);
        }
        mkdir($this->websiteFolder, 0777);
        umask($oldmask);
    }

    /**
     * Place files in created folder in magento root dir.
     *
     * @return void
     */
    protected function placeFiles()
    {
        $htaccessFile = file_get_contents($this->baseDir . DIRECTORY_SEPARATOR .'.htaccess');
        file_put_contents($this->websiteFolder . DIRECTORY_SEPARATOR . ".htaccess", $htaccessFile);
        $indexPhpFile = file_get_contents($this->baseDir . DIRECTORY_SEPARATOR . 'index.php');

        $replace = ["getcwd()", "(\$mageRunCode, \$mageRunType)"];
        $replacement = ["'{$this->magentoRoot}'", "('{$this->website->getCode()}', 'website')"];
        $indexPhpFile = str_replace($replace, $replacement, $indexPhpFile);

        file_put_contents($this->websiteFolder . DIRECTORY_SEPARATOR . "index.php", $indexPhpFile);
    }

    /**
     * Enable website configuration.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    protected function enableWebsiteConfiguration(FixtureFactory $fixtureFactory)
    {
        $code = $this->website->getCode();
        $scope = "web/website/$code/";
        $data = [
            'section' => [
                [
                    'path' => $scope . 'secure/base_link_url',
                    'scope' => $scope,
                    'value' => "{{secure_base_url}}websites/$code/"
                ],
                [
                    'path' => $scope . 'unsecure/base_link_url',
                    'scope' => $scope,
                    'value' => "{{unsecure_base_url}}websites/$code/"
                ]
            ]
        ];

        $fixture = $fixtureFactory->createByCode('configData', ['data' => $data]);
        $fixture->persist();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Product is present on custom website.";
    }
}
