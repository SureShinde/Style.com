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

class CheckoutCartSaveAfterObserver implements ObserverInterface
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
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        RequestInterface $request,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        QuoteCollection $quoteCollectionFactory,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->helper = $helper;
        $this->quoteCollection = $quoteCollectionFactory;
        $this->cart = $cart;
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
        $rentalAttrSetId = $helper->getProductAttributeSetIdByLabel(
            'Rental Booking'
        );
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $product = $helper->getProduct($productId);
            $itemId = (int) $item->getId();
            $collection = $this->quoteCollection->create();
            $bookingQuote = $helper->getDataByField($itemId, 'item_id', $collection);

            if ($helper->isBookingProduct($productId) && $itemId) {
                $productSetId = $product->getAttributeSetId();
                if (in_array($productSetId, $allowedAttrSetIDs)) {
                    $isThrowError = 0;
                    if ($rentalAttrSetId == $productSetId) {
                        $helper->processRentBookingSave($data, $product, $item, $isThrowError);
                        $this->saveCart();
                    }
                }
            }
        }
    }

    /**
     * saveCart
     *
     * @return void
     */
    private function saveCart()
    {
        $cartQuote = $this->cart->getQuote();
        $cartQuote->setTotalsCollectedFlag(false)->collectTotals();
        $cartQuote->setTotalsCollectedFlag(true);
        $cartQuote->save();
    }
}
