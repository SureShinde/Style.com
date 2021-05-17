<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


use Amasty\GiftCard\Model\Image\Image;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Filesystem $filesystem */
$filesystem = $objectManager->create(Filesystem::class);

$mediaWriter = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
$mediaWriter->create(FileUpload::AMGIFTCARD_IMAGE_MEDIA_PATH . DIRECTORY_SEPARATOR . FileUpload::ADMIN_UPLOAD_PATH);
$absolutePath = $mediaWriter->getAbsolutePath(
    FileUpload::AMGIFTCARD_IMAGE_MEDIA_PATH . DIRECTORY_SEPARATOR . FileUpload::ADMIN_UPLOAD_PATH . DIRECTORY_SEPARATOR
);
$img = imagecreatetruecolor(300, 300);
$color = imagecolorallocate($img, 255, 255, 255);
imagefilledrectangle($img, 0, 0, 300, 300, $color);
imagejpeg($img, $absolutePath . "test_giftcard_image.jpg", 100);

/** @var Image $gCardImage */
$gCardImage = $objectManager->create(Image::class);
$gCardImage->setTitle('Test Image')
    ->setStatus(1)
    ->setImagePath('test_giftcard_image.jpg')
    ->setIsUserUpload(false)
    ->setCodePosX(20)
    ->setCodePosY(20)
    ->setCodeTextColor('FF0000')
    ->save();

$fontPath = $mediaWriter->getAbsolutePath(FileUpload::FONT_FILE_ARIAL);
copy(__DIR__ . '/test_font.ttf', $fontPath);
