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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\System as ModifierSystem;

class System
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Magento\Framework\UrlInterface                 $urlBuilder
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data               $helper
     * @param array                                           $productUrls
     */
    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $locator,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        array $productUrls = []
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
    }

    public function afterModifyData(ModifierSystem $subject, $result)
    {
        $typeId = $this->locator->getProduct()->getTypeId();
        if ($typeId == "booking" || $typeId == "hotelbooking") {
            $product = $this->locator->getProduct();
            $id = $product->getId();
            $typeId = $product->getTypeId();
            $storeId = $product->getStoreId();
            $hotelTypeSetId = $this->helper->getProductAttributeSetIdByLabel(
                'Hotel Booking'
            );
            $reloadParameters = [
                'id' => $id,
                'type' => $typeId,
                'store' => $storeId,
            ];
            if ($id) {
                $reloadBaseUrl = 'catalog/product/edit';
            } else {
                $reloadBaseUrl = 'catalog/product/new';
            }
            $result['config']['reloadUrl'] = $this->urlBuilder->getUrl(
                $reloadBaseUrl,
                $reloadParameters
            );
            $result['config']['isBookingType'] = 1;
            $result['config']['isHotelBooking'] = 0;
            if ($typeId == "hotelbooking") {
                $result['config']['isHotelBooking'] = 1;
            }
            $result['config']['hotelTypeSetId'] = $hotelTypeSetId;
        } else {
            $result['config']['isBookingType'] = 0;
        }
        return $result;
    }
}
