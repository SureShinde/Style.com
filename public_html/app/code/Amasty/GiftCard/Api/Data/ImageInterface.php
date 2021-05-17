<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api\Data;

interface ImageInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const IMAGE_ID = 'image_id';
    const TITLE = 'title';
    const STATUS = 'status';
    const CODE_POS_X = 'code_pos_x';
    const CODE_POS_Y = 'code_pos_y';
    const CODE_TEXT_COLOR = 'code_text_color';
    const IMAGE_PATH = 'image_path';
    const IS_USER_UPLOAD = 'user_upload';
    /**#@-*/

    /**
     * @return int
     */
    public function getImageId(): int;

    /**
     * @param int $imageId
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setImageId(int $imageId): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setTitle(string $title): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setStatus(int $status): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return string|null
     */
    public function getCodePosX();

    /**
     * @param string|int $codePosX
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setCodePosX($codePosX): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return string|null
     */
    public function getCodePosY();

    /**
     * @param string|null $codePosY
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setCodePosY($codePosY): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return string|null
     */
    public function getCodeTextColor();

    /**
     * @param string $color
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setCodeTextColor(string $color): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return string|null
     */
    public function getImagePath();

    /**
     * @param string|null $imagePath
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setImagePath($imagePath): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @return bool
     */
    public function isUserUpload(): bool;

    /**
     * @param bool $flag
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function setIsUserUpload(bool $flag): \Amasty\GiftCard\Api\Data\ImageInterface;
}
