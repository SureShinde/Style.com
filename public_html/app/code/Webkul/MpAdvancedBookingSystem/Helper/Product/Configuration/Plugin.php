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
namespace Webkul\MpAdvancedBookingSystem\Helper\Product\Configuration;

class Plugin
{
    /**
     * Initialize dependencies.
     *
     * @param \Webkul\MpBookingSystem\Helper\Data $MpBookingSystemHelper
     */
    public function __construct(
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * Retrieve configuration options for configurable product
     *
     * @param \Magento\Catalog\Helper\Product\Configuration $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     *
     * @return array
     */
    public function aroundGetOptions(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId == "hotelbooking") {
            $attributes = $product->getTypeInstance()->getSelectedAttributesInfo($product);
            return array_merge($attributes, $proceed($item));
        }
        return $proceed($item);
    }

    /**
     * Retrieve custom options for product
     *
     * @param \Magento\Catalog\Helper\Product\Configuration $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     *
     * @return array
     */
    public function aroundGetCustomOptions(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) {
        $productId = $item->getProductId();
        $result = $proceed($item);
        if (!$this->helper->isBookingProduct($productId)) {
            return $result;
        }
        
        if (is_array($result)) {
            foreach ($result as $key => $item) {
                if (isset($item['label'])) {
                    $result[$key]['label'] = __($item['label']);
                }
            }
        }
        return $result;
    }
}
