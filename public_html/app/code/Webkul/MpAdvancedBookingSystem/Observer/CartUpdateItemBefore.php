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
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;

class CartUpdateItemBefore implements ObserverInterface
{
     /**
      * @var \Magento\Framework\Message\ManagerInterface
      */
    protected $_messageManager;

     /**
      * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
      */
    protected $helper;

     /**
      * @var \Webkul\MpAdvancedBookingSystem\Model\QuoteFactory
      */
    protected $_quote;

     /**
      * @var QuoteCollection
      */
    protected $_quoteCollection;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Webkul\MpAdvancedBookingSystem\Model\QuoteFactory $quote,
        QuoteCollection $quoteCollection,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
    ) {
        $this->_messageManager = $messageManager;
        $this->helper = $helper;
        $this->_quote = $quote;
        $this->_quoteCollection = $quoteCollection;
        $this->_eavAttribute = $eavAttribute;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $info = $observer->getEvent()->getInfo()->getData();
        $cart = $observer->getEvent()->getCart();
        $quote = $cart->getQuote();
        $cartQtyArr = [];
        $tableAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
            'Table Booking'
        );
        if ($info) {
            $totalCartQtyForTable = 0;
            foreach ($quote->getAllVisibleItems() as $item) {
                $productId = $item->getProductId();
                $productAttrSetId = $item->getProduct()->getAttributeSetId();
                if (array_key_exists($item->getId(), $info)
                    && $this->helper->isBookingProduct($productId)
                ) {
                    if ($item->getProductType()=="hotelbooking") {
                        $requestData = $item->getBuyRequest()->getData();
                        $product = $this->helper->getProduct($productId);
                        $bookedData = $this->helper->getBookedHotelDates($product);
                        $availableHotelDates = $this->helper->getAvailableHotelDates($product);

                        $bookingDateOptions = $this->helper->getHotelBookingDateOptions($product);
                        $bookingFromDate = $bookingToDate = 0;

                        if (!empty($bookingDateOptions)) {
                            foreach ($bookingDateOptions as $optionId => $optionValues) {
                                if ($optionValues['title'] == "Booking From") {
                                    $bookingFromDate = $optionId;
                                } elseif ($optionValues['title'] == "Booking Till") {
                                    $bookingToDate = $optionId;
                                }
                            }
                        }

                        $selectedBookingDateFrom = strtotime($requestData['options'][$bookingFromDate]);
                        $selectedBookingDateTo = strtotime($requestData['options'][$bookingToDate]);
                        $errorMessage = __(
                            'Room(s) are not available during %1 to %2 for %3',
                            $requestData['options'][$bookingFromDate],
                            $requestData['options'][$bookingToDate],
                            $product->getName()
                        );

                        if (!empty($requestData['selected_configurable_option'])) {
                            $childProductId = $requestData['selected_configurable_option'];

                            if (isset($bookedData[$requestData['selected_configurable_option']])
                                && !empty($bookedData[$requestData['selected_configurable_option']])
                            ) {
                                $datesArr = $bookedData[$childProductId]['booked_dates'];
                                
                                $array = [];
                                foreach ($datesArr as $bookedDate => $qtyAvailable) {
                                    $bookedDatesStr = strtotime($bookedDate);
                                    if ($bookedDatesStr >= $selectedBookingDateFrom
                                        && $bookedDatesStr <= $selectedBookingDateTo
                                    ) {
                                        $array[] = $qtyAvailable;
                                    }
                                }
                                if (count($array)>0) {
                                    $actualQtyAvailable = min($array);
                                } else {
                                    if (isset($availableHotelDates[$childProductId])) {
                                        $actualQtyAvailable = $availableHotelDates[$childProductId];
                                    } else {
                                        $actualQtyAvailable = $this->helper->getStockData(
                                            $childProductId
                                        )->getQty();
                                    }
                                }
                            } else {
                                if (isset($availableHotelDates[$childProductId])) {
                                    $actualQtyAvailable = $availableHotelDates[$childProductId];
                                } else {
                                    $actualQtyAvailable = $this->helper->getStockData(
                                        $childProductId
                                    )->getQty();
                                }
                            }
                            
                            if ($actualQtyAvailable > 0) {
                                $errorMessage = __(
                                    'Only %1 Room(s) are available during %2 to %3 for %4',
                                    (int)$actualQtyAvailable,
                                    $requestData['options'][$bookingFromDate],
                                    $requestData['options'][$bookingToDate],
                                    $product->getName()
                                );
                            }
                            $textMsg = "";
                            if (isset($requestData['super_attribute'])) {
                                $textMsg = __(" with");
                                foreach ($requestData['super_attribute'] as $attrId => $attrOptionId) {
                                    $attrData = $this->_eavAttribute->load($attrId);
                                    $optionText = $attrData->getSource()->getOptionText($attrOptionId);
                                    if ($attrData && $optionText) {
                                        $textMsg .= __(
                                            " %1 : %2, ",
                                            $attrData->getFrontendLabel(),
                                            $optionText
                                        );
                                    }
                                }
                            }
                            if ($textMsg!=="") {
                                $errorMessage .= $textMsg;
                            }

                            $dateDiff = $selectedBookingDateTo + $selectedBookingDateFrom;
                            if (isset($cartQtyArr[$productId][$childProductId][$dateDiff])) {
                                $totalCartQty = $cartQtyArr[$productId][$childProductId][$dateDiff]['total_cart_qty'];
                                $tempActualQty = $cartQtyArr[$productId][$childProductId][$dateDiff]['actual_qty'];
                                
                                if ($totalCartQty + $info[$item->getId()]['qty']  > $tempActualQty) {
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        __($errorMessage)
                                    );
                                } else {
                                    $cartQtyArr[$productId][$childProductId][$dateDiff]["total_cart_qty"] +=
                                        $info[$item->getId()]['qty'];
                                }
                            } else {
                                if ($info[$item->getId()]['qty']  > $actualQtyAvailable) {
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        __($errorMessage)
                                    );
                                }
                                $cartQtyArr[$productId][$childProductId][$dateDiff] = [
                                    "actual_qty" => $actualQtyAvailable,
                                    "new_qty" => $info[$item->getId()]['qty'],
                                    "total_cart_qty" => $info[$item->getId()]['qty'],
                                ];
                            }
                        }
                    } elseif ($productAttrSetId == $tableAttrSetId) {
                        $requestData = $item->getBuyRequest()->getData();
                        $data = $this->helper->getAvailableSlotQty($productId, $item->getId());
                        if (!empty($data['qty'])) {
                            if (!empty($requestData['charged_per_count']) && $requestData['charged_per_count'] > 1) {
                                $requestedTotalQty = $requestData['charged_per_count'] * $info[$item->getId()]['qty'];
                            } else {
                                $requestedTotalQty = $info[$item->getId()]['qty'];
                            }
                            $totalCartQtyForTable += $requestedTotalQty;
                            if ($totalCartQtyForTable > $data['qty']) {
                                $errorMessage = __("Maximum %1 guests are allowed", $data['qty']);
                                throw new \Magento\Framework\Exception\LocalizedException(
                                    $errorMessage
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
