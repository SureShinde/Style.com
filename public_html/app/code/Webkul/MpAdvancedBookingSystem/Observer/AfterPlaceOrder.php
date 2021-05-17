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

use Magento\Framework\Event\ObserverInterface;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\BookedFactory
     */
    protected $_booked;

    /**
     * @var Webkul\MpAdvancedBookingSystem\Model\InfoFactory
     */
    protected $_info;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\QuoteFactory
     */
    protected $_quote;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data         $bookingHelper
     * @param \Webkul\MpAdvancedBookingSystem\Model\BookedFactory $booked
     * @param \Webkul\MpAdvancedBookingSystem\Model\InfoFactory   $info
     * @param \Webkul\MpAdvancedBookingSystem\Model\QuoteFactory  $quote
     */
    public function __construct(
        \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper,
        \Webkul\MpAdvancedBookingSystem\Model\BookedFactory $booked,
        \Webkul\MpAdvancedBookingSystem\Model\InfoFactory $info,
        \Webkul\MpAdvancedBookingSystem\Model\QuoteFactory $quote
    ) {
        $this->_bookingHelper = $bookingHelper;
        $this->_booked = $booked;
        $this->_info = $info;
        $this->_quote = $quote;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $orderIds = $observer->getEvent()->getData('order_ids');
            $orderId = $orderIds[0];
            $order = $this->_bookingHelper->getOrder($orderId);
            $orderedItems = $order->getAllItems();
            foreach ($orderedItems as $item) {
                $this->setBookedSlotsInfo($item, $order);
            }
            $this->_bookingHelper->clearCache();
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterPlaceOrder_execute Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Set Booking Slots Info
     *
     * @param object $item
     * @param object $order
     */
    public function setBookedSlotsInfo($item, $order)
    {
        try {
            $helper = $this->_bookingHelper;
            $time = $helper->getCurrentTime();
            $orderId = $order->getId();
            $customerId = (int) $order->getCustomerId();
            $customerEmail = $order->getCustomerEmail();
            $quoteItemId = $item->getQuoteItemId();
            $bookingData = $helper->getDetailsByQuoteItemId($quoteItemId);
            $itemId = $item->getId();
            $qty = (int)$item->getQtyOrdered();
            $productId = $item->getProductId();
            if (!$bookingData['error']) {
                $bookingQuoteId = $bookingData['id'];
                $slotId = $bookingData['slot_id'];
                $parentId = $bookingData['parent_slot_id'];
                $slotDayIndex = $bookingData['slot_day_index'];
                $selectedBookingDate = $bookingData['slot_date'];
                $selectedBookingDateTo = $bookingData['to_slot_date'];
                $selectedBookingTime = $bookingData['slot_time'];
                $childProductId = 0;
                if ($item->getProductType()=="hotelbooking") {
                    $bookingFromDate = $bookingToDate = 0;
                    $itemData = $item->getBuyRequest()->getData();
                    $childProductId = $itemData['selected_configurable_option'];
                    if (!empty($bookingDateOptions)) {
                        foreach ($bookingDateOptions as $optionId => $optionValues) {
                            if ($optionValues['title'] == "Booking From") {
                                $bookingFromDate = $optionId;
                            } elseif ($optionValues['title'] == "Booking Till") {
                                $bookingToDate = $optionId;
                            }
                        }
                    }
                    $slotData = [
                        'booking_from' => $selectedBookingDate,
                        'booking_to' => $selectedBookingDateTo
                    ];
                } else {
                    $slotData = $helper->getSlotData(
                        $slotId,
                        $parentId,
                        $productId,
                        $bookingData
                    );
                    $tableAttrSetId = $helper->getProductAttributeSetIdByLabel(
                        'Table Booking'
                    );
                    if ($item->getProduct()->getAttributesetId() == $tableAttrSetId) {
                        $itemData = $item->getBuyRequest()->getData();
                        if (!empty($itemData['charged_per_count'])
                            && $itemData['charged_per_count'] > 1
                        ) {
                            $qty = $qty * $itemData['charged_per_count'];
                        }
                    }
                }
                if (!empty($slotData)) {
                    $rentType = 0;
                    if (!empty($slotData['rent_type'])) {
                        $rentType = $slotData['rent_type'];
                    }
                    $info = [
                        'order_id'       =>  $orderId,
                        'order_item_id'  =>  $itemId,
                        'item_id'        =>  $bookingData['item_id'],
                        'product_id'     =>  $productId,
                        'slot_id'        =>  $slotId,
                        'parent_slot_id' =>  $parentId,
                        'customer_id'    =>  $customerId,
                        'customer_email' =>  $customerEmail,
                        'qty'            =>  $qty,
                        'booking_from'   =>  date(
                            "Y-m-d H:i:s",
                            strtotime($slotData['booking_from'])
                        ),
                        'booking_too'    =>  date(
                            "Y-m-d H:i:s",
                            strtotime($slotData['booking_to'])
                        ),
                        'time'           =>  $time,
                        'slot_day_index' => $slotDayIndex,
                        'slot_date'      => $selectedBookingDate,
                        'slot_time'      => $selectedBookingTime,
                        'rent_type'      => $rentType,
                        'child_product_id'  =>      $childProductId,
                    ];
                    $this->_bookingHelper->logDataInLogger(
                        json_encode($info)
                    );
                    $this->_booked->create()->setData($info)->save();
                    $bookingInfo = $helper->getBookingInfo($productId);
                    if ($bookingInfo['is_booking']) {
                        $attributeSetId = 0;
                        if (!empty($slotData['attribute_set_id'])) {
                            $attributeSetId = $slotData['attribute_set_id'];
                        }
                        $bookingData['qty'] = $qty;
                        $bookingData['product_id'] = $productId;
                        $bookingData['attribute_set_id'] = $attributeSetId;
                        $this->_quote->create()
                            ->load($bookingQuoteId)
                            ->setQty($item->getQty())->save();
                        $this->saveBookingInfoData(
                            $bookingData,
                            $bookingInfo
                        );

                        if ($childProductId) {
                            $bookingInfoData = $helper->getJsonDecodedString($bookingInfo['info']);
                            if (!empty($bookingInfoData[$childProductId])) {
                                $tempQty = $bookingInfoData[$childProductId]['qty'];
                                $helper->setInStock($childProductId, (int)$tempQty);
                                $this->_bookingHelper->logDataInLogger(
                                    'itemQty'.$tempQty
                                );
                            }
                        }

                        $sku = $item->getProduct()->getSku();
                        if ($childProductId) {
                            $sku = $helper->getProduct($childProductId)->getSku();
                        }

                        if ($sku) {
                            $helper->cleanReservationData($sku);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterPlaceOrder_setBookedSlotsInfo Exception : ".$e->getMessage()
            );
        }
    }

    public function saveBookingInfoData($bookingData, $bookingInfo)
    {
        try {
            $helper = $this->_bookingHelper;
            $slotId = $bookingData['slot_id'];
            $parentId = $bookingData['parent_slot_id'];
            $slotDayIndex = $bookingData['slot_day_index'];
            $qty = $bookingData['qty'];
            $productId = $bookingData['product_id'];
            $attributeSetId = $bookingData['attribute_set_id'];
            $appointmentAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            $eventAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Event Booking'
            );
            $rentalAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            $tableAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Table Booking'
            );
            $bookingInfoData = $helper->getJsonDecodedString($bookingInfo['info']);
            $totalSlots = 0;

            if ($attributeSetId == $eventAttrSetId) {
                $product = $helper->getProduct($productId);
                $eventOptions = $helper->getEventOptions($product);
                if (!empty($eventOptions['event_ticket'])) {
                    $bookingOption = $eventOptions['event_ticket'];
                    if ($bookingOption['option_id'] == $parentId) {
                        $slotAvailableQty = 0;
                        $bookingOptionValues = $bookingOption['option_values'];
                        foreach ($bookingOptionValues as $key => $value) {
                            if ($value['option_type_id'] == $slotId) {
                                if (is_object($value)) {
                                    $valueData = $value->getData();
                                } else {
                                    $valueData = $value;
                                }

                                if (is_array($valueData)) {
                                    foreach ($bookingInfoData[0]['values'] as $valueKey => $valueDetails) {
                                        if (isset($valueDetails['option_type_id'])
                                            && $valueDetails['option_type_id'] == $slotId
                                        ) {
                                            $bookingInfoData[0]['values'][$valueKey] = $valueData;
                                            $slotAvailableQty = $valueData['qty'];
                                            $bookingInfoData[0]['values'][$valueKey]['qty']=$slotAvailableQty-$qty;
                                        }
                                    }
                                }
                            }
                        }
                        if ($bookingInfo['total_slots'] >= $qty) {
                            $totalSlots = $bookingInfo['total_slots']-$qty;
                        }
                        $product = $helper->getProduct($productId);
                        $helper->createOption($bookingInfoData, $product);
                        $helper->setInStock($productId, $totalSlots);
                        $helper->updateProduct($productId);
                    }
                }
            } elseif ($attributeSetId == $appointmentAttrSetId) {
                if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
                    $slotAvailableQty = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'];
                    $this->_bookingHelper->logDataInLogger(
                        'slotAvailableQty'.$slotAvailableQty
                    );
                    if ($bookingInfo['total_slots'] >= $qty) {
                        $totalSlots = $bookingInfo['total_slots']-$qty;
                    }
                    $this->_bookingHelper->logDataInLogger(
                        'totalSlots'.$totalSlots
                    );
                // $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'] = $slotAvailableQty - $qty;
                    $helper->setInStock($productId, $totalSlots);
                }
            } elseif ($attributeSetId == $rentalAttrSetId) {
                if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
                    $slotAvailableQty = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'];
                    if ($bookingInfo['total_slots'] >= $qty) {
                        $totalSlots = $bookingInfo['total_slots']-$qty;
                    }
                    $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'] = $slotAvailableQty - $qty;
                    $helper->setInStock($productId, $totalSlots);
                } else {
                    if ($bookingInfo['total_slots'] >= $qty) {
                        $totalSlots = $bookingInfo['total_slots']-$qty;
                    }
                    $helper->setInStock($productId, $totalSlots);
                }
            } elseif ($attributeSetId == $tableAttrSetId) {
                if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
                    $slotAvailableQty = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'];
                    if ($bookingInfo['total_slots'] >= $qty) {
                        $totalSlots = $bookingInfo['total_slots'] - $qty;
                    }
                // $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'] = $slotAvailableQty - $qty;
                    $helper->setInStock($productId, $totalSlots);
                }
            }
            if (empty($bookingInfo['attribute_set_id'])) {
                $totalSlots = $bookingInfo['total_slots'];
            }
            $infoData = [
                'info' => $helper->getJsonEcodedString($bookingInfoData),
                'total_slots' => $totalSlots
            ];
            $this->_info->create()->load($bookingInfo['id'])
                ->addData($infoData)
                ->save();
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterPlaceOrder_saveBookingInfoData Exception : ".$e->getMessage()
            );
        }
    }
}
