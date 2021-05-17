<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Magento\Framework\Model\AbstractModel;

class Image extends AbstractModel implements ImageInterface
{
    const DATA_PERSISTOR_KEY = 'amgcard_image';

    protected function _construct()
    {
        $this->_init(ResourceModel\Image::class);
        $this->setIdFieldName(ImageInterface::IMAGE_ID);
    }

    public function getImageId(): int
    {
        return (int)$this->_getData(ImageInterface::IMAGE_ID);
    }

    public function setImageId(int $imageId): ImageInterface
    {
        return $this->setData(ImageInterface::IMAGE_ID, (int)$imageId);
    }

    public function getTitle(): string
    {
        return $this->_getData(ImageInterface::TITLE);
    }

    public function setTitle(string $title): ImageInterface
    {
        return $this->setData(ImageInterface::TITLE, $title);
    }

    public function getStatus(): int
    {
        return (int)$this->_getData(ImageInterface::STATUS);
    }

    public function setStatus(int $status): ImageInterface
    {
        return $this->setData(ImageInterface::STATUS, (int)$status);
    }

    public function getCodePosX()
    {
        return $this->_getData(ImageInterface::CODE_POS_X);
    }

    public function setCodePosX($codePosX): ImageInterface
    {
        return $this->setData(ImageInterface::CODE_POS_X, $codePosX);
    }

    public function getCodePosY()
    {
        return $this->_getData(ImageInterface::CODE_POS_Y);
    }

    public function setCodePosY($codePosY): ImageInterface
    {
        return $this->setData(ImageInterface::CODE_POS_Y, $codePosY);
    }

    public function getCodeTextColor()
    {
        return $this->_getData(ImageInterface::CODE_TEXT_COLOR);
    }

    public function setCodeTextColor(string $color): ImageInterface
    {
        return $this->setData(ImageInterface::CODE_TEXT_COLOR, $color);
    }

    public function getImagePath()
    {
        return $this->_getData(ImageInterface::IMAGE_PATH);
    }

    public function setImagePath($imagePath): ImageInterface
    {
        return $this->setData(ImageInterface::IMAGE_PATH, $imagePath);
    }

    public function isUserUpload(): bool
    {
        return (bool)$this->_getData(ImageInterface::IS_USER_UPLOAD);
    }

    public function setIsUserUpload(bool $flag): ImageInterface
    {
        return $this->setData(ImageInterface::IS_USER_UPLOAD, (bool)$flag);
    }
}
