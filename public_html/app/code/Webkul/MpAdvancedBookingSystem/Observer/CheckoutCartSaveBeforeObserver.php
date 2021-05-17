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
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;

class CheckoutCartSaveBeforeObserver implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helper;

    /**
     * @var QuoteCollection
     */
    private $quoteCollection;

    /**
     * @param CheckoutSession $checkoutSession
     * @param RequestInterface $request
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param QuoteCollection $quoteCollectionFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        RequestInterface $request,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        QuoteCollection $quoteCollectionFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->helper = $helper;
        $this->quoteCollection = $quoteCollectionFactory;
    }

    /**
     * Checkout cart product add event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->request->getParams();
        $helper = $this->helper;
        $quoteId =  $this->checkoutSession->getQuote()->getId();
        $items =  $this->checkoutSession->getQuote()->getAllVisibleItems();
        $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
        $hotelAttrSetId = $helper->getProductAttributeSetIdByLabel(
            'Hotel Booking'
        );
        
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $product = $helper->getProduct($productId);
            $itemId = (int) $item->getId();
            $collection = $this->quoteCollection->create();
            $bookingQuote = $helper->getDataByField($itemId, 'item_id', $collection);

            if ($helper->isBookingProduct($productId) && $itemId && !$bookingQuote) {
                $this->processBooking(
                    $product,
                    $allowedAttrSetIDs,
                    $data,
                    $item,
                    $productId
                );
            } elseif ($bookingQuote && $helper->isBookingProduct($productId)) {
                $bookingQuote->setQty($item->getQty())->save();
                $productSetId = $product->getAttributeSetId();
                if (!in_array($productSetId, $allowedAttrSetIDs)
                    && $productSetId!==$hotelAttrSetId
                    && $itemId
                ) {
                    $helper->processItem($item);
                    $bookingQuote->setQty($item->getQty())->save();
                }
            }
        }
    }

    /**
     * processBooking
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $allowedAttrSetIDs
     * @param array $data
     * @param object $item
     * @param int $productId
     * @return void
     */
    private function processBooking(
        $product,
        $allowedAttrSetIDs,
        $data,
        $item,
        $productId
    ) {
        $helper = $this->helper;
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
        $productSetId = $product->getAttributeSetId();
        if (in_array($productSetId, $allowedAttrSetIDs) || $productSetId==$hotelAttrSetId) {
            $isThrowError = 1;
            if ($appointmentAttrSetId == $productSetId) {
                $helper->processBookingSave($data, $product, $item, $isThrowError);
            } elseif ($eventAttrSetId == $productSetId) {
                $helper->processEventBookingSave($data, $product, $item, $isThrowError);
            } elseif ($rentalAttrSetId == $productSetId) {
                $helper->processRentBookingSave($data, $product, $item, $isThrowError);
            } elseif ($productSetId==$hotelAttrSetId) {
                $helper->processHotelBookingSave($data, $product, $item, $isThrowError);
            } elseif ($productSetId==$tableAttrSetId) {
                $helper->processTableBookingSave($data, $product, $item, $isThrowError);
            }
        } elseif (!empty($data['slot_id'])) {
            $helper->processDefaultSlotData($data, $item, $productId, $itemId);
        }
    }
}
