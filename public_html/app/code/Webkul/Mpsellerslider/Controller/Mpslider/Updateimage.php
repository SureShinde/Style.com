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

namespace Webkul\Mpsellerslider\Controller\Mpslider;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;

/**
 * Webkul Mpsellerslider Mpslider controller
 */

class Updateimage extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Webkul\Mpsellerslider\Helper\Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Mpsellerslider\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        // \Magento\Framework\Serialize\SerializerInterface $serialize,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Mpsellerslider\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->serialize = $serialize;
        $this->session = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param  RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * delete images through ajax call from DB
     * and pub/media/avatar/seller_id directory.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $imageName = $params['file'];
            $imageUrl = $params['img_url'];
            $sellerId = $this->helper->getSellerId();
            $collection = $this->helper->getSellerProfileData('seller_id', $sellerId);
            if ($collection->getSize() > 0) {
                foreach ($collection as $value) {
                    $getImages = $value->getSliderImg();
                    $sliderImages = $this->getUpdatedSliderImages(
                        $getImages,
                        $imageName,
                        $imageUrl
                    );
                    $sliderImages = array_filter($sliderImages);
                    $newSliderImags = $this->serialize->serialize($sliderImages);
                    $value->setSliderImg($newSliderImags)->save();

                    $this->getResponse()->representJson(
                        $this->jsonHelper->jsonEncode(__('Image Text successfully updated'))
                    );
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Mpslider_Updateimage_execute Exception : ".$e->getMessage()
            );
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($e->getMessage())
            );
        }
    }

    /**
     * getUpdatedSliderImages
     *
     * @param string $getImages
     * @param string $imageName
     * @param string $imageUrl
     * @return array
     */
    private function getUpdatedSliderImages($getImages, $imageName, $imageUrl)
    {
        if ($this->helper->unserializeData($getImages)) {
            $sliderImages = $this->helper->unserializeData($getImages);
            $sliderImages = array_filter($sliderImages);
            $key = array_search($imageName, array_column($sliderImages, 'image'));
            if ($key!==false) {
                $sliderImages[$key]['url'] = $imageUrl;
            }
        } else {
            $sliderImages = explode(',', $getImages);
            $sliderImages = array_filter($sliderImages);
            if (in_array($imageName, $sliderImages)) {
                $key = array_search($imageName, $sliderImages);
            }
            if (isset($key) && $key!==false) {
                foreach ($sliderImages as $newkey => $newImageFormat) {
                    $sliderImages[$newkey] = [];
                    if ($newkey == $key) {
                        $newImgUrl = $imageUrl;
                    } else {
                        $newImgUrl = '#';
                    }
                    $sliderImages[$newkey]['image'] = $newImageFormat;
                    $sliderImages[$newkey]['url'] = $newImgUrl;
                }
            }
        }
        return $sliderImages;
    }
}
