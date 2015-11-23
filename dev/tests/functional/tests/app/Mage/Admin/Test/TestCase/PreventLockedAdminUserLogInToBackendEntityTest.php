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

namespace Mage\Admin\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Mage\Adminhtml\Test\Page\AdminAuthLogin;
use Mage\Admin\Test\Fixture\User;

/**
 * Preconditions:
 * 1. Create admin user.
 * 2. Setup configuration.
 *
 * Steps:
 * 1. Try to log In 3 times with wrong credentials from Test Data.
 * 2. Try to Log In as Admin user which locked with correct credentials.
 * 3. Perform assertions.
 *
 * @group ACL_(MX)
 * @ZephyrId MPERF-7131
 */
class PreventLockedAdminUserLogInToBackendEntityTest extends Injectable
{
    /**
     * Admin auth login page.
     *
     * @var AdminAuthLogin
     */
    protected $adminAuthLogin;

    /**
     * Create custom admin user and setup configuration.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $adminUser = $fixtureFactory->createByCode('user', ['dataSet' => 'custom_admin']);
        $adminUser->persist();
        $this->objectManager->create(
            'Mage\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'max_admin_login_failures']
        )->run();

        return ['adminUser' => $adminUser];
    }

    /**
     * Injection page.
     *
     * @param AdminAuthLogin $adminAuthLogin
     * @return void
     */
    public function __inject(AdminAuthLogin $adminAuthLogin)
    {
        $this->adminAuthLogin = $adminAuthLogin;
    }

    /**
     * Run prevent locked admin user log in to backend entity test.
     *
     * @param User $adminUser
     * @param int $countRetries
     * @return void
     */
    public function test(User $adminUser, $countRetries)
    {
        $this->adminAuthLogin->open();
        // Steps:
        $userName = $adminUser->getUsername();
        for ($i = 1; $i <= $countRetries; $i++) {
            $this->login(['username' => $userName, 'password' => '123123qq']);
        }
        $this->login(['username' => $userName, 'password' => $adminUser->getPassword()]);
    }

    /**
     * Log in to backend.
     *
     * @param array $adminData
     * @return void
     */
    protected function login(array $adminData)
    {
        $this->adminAuthLogin->getLoginBlock()->loginToAdminPanel($adminData);
    }

    /**
     * Setup default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Mage\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'max_admin_login_failures', 'rollback' => true]
        )->run();
    }
}
