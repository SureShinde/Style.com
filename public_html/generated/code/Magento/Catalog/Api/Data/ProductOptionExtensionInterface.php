<?php
namespace Magento\Catalog\Api\Data;

/**
 * ExtensionInterface class for @see \Magento\Catalog\Api\Data\ProductOptionInterface
 */
interface ProductOptionExtensionInterface extends \Magento\Framework\Api\ExtensionAttributesInterface
{
    /**
     * @return \Magento\Catalog\Api\Data\CustomOptionInterface[]|null
     */
    public function getCustomOptions();

    /**
     * @param \Magento\Catalog\Api\Data\CustomOptionInterface[] $customOptions
     * @return $this
     */
    public function setCustomOptions($customOptions);

    /**
     * @return \Magento\Downloadable\Api\Data\DownloadableOptionInterface|null
     */
    public function getDownloadableOption();

    /**
     * @param \Magento\Downloadable\Api\Data\DownloadableOptionInterface $downloadableOption
     * @return $this
     */
    public function setDownloadableOption(\Magento\Downloadable\Api\Data\DownloadableOptionInterface $downloadableOption);

    /**
     * @return \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface[]|null
     */
    public function getConfigurableItemOptions();

    /**
     * @param \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface[] $configurableItemOptions
     * @return $this
     */
    public function setConfigurableItemOptions($configurableItemOptions);

    /**
     * @return \Magento\Bundle\Api\Data\BundleOptionInterface[]|null
     */
    public function getBundleOptions();

    /**
     * @param \Magento\Bundle\Api\Data\BundleOptionInterface[] $bundleOptions
     * @return $this
     */
    public function setBundleOptions($bundleOptions);

    /**
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface|null
     */
    public function getAmGiftcardOptions();

    /**
     * @param \Amasty\GiftCard\Api\Data\GiftCardOptionInterface $amGiftcardOptions
     * @return $this
     */
    public function setAmGiftcardOptions(\Amasty\GiftCard\Api\Data\GiftCardOptionInterface $amGiftcardOptions);
}
