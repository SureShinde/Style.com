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

class SalesQuoteAddItem implements ObserverInterface
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
        QuoteCollection $quoteCollection
    ) {
        $this->_messageManager = $messageManager;
        $this->helper = $helper;
        $this->_quote = $quote;
        $this->_quoteCollection = $quoteCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        if ($item->getBuyRequest()) {
            $data = $item->getBuyRequest()->getData();
            $productId = $item->getProductId();
            if ($this->helper->isBookingProduct($productId)) {
                $product = $this->helper->getProduct($productId);
                if ($item->getProductType()=="hotelbooking") {
                    $this->helper->checkIsHotelBookedForDateRange($data, $product, $item);
                } else {
                    $tableAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
                        'Table Booking'
                    );
                    if ($item->getProduct()->getAttributeSetId() == $tableAttrSetId) {
                        $this->helper->checkIsCapacityAvailableForDateRange($data, $product, $item);
                    }
                }
            }
        }
    }
}
