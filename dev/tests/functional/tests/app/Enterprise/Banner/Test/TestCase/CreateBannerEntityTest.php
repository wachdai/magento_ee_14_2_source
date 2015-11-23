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

namespace Enterprise\Banner\Test\TestCase;

use Enterprise\Banner\Test\Fixture\Banner;
use Enterprise\Banner\Test\Page\Adminhtml\BannerIndex;
use Enterprise\Banner\Test\Page\Adminhtml\BannerNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\ObjectManager;

/**
 * Preconditions:
 * 1. Create customer segment.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Go to CMS -> Banners.
 * 3. Click "Add Banner" button.
 * 4. Fill data according to dataSet.
 * 5. Perform all assertions.
 *
 * @group CMS_Content_(PS)
 * @ZephyrId MPERF-6865
 */
class CreateBannerEntityTest extends Injectable
{
    /**
     * Banner index page.
     *
     * @var BannerIndex
     */
    protected $bannerIndex;

    /**
     * Banner new page.
     *
     * @var BannerNew
     */
    protected $bannerNew;

    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Website name.
     *
     * @var string
     */
    protected $websiteName;

    /**
     * Inject pages.
     *
     * @param BannerIndex $bannerIndex
     * @param BannerNew $bannerNew
     * @param ObjectManager $objectManager
     * @return void
     */
    public function __inject(BannerIndex $bannerIndex, BannerNew $bannerNew, ObjectManager $objectManager)
    {
        $this->bannerIndex = $bannerIndex;
        $this->bannerNew = $bannerNew;
        $this->objectManager = $objectManager;
    }

    /**
     * Create banner.
     *
     * @param Banner $banner
     * @return void
     */
    public function test(Banner $banner)
    {
        // Steps
        $this->bannerIndex->open();
        $this->bannerIndex->getGridPageActions()->addNew();
        $this->bannerNew->getBannerForm()->fill($banner);
        $this->bannerNew->getFormPageActions()->save();

        // Prepare data for tier down
        $this->websiteName = explode('/', $banner->getDataFieldConfig('store_contents')['source']->getStore())[0];
    }

    /**
     * Delete website.
     *
     * @return void
     */
    public function tearDown()
    {
        if (!empty($this->websiteName)) {
            $this->objectManager->create(
                'Mage\Adminhtml\Test\TestStep\DeleteWebsiteStep',
                ['websitesNames' => [$this->websiteName]]
            )->run();
        }
    }
}
