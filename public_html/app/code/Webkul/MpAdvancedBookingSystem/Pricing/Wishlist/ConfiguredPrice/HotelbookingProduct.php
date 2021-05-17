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
namespace Webkul\MpAdvancedBookingSystem\Pricing\Wishlist\ConfiguredPrice;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Pricing configuration of wishlist product.
 */
class HotelbookingProduct extends AbstractPrice
{
    /**
     * Price type final
     */
    const PRICE_CODE = 'final_price';

    /**
     * @var ItemInterface
     */
    private $item;

    /**
     * Get Configured Price Amount object by price type
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getConfiguredAmount(): \Magento\Framework\Pricing\Amount\AmountInterface
    {
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('virtual_product');
        $product = $customOption ? $customOption->getProduct() : $this->getProduct();
        return $product->getPriceInfo()->getPrice(ConfiguredPriceInterface::CONFIGURED_PRICE_CODE)->getAmount();
    }

    /**
     * Get Configured Regular Price Amount object by price type
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getConfiguredRegularAmount(): \Magento\Framework\Pricing\Amount\AmountInterface
    {
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('virtual_product');
        $product = $customOption ? $customOption->getProduct() : $this->getProduct();
        return $product->getPriceInfo()->getPrice(ConfiguredPriceInterface::CONFIGURED_REGULAR_PRICE_CODE)->getAmount();
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('virtual_product');
        $product = $customOption ? $customOption->getProduct() : $this->getProduct();
        $price = $product->getPriceInfo()->getPrice(self::PRICE_CODE)->getValue();

        return max(0, $price);
    }

    /**
     * @inheritdoc
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }
}
