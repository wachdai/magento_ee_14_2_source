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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Iphone preview model
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Preview_Iphone extends Mage_XmlConnect_Model_Preview_Abstract
{
    /**
     * Get application banner image url
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getBannerImage()
    {
        $result = array();
        $bannerImages = $this->getImageModel()
            ->getDeviceImagesByType(Mage_XmlConnect_Model_Device_Iphone::IMAGE_TYPE_PORTRAIT_BANNER);
        if (!empty($bannerImages)) {
            $width  = Mage_XmlConnect_Model_Device_Iphone::PREVIEW_PORTRAIT_BANNER_WIDTH;
            $height = Mage_XmlConnect_Model_Device_Iphone::PREVIEW_PORTRAIT_BANNER_HEIGHT;
            foreach ($bannerImages as $banner) {
                if (!isset($banner['image_file'])) {
                    continue;
                }
                $result[] = $this->getImageModel()->getCustomSizeImageUrl($banner['image_file'], $width, $height);
            }
        } else {
            $result[] = $this->getPreviewImagesUrl('banner.png');
        }
        return $result;
    }

    /**
     * Get background image url according device type
     *
     * @return string
     */
    public function getBackgroundImage()
    {
        $configPath = 'conf/body/backgroundImage';
        $imageUrlOrig = $this->getData($configPath);
        if ($imageUrlOrig) {
            $backgroundImage = $imageUrlOrig;
        } else {
            $backgroundImage = $this->getPreviewImagesUrl('background.png');
        }
        return $backgroundImage;
    }
}
