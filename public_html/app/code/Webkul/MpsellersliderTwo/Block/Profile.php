<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpsellersliderTwo
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpsellersliderTwo\Block;

use Magento\Framework\View\Element\Template\Context;
//use Magento\Framework\Unserialize\Unserialize;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Seller profile collection
 */
class Profile extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpsellersliderTwo to return collection
     */
    private $_sellerList;

    /**
     * @var $mphelper Webkul\Marketplace\Helper\Data
     */
    private $mphelper;

    /**
     * @var $jsonHelper Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var $unserialize Magento\Framework\Unserialize\Unserialize
     */
    private $unserialize;

    /**
     * @var \Webkul\MpsellersliderTwo\Helper\Data
     */
    private $helper;

    /**
     * @param Context           $context
     * @param Unserialize       $unserialize
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Serialize $unserialize,
        \Webkul\MpsellersliderTwo\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->unserialize = $unserialize;
        $this->helper = $helper;
        $this->mphelper     = $mphelper;
        $this->jsonHelper     = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * [getSellerData get seller slider images and settings].
     *
     * @return [array] [return seller slider images and settings]
     */
    public function getSellerData()
    {
        try {
            $imageSettings = [];
            $imageAttributes = [];
            $sliderImagesArray = [];
            $uploadedImages = [];
            $this->helper->checkMpProfileStoreExists($this->helper->getSellerId());
            $data = $this->helper->getSellerProfileData('seller_id', $this->helper->getSellerId());

            if ($data->getSize() > 0) {
                foreach ($data as $seller) {
                    $sliderImages = $seller->getSliderImgTwo();
                    $setting = $seller->getSliderSettingTwo();
                    if ($setting !== '' && $setting !== null) {
                        $imageSettings = $this->unserialize->unserialize($setting);
                        foreach ($imageSettings as $key) {
                            array_push($imageAttributes, $key);
                        }
                    }
                    if ($sliderImages !== '' && $sliderImages !== null) {
                        if ($this->unserialize->unserialize($sliderImages)) {
                            $sliderImagesArray = $this->unserialize->unserialize($sliderImages);
                        } else {
                            $sliderImagesArray = explode(',', $sliderImages);
                        }
                        $uploadedImages = array_filter($sliderImagesArray);
                    }

                    return [
                        'images' => $uploadedImages,
                        'settings' => $imageAttributes,
                    ];
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Block_Profile getSellerData : ".$e->getMessage());
            return false;
        }
    }

    /**
     * [checkIsPartner used to check current user is seller or not]
     *
     * @return [integer]
     */
    public function checkIsPartner()
    {
        try {
            return $this->mphelper->isSeller();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Block_Profile checkIsPartner : ".$e->getMessage());
            return false;
        }
    }

    /**
     * [getSellerId get current user id]
     *
     * @return [integer]
     */
    public function getSellerId()
    {
        try {
            return $this->helper->getSellerId();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Block_Profile getSellerId : ".$e->getMessage());
            return 0;
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
     * [getJsonHelper get json helper]
     *
     * @return [object]
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
