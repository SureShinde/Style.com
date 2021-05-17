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
namespace Webkul\MpAdvancedBookingSystem\Model\Product\Type\Hotelbooking;

class Price extends \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price
{
    /**
     * Default action to get price of product
     *
     * @param  Product $product
     * @return float
     */
    public function getPrice($product)
    {
        if (!empty($product)) {
            $simpleProductOption = $product->getCustomOption('virtual_product');
            if (!empty($simpleProductOption)) {
                $simpleProduct = $simpleProductOption->getProduct();
                if (!empty($simpleProduct)) {
                    return $simpleProduct->getPrice();
                }
            }
        }
        return 0;
    }

    /**
     * Get product final price
     *
     * @param  float                          $qty
     * @param  \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getFinalPrice($qty, $product)
    {
        if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }
        if ($product->getCustomOption('virtual_product')
            && $product->getCustomOption('virtual_product')->getProduct()
        ) {
            $finalPrice = parent::getFinalPrice($qty, $product->getCustomOption('virtual_product')->getProduct());
        } else {
            $priceInfo = $product->getPriceInfo();
            $finalPrice = $priceInfo->getPrice('final_price')->getAmount()->getValue();
        }
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }
}
