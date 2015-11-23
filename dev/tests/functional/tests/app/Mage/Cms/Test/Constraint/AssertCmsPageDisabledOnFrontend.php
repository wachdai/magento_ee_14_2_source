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

namespace Mage\Cms\Test\Constraint;

use Magento\Mtf\Client\Browser;
use Mage\Cms\Test\Fixture\CmsPage;
use Magento\Mtf\Constraint\AbstractConstraint;
use Mage\Cms\Test\Page\CmsPage as FrontendCmsPage;

/**
 * Assert that created CMS page with status "disabled" displays with error message on frontend.
 */
class AssertCmsPageDisabledOnFrontend extends AbstractConstraint
{
    /**
     * Text of error message.
     */
    const ERROR_MESSAGE = "WE ARE SORRY, BUT THE PAGE YOU ARE LOOKING FOR CANNOT BE FOUND.";

    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that created CMS page with status "disabled" displays with error message on frontend.
     *
     * @param CmsPage $cms
     * @param FrontendCmsPage $frontendCmsPage
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CmsPage $cms,
        FrontendCmsPage $frontendCmsPage,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $cms->getIdentifier());
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_MESSAGE,
            $frontendCmsPage->getCmsPageContentBlock()->getPageHeadTitle(),
            'Wrong page is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Created CMS page with status "disabled" displays with error message on frontend.';
    }
}
