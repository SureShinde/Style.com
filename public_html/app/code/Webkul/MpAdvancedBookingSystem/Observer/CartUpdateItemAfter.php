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

class CartUpdateItemAfter implements ObserverInterface
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface   $request
     */
    public function __construct(
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->_request->getParams();
        $info = $observer->getEvent()->getInfo()->getData();
        $cart = $observer->getEvent()->getCart();
        $quote = $cart->getQuote();
        if (!empty($data['item_id']) && !empty($data['item_qty'])) {
            if (!empty($info[$data['item_id']])) {
                $itemId = $data['item_id'];
                $item = $quote->getItemById($itemId);
                if ($item) {
                    $productId = $item->getProductId();
                    if ($this->helper->isBookingProduct($productId)) {
                        $this->helper->processItem(
                            $item
                        );
                    }
                }
            }
        }
    }
}
