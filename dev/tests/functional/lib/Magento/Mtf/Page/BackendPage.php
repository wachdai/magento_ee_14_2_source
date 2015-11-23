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

namespace Magento\Mtf\Page;

use Magento\Mtf\Config;
use Magento\Mtf\ObjectManager;
use Mage\Adminhtml\Test\Page\AdminAuthLogin;
use Mage\Adminhtml\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Config\DataInterface;

/**
 * Class for backend pages.
 */
class BackendPage extends Page
{
    /**
     * Admin auth login page.
     *
     * @var AdminAuthLogin
     */
    protected $adminAuthLogin;

    /**
     * Dashboard page.
     *
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * Configuration data.
     *
     * @var Config
     */
    protected $configuration;

    /**
     * @constructor
     * @param DataInterface $configData
     * @param BrowserInterface $browser
     * @param BlockFactory $blockFactory
     * @param Config $configuration
     */
    public function __construct(
        DataInterface $configData,
        BrowserInterface $browser,
        BlockFactory $blockFactory,
        Config $configuration
    ) {
        parent::__construct($configData, $browser, $blockFactory);
        $this->configuration = $configuration;
    }

    /**
     * Init page. Set page url.
     *
     * @return void
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . static::MCA;
    }

    /**
     * Open backend page and log in if needed.
     *
     * @param array $params
     * @return $this
     */
    public function open(array $params = [])
    {
        $admin = [
            'username' => [
                'value' => $this->configuration->getParameter('application/backendLogin')
            ],
            'password' => [
                'value' => $this->configuration->getParameter('application/backendPassword')
            ]
        ];
        $this->adminAuthLogin = ObjectManager::getInstance()->create('Mage\Adminhtml\Test\Page\AdminAuthLogin');
        $this->dashboard = ObjectManager::getInstance()->create('Mage\Adminhtml\Test\Page\Adminhtml\Dashboard');

        if (!$this->dashboard->getAdminPanelHeader()->isVisible()) {
            $this->loginSuperAdmin($admin);
        }
        return parent::open($params);
    }

    /**
     * Log in on backend for super admin.
     *
     * @param array $admin
     * @return void
     */
    protected function loginSuperAdmin(array $admin)
    {
        $this->adminAuthLogin->open();
        $loginBlock = $this->adminAuthLogin->getLoginBlock();
        if ($loginBlock->isVisible()) {
            $loginBlock->loginToAdminPanel($admin);
        }
    }
}
