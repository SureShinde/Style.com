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

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class AssignSeller
{
    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->product = $product;
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * afterCheckFieldStatus
     * runs to change the result of original function for booking products
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterCheckFieldStatus(
        \Webkul\Marketplace\Ui\DataProvider\Product\Form\Modifier\AssignSeller $subject,
        $result
    ) {
        try {
            $params = $this->request->getParams();
            $product = $this->coreRegistry->registry('product');
            $productType = $product->getTypeId();
            if ($productType == "booking" || $productType == "hotelbooking") {
                $productSetId = $product->getAttributeSetId();
                $appointmentAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
                    'Appointment Booking'
                );
                $eventAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
                    'Event Booking'
                );
                $rentalAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
                    'Rental Booking'
                );
                $hotelAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
                    'Hotel Booking'
                );
                $tableAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
                    'Table Booking'
                );

                $allowedBookingTypes = $this->helper->getAllowedBookingProductTypes();
                $allowedBookings = [];
                foreach ($allowedBookingTypes as $type) {
                    $allowedBookings[] = $type['value'];
                }
                $allowedAttrSetIDs = $this->helper->getAllowedAttrSetIDs();

                if ($productSetId == $appointmentAttrSetId && in_array('appointment', $allowedBookings)) {
                    $result = true;
                } elseif ($productSetId == $eventAttrSetId && in_array('event', $allowedBookings)) {
                    $result = true;
                } elseif ($productSetId == $rentalAttrSetId && in_array('rental', $allowedBookings)) {
                    $result = true;
                } elseif ($productSetId == $hotelAttrSetId && in_array('hotel', $allowedBookings)) {
                    $result = true;
                } elseif ($productSetId == $tableAttrSetId && in_array('table', $allowedBookings)) {
                    $result = true;
                } elseif (!in_array($productSetId, $allowedAttrSetIDs) && in_array('default', $allowedBookings)) {
                    $result = true;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Plugin_Ui_afterCheckFieldStatus Exception : ".$e->getMessage()
            );
        }
        return $result;
    }
}
