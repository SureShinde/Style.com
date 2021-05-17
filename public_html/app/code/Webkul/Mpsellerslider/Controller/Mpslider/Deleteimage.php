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
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Webkul Mpsellerslider Mpslider controller
 */

class Deleteimage extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fileSystem;

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
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Mpsellerslider\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Mpsellerslider\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl,
        // \Magento\Framework\Serialize\SerializerInterface $serialize,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->serialize = $serialize;
        $this->session = $customerSession;
        $this->fileSystem = $filesystem;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
        $this->file = $file;
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
            $deleteImages = $params['file'];
            $newSliderImags = '';
            $sellerId = $this->helper->getSellerId();
            $collection = $this->helper->getSellerProfileData('seller_id', $sellerId);
            if ($collection->getSize() > 0) {
                foreach ($collection as $value) {
                    if ($this->helper->unserializeData($value->getSliderImg())) {
                        $sliderImages = $this->helper->unserializeData($value->getSliderImg());
                        $sliderImages = array_filter($sliderImages);
                        $key = array_search($deleteImages, array_column($sliderImages, 'image'));
                    } else {
                        $sliderImages = explode(',', $value->getSliderImg());
                        $sliderImages = array_filter($sliderImages);
                        if (in_array($deleteImages, $sliderImages)) {
                            $key = array_search($deleteImages, $sliderImages);
                        }
                    }

                    if (isset($key) && $key!==false) {
                        unset($sliderImages[$key]);
                    }

                    $avatarDir = $this->fileSystem->getDirectoryRead(
                        DirectoryList::MEDIA
                    )->getAbsolutePath(
                        'avatar/'.$sellerId.'/'.$deleteImages
                    );

                    if ($this->file->fileExists($avatarDir)) {
                        $flag = $this->deleteImage($avatarDir);
                    }

                    $sliderImages = array_filter($sliderImages);
                    $newSliderImags = $this->serialize->serialize($sliderImages);
                    $value->setSliderImg($newSliderImags)->save();

                    if (isset($flag)) {
                        $this->getResponse()->representJson(
                            $this->jsonHelper->jsonEncode(__('Image successfully deleted'))
                        );
                    } else {
                        $this->getResponse()->representJson(
                            $this->jsonHelper->jsonEncode(__('something went wrong'))
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Mpslider_Deleteimage_execute Exception : ".$e->getMessage()
            );
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($e->getMessage())
            );
        }
    }

    /**
     * deleteImage deletes image
     *
     * @param string $path [contains image path]
     * @return object|boolean
     */
    private function deleteImage($path)
    {
        try {
            $directory = $this->fileSystem->getDirectoryWrite(
                DirectoryList::MEDIA
            );
            $result = $directory->delete($directory->getRelativePath($path));
            return $result;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Mpslider_Deleteimage_deleteImage Exception : ".$e->getMessage()
            );
            return false;
        }
    }
}
