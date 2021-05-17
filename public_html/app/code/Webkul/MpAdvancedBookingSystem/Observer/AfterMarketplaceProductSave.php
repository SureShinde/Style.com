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
namespace Webkul\MpAdvancedBookingSystem\Observer;

class AfterMarketplaceProductSave extends AfterProductSave
{
    /**
     * After save marketplace product event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $helper = $this->_bookingHelper;
            $observerData = $observer->getData();
            $wholeData = [];
            if (!empty($observerData['0'])) {
                $wholeData = $observerData['0'];
            }
            if (!empty($wholeData['id'])) {
                $product = $helper->getProduct($wholeData['id']);
                $productId = $product->getId();
                $productType = $product->getTypeId();

                $data = $this->_request->getParams();
                $files = $this->_request->getFiles();

                if ($productType == "virtual") {
                    $this->checkHotelChildProduct($productId, $data);
                }
                if ($productType != "booking" && $productType != "hotelbooking") {
                    return;
                }
                $productSetId = $product->getAttributeSetId();
                
                $this->saveBooking(
                    $productSetId,
                    $data,
                    $productId,
                    $files
                );
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "MpAdvancedBookingSystem_Observer_AfterMarketplaceProductSave execute : ".$e->getMessage()
            );
        }
    }

    /**
     * saveBooking
     *
     * @param int $productSetId
     * @param array $data
     * @param int $productId
     * @param array $files
     * @return void
     */
    private function saveBooking(
        $productSetId,
        $data,
        $productId,
        $files
    ) {
        $helper = $this->_bookingHelper;
        $product = $helper->getProduct($productId);
        $appointmentAttrSetId = $helper->getProductAttributeSetIdByLabel(
            'Appointment Booking'
        );
        $eventAttrSetId = $helper->getProductAttributeSetIdByLabel(
            'Event Booking'
        );
        $rentalAttrSetId = $helper->getProductAttributeSetIdByLabel(
            'Rental Booking'
        );
        $hotelAttrSetId = $helper->getProductAttributeSetIdByLabel(
            'Hotel Booking'
        );
        $tableAttrSetId = $helper->getProductAttributeSetIdByLabel(
            'Table Booking'
        );
        $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
        if ($productSetId == $appointmentAttrSetId) {
            $this->saveAppointmentBooking($data, $productId, $productSetId);
        } elseif ($productSetId == $eventAttrSetId) {
            $this->saveEventBooking($product, $data, $productId, $productSetId);
        } elseif ($productSetId == $rentalAttrSetId) {
            $this->saveRentalBooking($product, $data, $productId, $productSetId);
        } elseif ($productSetId == $hotelAttrSetId) {
            $this->saveHotelBooking($data, $productId, $productSetId, $files);
        } elseif ($productSetId == $tableAttrSetId) {
            $this->saveTableBooking($data, $productId, $productSetId);
        } elseif (!in_array($productSetId, $allowedAttrSetIDs)) {
            $this->saveDefaultBooking($data, $productId, $productSetId);
        }
    }
}
