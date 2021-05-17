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
namespace Webkul\MpAdvancedBookingSystem\Model\Product\Type\Booking;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Default action to get price of product
     *
     * @param  Product $product
     * @return float
     */
    public function getPrice($product)
    {
        return $product->getData('price');
    }
}
