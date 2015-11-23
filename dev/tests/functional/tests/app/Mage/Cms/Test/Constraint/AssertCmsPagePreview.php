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

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Browser;
use Mage\Cms\Test\Fixture\CmsPage;
use Magento\Mtf\Constraint\AbstractConstraint;
use Mage\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Mage\Cms\Test\Page\CmsPage as FrontendCmsPage;
use Mage\Cms\Test\Page\Adminhtml\CmsPageEdit;

/**
 * Assert that content of created cms page displayed in main content section and equals passed from fixture.
 */
class AssertCmsPagePreview extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * I-frame selector.
     *
     * @var string
     */
    protected $iFrameSelector = '#preview_iframe';

    /**
     * Assert that content of created cms page displayed in main content section and equals passed from fixture.
     *
     * @param CmsPage $cms
     * @param CmsPageIndex $cmsPageIndex
     * @param FrontendCmsPage $frontendCmsPage
     * @param CmsPageEdit $cmsPageEdit
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CmsPage $cms,
        CmsPageIndex $cmsPageIndex,
        FrontendCmsPage $frontendCmsPage,
        CmsPageEdit $cmsPageEdit,
        Browser $browser
    ) {
        $cmsPageIndex->open();
        $cmsPageIndex->getCmsPageGridBlock()->searchAndOpen(['title' => $cms->getTitle()]);
        $cmsPageEdit->getPageMainActions()->preview();
        $browser->selectWindow();
        $frontendCmsPage->getTemplateBlock()->waitLoader();
        $browser->switchToFrame(new Locator($this->iFrameSelector));
        $element = $browser->find('body');

        $fixtureContent = $cms->getContent();
        \PHPUnit_Framework_Assert::assertContains(
            $fixtureContent['content'],
            $frontendCmsPage->getCmsPageContentBlock()->getPageContent($element),
            'Wrong content is displayed.'
        );
        if ($cms->getContentHeading()) {
            \PHPUnit_Framework_Assert::assertEquals(
                strtolower($cms->getContentHeading()),
                strtolower($frontendCmsPage->getCmsPageContentBlock()->getPageTitle($element)),
                'Wrong title is displayed.'
            );
        }
        if (isset($fixtureContent['widget'])) {
            foreach ($fixtureContent['widget']['preset'] as $widget) {
                \PHPUnit_Framework_Assert::assertTrue(
                    $frontendCmsPage->getCmsPageContentBlock()->isWidgetVisible($widget),
                    "Widget '{$widget['widget_type']}' is not displayed."
                );
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'CMS Page content equals to data from fixture.';
    }
}
