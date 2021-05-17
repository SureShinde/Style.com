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

namespace Webkul\MpsellersliderTwo\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Webkul MpsellersliderTwo Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    private $sellerCollection;

    /**
     * @var \Webkul\Marketplace\Model\Seller
     */
    private $sellerModel;

    /**
     * @var \Webkul\MpsellersliderTwo\Logger\Logger
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerCollection
     * @param \Webkul\Marketplace\Model\Seller $sellerModel
     * @param \Webkul\MpsellersliderTwo\Logger\Logger $logger
     * @param \Magento\Framework\Unserialize\Unserialize $unserialize
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Framework\Filesystem $filesystem,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerCollection,
        \Webkul\Marketplace\Model\Seller $sellerModel,
        \Webkul\MpsellersliderTwo\Logger\Logger $logger,
        \Magento\Framework\Unserialize\Unserialize $unserialize
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->sellerCollection = $sellerCollection;
        $this->sellerModel = $sellerModel;
        $this->logger = $logger;
        $this->unserialize = $unserialize;
    }

    public function getSellerId()
    {
        try {
            return $this->helper->getCustomerId();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getSellerId : ".$e->getMessage());
        }
    }

    public function getCurrentStoreId()
    {
        try {
            return $this->helper->getCurrentStoreId();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getCurrentStoreId : ".$e->getMessage());
            return 0;
        }
    }

    public function validateImage($sellerId, $image)
    {
        try {
            $target = $this->mediaDirectory->getAbsolutePath(
                'avatar/' . $sellerId . '/'. $image
            );
            $imageCheck = getimagesize($target);

            if (!empty($imageCheck) && $imageCheck!==false) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data validateImage : ".$e->getMessage());
            return false;
        }
    }

    public function getSellerProfileData($columnName, $columnValue)
    {
        try {
            $data = $this->sellerCollection->create()
                ->addFieldToFilter($columnName, ['eq' => $columnValue])
                ->addFieldToFilter('store_id', $this->getCurrentStoreId());
            if ($data->getSize()<=0) {
                $data = $this->sellerCollection->create()
                    ->addFieldToFilter($columnName, ['eq' => $columnValue])
                    ->addFieldToFilter('store_id', ['eq' => 0]);
            }
            return $data;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getSellerProfileData : ".$e->getMessage());
        }
    }

    public function checkMpProfileStoreExists($sellerId)
    {
        try {
            $data = $this->sellerCollection->create()
                ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                ->addFieldToFilter('store_id', $this->getCurrentStoreId());
            if ($data->getSize() > 0) {
                return true;
            } else {
                $data = $this->sellerCollection->create()
                    ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                    ->addFieldToFilter('store_id', 0)
                    ->getFirstItem();
                $userData = $data->getData();
                if (!empty($userData)) {
                    $userData['store_id'] = $this->getCurrentStoreId();
                    unset($userData['entity_id']);
                    $this->sellerModel->setData($userData)->save();
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data checkMpProfileStoreExists : ".$e->getMessage());
        }
    }

    public function isModuleEnable()
    {
        try {
            return $this->scopeConfig->getValue(
                'marketplace/general_settings/mpsellerslidertwo_enable',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_isModuleEnable Exception : ".$e->getMessage());
            return false;
        }
    }

    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }

    /**
     * unserializeData
     *
     * @param string $data
     * @return array|boolean
     */
    public function unserializeData($data)
    {
        try {
            return $this->unserialize->unserialize($data);
        } catch (\InvalidArgumentException $e) {
            $this->logDataInLogger("Helper_Data_unserializeData : ".$data);
            $this->logDataInLogger("Helper_Data_unserializeData InvalidArgumentException : ".$e->getMessage());
            return false;
        }
    }
}
