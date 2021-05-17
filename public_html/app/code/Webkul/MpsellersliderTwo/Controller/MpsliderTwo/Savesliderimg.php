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

namespace Webkul\MpsellersliderTwo\Controller\MpsliderTwo;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 *  Webkul MpsellersliderTwo MpsliderTwo Saveslider controller
 */
class Savesliderimg extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var UploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var boolean
     */
    private $error = false;

    /**
     * @var \Webkul\MpsellersliderTwo\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\Marketplace\Model\Seller
     */
    private $sellerModel;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param \Webkul\MpsellersliderTwo\Helper\Data $helper
     * @param \Webkul\Marketplace\Model\Seller $sellerModel
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        Filesystem $filesystem,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        UploaderFactory $fileUploaderFactory,
        \Webkul\MpsellersliderTwo\Helper\Data $helper,
        \Webkul\Marketplace\Model\Seller $sellerModel,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->serialize = $serialize;
        $this->logger = $logger;
        $this->session = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->helper = $helper;
        $this->sellerModel = $sellerModel;
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
     * To save slider images and slider settings in DB.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $count = 0;
        $newimg = [];
        $arrayOfImageName = [];
        if ($this->getRequest()->isPost() && $count == 0) {
            try {
                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $fields = $this->getRequest()->getParams();
                list($data, $errors) = $this->validateData($fields);
                $sellerId = $this->helper->getSellerId();
                if (empty($errors)) {
                    foreach ($fields['img'] as $key => $val) {
                        $newimg[$key] = $val;
                    }
                    $serial = $this->serialize->serialize($newimg);
                    $target = $this->mediaDirectory->getAbsolutePath(
                        'avatar/'.$sellerId.'/'
                    );
                    if (isset($fields['wk_file_count'])) {
                        $wkFileArray = explode(",", $fields['wk_file_count']);
                        $count = $count++;
                        $arrayOfImageName = $this->uploadSliderImages(
                            $wkFileArray,
                            $target,
                            $fields
                        );
                    }
                    $arrayOfImageName = array_filter($arrayOfImageName);
                    if ($this->error == false) {
                        $this->saveSliderImageSettings(
                            $arrayOfImageName,
                            $sellerId,
                            $serial
                        );
                    }
                } else {
                    foreach ($errors as $message) {
                        $this->messageManager->addError($message);
                    }
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger("Controller_MpsliderTwo_Savesliderimg execute : ".$e->getMessage());
                $this->messageManager->addError($e->getMessage());
            }
            $count ++;
        }

        return $this->resultRedirectFactory->create()->setPath(
            '*/*/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * [validateData to validate required fields and value of post data].
     *
     * @return [array] [return an array of errors if any]
     */
    private function validateData($params)
    {
        $errors = [];
        $data = [];
        try {
            if (isset($params['img'])) {
                foreach ($params['img'] as $code => $value) {
                    $filteredData = $this->checkErrors($code, $value);
                    if (isset($filteredData['data']) && $filteredData['data']!=="") {
                        $data[$code] = $value;
                    } elseif (isset($filteredData['error'])
                        && $filteredData['error']!==""
                    ) {
                        $errors[] = $filteredData['error'];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_MpsliderTwo_Savesliderimg_validateData : ".$e->getMessage());
        }
        return [$data,$errors];
    }

    /**
     * checkErrors used to check for errors
     *
     * @param integer $code [contains index]
     * @param string $value [contains parameter value]
     * @return array [return data and error(s), if any]
     */
    private function checkErrors($code, $value)
    {
        $data = '';
        $errors = '';
        switch ($code):
            case 'width':
                if (trim($value) != '' && is_numeric($value)) {
                    $data = $value;
                } else {
                    $errors = __(
                        'Image width field can not contain any space, alphabet or special character'
                    );
                }
                break;
            case 'height':
                if (trim($value) != '' && is_numeric($value)) {
                    $data = $value;
                } else {
                    $errors = __(
                        'Image height field can not contain any space, alphabet or special character'
                    );
                }
                break;
            case 'speed':
                if (trim($value) != '' && is_numeric($value)) {
                    $data = $value;
                } else {
                    $errors = __(
                        'Slider speed field can not contain any space, alphabet or special character'
                    );
                }
                break;
            case 'duration':
                if (trim($value) != '' && is_numeric($value)) {
                    $data = $value;
                } else {
                    $errors = __(
                        'Slider duration field can not contain any space, alphabet or special character'
                    );
                }
        endswitch;

        return ['data' => $data, 'error' => $errors];
    }

    /**
     * uploadSliderImages used to upload images into directory
     *
     * @param array $imageFiles [contains counter for files]
     * @param string $target [contains target to upload file]
     * @return array [returns uploaded files names]
     */
    private function uploadSliderImages($imageFiles, $target, $data)
    {
        $name = [];
        $i = 0;

        if (!empty($imageFiles)
            && isset($imageFiles[0])
            && $imageFiles[0]!==""
            && $imageFiles[0]!==null
        ) {

            foreach ($imageFiles as $wkValue) {
                /**
                 * @var $uploader
                 * \Magento\MediaStorage\Model\File\Uploader
                 */
                $uploader = $this->fileUploaderFactory
                    ->create(
                        ['fileId' => 'sliderimg_'.$wkValue]
                    );

                $image = $uploader->validateFile();

                if (isset($image['tmp_name'])
                    && $image['tmp_name'] !== ''
                    && $image['tmp_name'] !== null
                ) {
                    $imageCheck = getimagesize($image['tmp_name']);

                    if ($imageCheck['mime']) {
                        $image['name'] = str_replace(" ", "_", $image['name']);
                        $imgName = rand(1, 99999).$image['name'];
                        $name[$i]['image'] = $imgName;
                        if (isset($data['sliderimgurl_'.$wkValue])) {
                            $name[$i]['url'] = $data['sliderimgurl_'.$wkValue];
                        } else {
                            $name[$i]['url'] = '#';
                        }

                        $uploader->setAllowedExtensions(
                            ['jpg', 'jpeg', 'gif', 'png']
                        );
                        $uploader->setAllowRenameFiles(true);
                        $result = $uploader->save($target, $imgName);

                        if (isset($result['error'])
                            && $result['error']!==0
                        ) {
                            $this->messageManager->addError(__('%1 Image Not Uploaded', $image['name']));
                            $this->error = true;
                        } else {
                            $name[$i]['image'] = $result['file'];
                        }
                        $i++;
                    } else {
                        $this->messageManager->addError(__('Disallowed file type.'));
                        $this->error = true;
                    }
                }
            }
        }
        return $name;
    }

    /**
     * saveSliderImageSettings used to save slider image settings in DB
     *
     * @param array $imageDataArray [contains files names]
     * @param string $sliderImages [used to implode files names into string]
     * @param integer $sellerId [contains seller id]
     * @param string $serial [contains serialized data]
     */
    private function saveSliderImageSettings(
        $imageDataArray,
        $sellerId,
        $serial
    ) {
        $tempImgArray = [];
        $countImage = count($imageDataArray);
        $sliderImages = '';
        $i = 0;
        $collection = $this->helper->getSellerProfileData('seller_id', $sellerId);

        if ($collection->getSize() > 0) {
            foreach ($collection as $value) {
                $sliderImage = $value->getSliderImgTwo();
                $sellerData = $this->getSliderData($value->getEntityId());

                if (($sliderImage == null || $sliderImage == '')
                    && $countImage > 0
                ) {
                    $sliderImages = $this->serialize->serialize($imageDataArray);
                } else {
                    if ($this->helper->unserializeData($sliderImage)) {
                        $existingSliderImages = $this->helper->unserializeData($sliderImage);
                    } else {
                        $existingSliderImages = explode(',', $sliderImage);
                    }

                    $existingSliderImages = array_filter($existingSliderImages);

                    foreach ($existingSliderImages as $existImages) {
                        if (is_array($existImages) && isset($existImages['image'])) {
                            $tempImgArray[$i]['image'] = $existImages['image'];
                            $tempImgArray[$i]['url'] = $existImages['url'];
                        } else {
                            $tempImgArray[$i]['image'] = $existImages;
                            $tempImgArray[$i]['url'] = '#';
                        }
                        $i++;
                    }
                    $imageDataArray  = array_merge($imageDataArray, $tempImgArray);
                    if ($sellerData && $sellerData->getEntityId()) {
                        $sliderImages = $this->serialize->serialize($imageDataArray);
                    }
                }
                $this->saveSliderData($value->getEntityId(), $sliderImages, $serial);
            }
            $this->messageManager->addSuccess(
                __(
                    'Gallery was successfully saved'
                )
            );
        }
    }

    /**
     * getSliderData used to load seller model
     *
     * @param integer $id [contains auto id of model]
     * @return object
     */
    private function getSliderData($id)
    {
        return $sellerData = $this->sellerModel->load($id);
    }

    /**
     * saveSliderData used to save slider data in model
     *
     * @param integer $id
     * @param string  $sliderImages [contains slider images of seller]
     * @param string  $serial [contains slider settings]
     */
    private function saveSliderData($id, $sliderImages, $serial)
    {
        $sellerData = $this->getSliderData($id);
        $sellerData->setSliderImgTwo($sliderImages);
        $sellerData->setSliderSettingTwo($serial);
        $sellerData->save();
    }
}
