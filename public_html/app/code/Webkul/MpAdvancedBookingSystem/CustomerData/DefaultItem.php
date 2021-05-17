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
namespace Webkul\MpAdvancedBookingSystem\CustomerData;

use Magento\Framework\App\ObjectManager;

class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    /**
     * {@inheritdoc}
     */
    public function doGetItemData()
    {
        $this->helper = ObjectManager::getInstance()->get(\Webkul\MpAdvancedBookingSystem\Helper\Data::class);
        $this->escaper = ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class);
        $imageInitHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $productName = $this->escaper->escapeHtml($this->item->getProduct()->getName());
        $result = [
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'product_image' => [
                'src' => $imageInitHelper->getUrl(),
                'alt' => $imageInitHelper->getLabel(),
                'width' => $imageInitHelper->getWidth(),
                'height' => $imageInitHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
        ];

        if (isset($result['product_id']) && $result['product_id']) {
            if ($this->helper->isBookingProduct($result['product_id'])) {
                $result['is_visible_in_site_visibility'] = false;
            }
        }
        return $result;
    }
}
