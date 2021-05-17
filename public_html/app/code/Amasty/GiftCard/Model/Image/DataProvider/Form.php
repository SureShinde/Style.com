<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image\DataProvider;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\Image\ResourceModel\CollectionFactory;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    public function __construct(
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        FileUpload $fileUpload,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->fileUpload = $fileUpload;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        foreach ($this->collection->getData() as $image) {
            $this->loadedData[$image[ImageInterface::IMAGE_ID]] = $this->prepareImageData($image);
        }
        $data = $this->dataPersistor->get(\Amasty\GiftCard\Model\Image\Image::DATA_PERSISTOR_KEY);

        if (!empty($data)) {
            $imageId = isset($data[ImageInterface::IMAGE_ID])
                ? $data[ImageInterface::IMAGE_ID]
                : null;
            $this->loadedData[$imageId] = $data;
            $this->dataPersistor->clear(\Amasty\GiftCard\Model\Image\Image::DATA_PERSISTOR_KEY);
        }

        return $this->loadedData;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareImageData(array $data): array
    {
        if (isset($data[ImageInterface::IMAGE_PATH])) {
            $data['image'] = [
                [
                    'name' => $data[ImageInterface::IMAGE_PATH],
                    'url' => $this->fileUpload->getImageUrl(
                        $data[ImageInterface::IMAGE_PATH]
                    )
                ]
            ];
        }

        return $data;
    }
}
