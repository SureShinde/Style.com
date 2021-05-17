<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsellerslider
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/*Webkul Mpsellerslider Seller Slider Block*/

namespace Webkul\Mpsellerslider\Block;

use Magento\Framework\Unserialize\Unserialize;

/**
 * Seller images collection
 */
class Slider extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    private $mphelper;

    /**
     * @var $unserialize Magento\Framework\Unserialize\Unserialize
     */
    private $unserialize;

    /**
     * @var Webkul\Mpsellerslider\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                 $context
     * @param Unserialize                                                      $unserialize
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Webkul\Mpsellerslider\Helper\Data $helper,
        Unserialize $unserialize
    ) {
        $this->unserialize = $unserialize;
        $this->mphelper = $mphelper;
        $this->helper = $helper;
        parent::__construct($context);
    }
    /**
     * Get Images Collection get All seller slider images and settings for slider]
     *
     * @return [array] [return seller data ]
     */
    public function getImagesCollection()
    {
        try {
            $shopUrl = $this->mphelper->getProfileUrl();
            $imageSettingsArray = [];
            $sliderImagesArray = [];
            $allSliderImages = [];
            if (!$shopUrl) {
                $shopUrl = $this->getRequest()->getParam('shop');
            }
            if ($shopUrl) {
                $data = $this->helper->getSellerProfileData('shop_url', $shopUrl);
                if ($data->getSize() > 0) {
                    foreach ($data as $seller) {
                        $userid = $seller->getSellerId();
                        $sliderData = $this->getSliderData($seller, $allSliderImages, $imageSettingsArray);
                        $allSliderImages = $sliderData[0];
                        $imageSettingsArray = $sliderData[1];
                        /* return seller array data */
                        return [
                            'seller_id' => $userid,
                            'images'    => $allSliderImages,
                            'settings'  => $imageSettingsArray,
                        ];
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Block_Slider getImagesCollection : ".$e->getMessage());
            return false;
        }
    }

    /**
     * [getIsSecure check is secure or not]
     *
     * @return [boolean]
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    /**
     * [getMediaUrl get media url]
     *
     * @return [string]
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * Slider data
     *
     * @param object $seller
     * @param array $allSliderImages
     * @param array $imageSettingsArray
     */
    private function getSliderData($seller, $allSliderImages, $imageSettingsArray)
    {
        $sliderImages  = $seller->getSliderImg();
        $imageSettings = "";
        if ($seller->getSliderSetting()!==""
            && $seller->getSliderSetting()!==null
        ) {
            $imageSettings = $this->unserialize->unserialize(
                $seller->getSliderSetting()
            );
        }

        if (isset($imageSettings)
            && $imageSettings !== ""
            && $imageSettings !== null
        ) {
            foreach ($imageSettings as $key) {
                array_push($imageSettingsArray, $key);
            }
        }
        if (isset($sliderImages)
            && $sliderImages !== ''
            && $sliderImages !== null
        ) {
            if ($this->unserialize->unserialize($sliderImages)) {
                $sliderImagesArray = $this->unserialize->unserialize($sliderImages);
            } else {
                $sliderImagesArray = explode(',', $sliderImages);
            }
            $allSliderImages   = array_filter($sliderImagesArray);
        }
        return [$allSliderImages, $imageSettingsArray];
    }
}
