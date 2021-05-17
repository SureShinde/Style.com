<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Controller\Adminhtml\Hotelbooking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Uploadimage extends Action
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param Context $context
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        Context $context,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->filesystem = $filesystem;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = [
            'message' => __("Icon Uploaded Successfully"),
            'error' => false
        ];
        try {
            if ($this->getRequest()->getParam('isAjax')) {
                $params = $this->getRequest()->getParams();
                $files = $this->getRequest()->getFiles();
                if (!empty($params) && !empty($files) && isset($files['amenity_icon'])) {
                    $productId = $params['product_id'];
                    $optionId = $params['option_id'];
                    $target = $this->_mediaDirectory->getAbsolutePath(
                        'catalog/product/'.$productId.'/'.$optionId.'/'
                    );
                    $removeDir = $this->filesystem->getDirectoryRead(
                        DirectoryList::MEDIA
                    )->getAbsolutePath(
                        'catalog/product/'.$productId.'/'.$optionId.'/'
                    );
                    $this->deleteImage($removeDir);
                    $data = $this->uploadImageToDirectory($target);
                } else {
                    $data['message'] = __('Please correct the data sent.');
                    $data['error'] = true;
                }
            } else {
                $data['message'] = __('Please correct the data sent.');
                $data['error'] = true;
            }
        } catch (\Exception $e) {
            $data['message'] = __('Something went wrong !!!');
            $data['error'] = true;
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
    }

    /**
     * uploadImageToDirectory
     *
     * @param string $target
     * @return array
     */
    public function uploadImageToDirectory($target)
    {
        $error = [
            'message' => __("Icon Uploaded Successfully"),
            'error' => false
        ];
        $uploader = $this->_fileUploaderFactory
            ->create(
                ['fileId' => 'amenity_icon']
            );
        $image = $uploader->validateFile();

        if (isset($image['tmp_name'])
            && $image['tmp_name'] !== ''
            && $image['tmp_name'] !== null
        ) {
            $imageCheck = @getimagesize($image['tmp_name']);

            if ($imageCheck['mime']) {
                $image['name'] = str_replace(" ", "_", $image['name']);
                $imgName = rand(1, 99999).$image['name'];

                $uploader->setAllowedExtensions(
                    ['jpg', 'jpeg', 'gif', 'png']
                );
                $uploader->setAllowRenameFiles(true);
                $result = $uploader->save($target, $imgName);

                if (isset($result['error'])
                    && $result['error']!==0
                ) {
                    $error['error'] = true;
                    $error['message'] = __('%1 Icon Not Uploaded', $image['name']);
                } else {
                    // return $result['file'];
                    $this->messageManager->addSuccess(__('Icon Uploaded Successfully'));
                    return $error;
                }
            } else {
                $error['error'] = true;
                $error['message'] = __('Disallowed file type.');
            }
        } else {
            $error['error'] = true;
            $error['message'] = __('Invalid Image.');
        }
        return $error;
    }

    /**
     * deleteImage
     * deleteImage deletes image
     *
     * @param  string $path [contains path]
     * @return boolean
     */
    public function deleteImage($path)
    {
        try {
            $directory = $this->_mediaDirectory;
            $result = $directory->delete($directory->getRelativePath($path));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
