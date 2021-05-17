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
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;
use Webkul\MpAdvancedBookingSystem\Model\Info;

class CheckoutCartProductAddAfterObserver implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var QuoteCollection
     */
    protected $_quoteCollection;

    /**
     * @param CheckoutSession                   $checkoutSession
     * @param RequestInterface                  $request
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper
     * @param ManagerInterface                  $messageManager
     * @param QuoteCollection                   $quoteCollectionFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        RequestInterface $request,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper,
        ManagerInterface $messageManager,
        QuoteCollection $quoteCollectionFactory
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_request = $request;
        $this->_bookingHelper = $bookingHelper;
        $this->_messageManager = $messageManager;
        $this->_quoteCollection = $quoteCollectionFactory;
    }

    /**
     * Checkout cart product add event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->_request->getParams();
        $helper = $this->_bookingHelper;
        $product = $observer->getEvent()->getProduct();
        $item = $observer->getEvent()->getQuoteItem();
        $quoteId = $item->getQuoteId();
        $productType = $product->getTypeId();

        if ($productType == 'booking' && $item->getId()) {
            $productSetId = $product->getAttributeSetId();
            $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
            $rentalAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            if (in_array($productSetId, $allowedAttrSetIDs)) {
                if ($productSetId == $rentalAttrSetId) {
                    $helper->processRentBookingSave($data, $product, $item);
                }
            }
        }
        
        if ($productType == 'booking' && !$item->getId()) {
            $productSetId = $product->getAttributeSetId();
            $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
            $rentalAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            if (in_array($productSetId, $allowedAttrSetIDs)) {
                if ($productSetId == $rentalAttrSetId) {
                    $this->rentTypeBookingAction($data, $quoteId, $product, $item);
                }
            }
        }
        
        if ($productType=="hotelbooking" && $item->getId()) {
            $collection = $this->_quoteCollection->create();
            $bookingQuote = $helper->getDataByField($item->getId(), 'item_id', $collection);
            if ($bookingQuote && $bookingQuote->getItemId()==$item->getId()) {
                $helper->checkItemQtyAvilableForHotel($data, $product, $item, $bookingQuote);
            }
        }
        if ($productType == 'booking' && $item->getId()) {
            $productSetId = $product->getAttributeSetId();
            $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
            $tableAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Table Booking'
            );
            if (in_array($productSetId, $allowedAttrSetIDs)) {
                if ($productSetId==$tableAttrSetId) {
                    $this->tableTypeBookingAction($data, $quoteId, $product, $item);
                }
            }
        }

        if ($productType == 'booking' && $item->getId()) {
            $productSetId = $product->getAttributeSetId();
            $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
            $appointmentAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            if (in_array($productSetId, $allowedAttrSetIDs)) {
                if ($productSetId == $appointmentAttrSetId) {
                    $this->appointmentTypeBookingAction($data, $quoteId, $product, $item);
                }
            }
        }
        if ($productType == 'booking' && $item->getId()) {
            $productSetId = $product->getAttributeSetId();
            $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
            $eventAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Event Booking'
            );
            if (in_array($productSetId, $allowedAttrSetIDs)) {
                if ($productSetId == $eventAttrSetId) {
                    $this->_bookingHelper->processEventBookingSave($data, $product, $item);
                }
            }
        }
    }

    public function rentTypeBookingAction($data, $quoteId, $product, $item)
    {
        $helper = $this->_bookingHelper;
        $productId = $product->getId();
        $rentOpt = $helper->getRentOptions($product);
        if (!empty($rentOpt['choose_rent_type']['option_id']) && !empty($data['booking_date_from'])) {
            $rentType = '';
            $optionId = $rentOpt['choose_rent_type']['option_id'];
            $optionValues = $rentOpt['choose_rent_type']['option_values'];
            if (!empty($data['options'][$optionId]) && !empty($optionValues)) {
                $optionValId = $data['options'][$optionId];
                foreach ($optionValues as $key => $value) {
                    if ($optionValId == $value['option_type_id']) {
                        if ($value['title'] == 'Hourly Basis') {
                            $rentType = Info::RENT_TYPE_HOURLY;
                        } else {
                            $rentType = Info::RENT_TYPE_DAILY;
                        }
                        break;
                    }
                }
            }
            $bookedSlotFromDate = $data['booking_date_from'];
            if ($rentType == Info::RENT_TYPE_HOURLY) {
                $bookedSlotToDate = $data['booking_date_from'];
                $selectedBookingFromTime = '';
                $selectedBookingToTime = '';
            } else {
                $bookedSlotToDate = $data['booking_date_to'];
                $selectedBookingFromTime = date(
                    "h:i a",
                    strtotime($bookedSlotFromDate)
                );
                $selectedBookingToTime = date(
                    "h:i a",
                    strtotime($bookedSlotToDate)
                );
            }
            $selectedBookingFromDate = date(
                "Y-m-d",
                strtotime($bookedSlotFromDate)
            );
            $selectedBookingToDate = date(
                "Y-m-d",
                strtotime($bookedSlotToDate)
            );
            if ($rentType == Info::RENT_TYPE_HOURLY) {
                if (empty($data['slot_day_index'])) {
                    $data['parent_slot_id'] = 0;
                    $data['slot_id'] = 0;
                    $data['slot_day_index'] = 0;
                    $data['booking_from_time'] = 0;
                    $data['booking_to_time'] = 0;
                }
                $parentSlotId = $data['parent_slot_id'];
                $slotId = $data['slot_id'];
                $slotDayIndex = $data['slot_day_index'];
                $slotIdFrom = $data['booking_from_time'];
                $slotIdTo = $data['booking_to_time'];

                $bookingInfo = $helper->getBookingInfo($productId);
                $bookingSlotData = $helper->getJsonDecodedString(
                    $bookingInfo['info']
                );

                $isSlotExisted = 0;
                if (!empty($bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotIdFrom]) &&
                    !empty($bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotIdTo])) {
                    $isSlotExisted = 1;
                }
                if ($data['slot_day_index'] && $isSlotExisted) {
                    $bookingSlotDataArr = $bookingSlotData[$slotDayIndex][$parentSlotId];
                    $slotDataFrom = $bookingSlotDataArr['slots_info'][$slotIdFrom];
                    $slotDataTo = $bookingSlotDataArr['slots_info'][$slotIdTo];
                    if (!empty($slotDataFrom['time']) && !empty($slotDataTo['time'])) {
                        $selectedBookingFromTime = $slotDataFrom['time'];
                        $selectedBookingToTime = $slotDataTo['time'];
                    }
                    $rentPeriodArr = $this->_checkoutSession->getRentPeriod();
                    // number of hours for rent
                    $hourDiff = strtotime($selectedBookingToTime) - strtotime($selectedBookingFromTime);
                    $rentPeriod = round($hourDiff/(60*60));
                    if (!$rentPeriod) {
                        $rentPeriod = 1;
                    }
                    // update item price
                    $price = $helper->getCovertedPrice($item->getProduct()->getFinalPrice());
                    $item->setCustomPrice($price*$rentPeriod);
                    $item->setOriginalCustomPrice($price*$rentPeriod);
                }
            } elseif ($rentType == Info::RENT_TYPE_DAILY) {
                $rentPeriodArr = $this->_checkoutSession->getRentPeriod();
                // number of days for rent
                $dateDiff = strtotime($data['booking_date_to']) - strtotime($data['booking_date_from']);
                $rentPeriod = round($dateDiff/(60*60*24));
                if (!$rentPeriod) {
                    $rentPeriod = 1;
                }
                if (strtotime($data['booking_date_to']) != strtotime($data['booking_date_from'])) {
                    $rentPeriod++;
                }
                // update item price
                $price = $helper->getCovertedPrice($item->getProduct()->getFinalPrice());
                $item->setCustomPrice($price*$rentPeriod);
                $item->setOriginalCustomPrice($price*$rentPeriod);
            }
        }
    }

    public function tableTypeBookingAction($data, $quoteId, $product, $item)
    {
        $helper = $this->_bookingHelper;
        $productId = $product->getId();
        $collection = $this->_quoteCollection->create();
        $bookingQuote = $helper->getDataByField($item->getId(), 'item_id', $collection);
        if ($bookingQuote && $bookingQuote->getItemId()==$item->getId()) {
            $helper->checkItemQtyAvilableForTable($data, $product, $item, $bookingQuote);
        }
    }

    public function appointmentTypeBookingAction($data, $quoteId, $product, $item)
    {
        $helper = $this->_bookingHelper;
        $productId = $product->getId();
        $collection = $this->_quoteCollection->create();
        $bookingQuote = $helper->getDataByField($item->getId(), 'item_id', $collection);
        if ($bookingQuote && $bookingQuote->getItemId()==$item->getId()) {
            $helper->checkItemQtyAvilableForAppointment($data, $product, $item, $bookingQuote);
        }
    }
}
