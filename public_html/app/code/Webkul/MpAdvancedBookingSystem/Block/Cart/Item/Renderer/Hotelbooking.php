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
namespace Webkul\MpAdvancedBookingSystem\Block\Cart\Item\Renderer;

/**
 * Shopping cart item render block for hotelbooking products.
 */
class Hotelbooking extends \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable
{
    /**
     * getChildProduct
     * Get item hotelbooking child product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getChildProduct()
    {
        if ($childProduct = $this->getItem()->getOptionByCode('virtual_product')) {
            return $childProduct->getProduct();
        }
        return $this->getProduct();
    }
}
