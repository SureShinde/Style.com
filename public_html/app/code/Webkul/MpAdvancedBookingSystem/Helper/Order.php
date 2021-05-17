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
namespace Webkul\MpAdvancedBookingSystem\Helper;

class Order extends Data
{
    public function saveBookingInfoData($bookingData, $bookingInfo)
    {
        try {
            $slotId = $bookingData['slot_id'];
            $parentId = $bookingData['parent_slot_id'];
            $slotDayIndex = $bookingData['slot_day_index'];
            $qty = $bookingData['qty'];
            $productId = $bookingData['product_id'];
            $attributeSetId = $bookingData['attribute_set_id'];
            $appointmentAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            $eventAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Event Booking'
            );
            $rentalAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            $bookingInfoData = $this->getJsonDecodedString($bookingInfo['info']);
            $totalSlots = 0;
            if ($attributeSetId == $eventAttrSetId && !empty($bookingInfoData[0])) {
                $bookingOption = $bookingInfoData[0];
                if (!empty($bookingOption['option_id']) && !empty($bookingOption['values'])) {
                    if ($bookingOption['option_id'] == $parentId) {
                        $slotAvailableQty = 0;
                        $bookingOptionValues = $bookingOption['values'];
                        foreach ($bookingOptionValues as $key => $value) {
                            if ($value['option_type_id'] == $slotId) {
                                $slotAvailableQty = $value['qty'];
                                $bookingInfoData[0]['values'][$key]['qty']=$slotAvailableQty+$qty;
                            }
                        }
                        $totalSlots = $bookingInfo['total_slots']+$qty;
                        $product = $this->getProduct($productId);
                        $this->createOption($bookingInfoData, $product);
                        $this->setInStock($productId, $totalSlots);
                        $this->updateProduct($productId);
                    }
                }
            } elseif ($attributeSetId == $appointmentAttrSetId) {
                if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
                    $slotAvailableQty = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'];
                    $totalSlots = $bookingInfo['total_slots']+$qty;
                    $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'] = $slotAvailableQty+$qty;
                    $this->setInStock($productId, $totalSlots);
                    $this->updateProduct($productId);
                }
            } elseif ($attributeSetId == $rentalAttrSetId) {
                if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
                    $slotAvailableQty = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'];
                    $totalSlots = $bookingInfo['total_slots']+$qty;
                    $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'] = $slotAvailableQty + $qty;
                    $this->setInStock($productId, $totalSlots);
                    $this->updateProduct($productId);
                } else {
                    $totalSlots = $bookingInfo['total_slots']+$qty;
                    $this->setInStock($productId, $totalSlots);
                    $this->updateProduct($productId);
                }
            }
            $infoData = [
                'info' => $this->getJsonEcodedString($bookingInfoData),
                'total_slots' => $totalSlots
            ];
            $this->_info->create()->load($bookingInfo['id'])
            ->addData($infoData)
            ->save();
        } catch (\Exception $e) {
            $this->logDataInLogger(
                $e->getMessage()
            );
        }
    }
}
