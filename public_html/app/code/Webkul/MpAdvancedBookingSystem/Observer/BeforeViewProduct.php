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
use Magento\Framework\App\RequestInterface;

class BeforeViewProduct implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @param RequestInterface                          $request
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper
     */
    public function __construct(
        RequestInterface $request,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper
    ) {
        $this->_request = $request;
        $this->_bookingHelper = $bookingHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $data = $this->_request->getParams();
            $productId = $data['id'];
            $this->_bookingHelper->enableOptions($productId);
            $this->_bookingHelper->checkBookingProduct($productId);
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_BeforeViewProduct execute : ".$e->getMessage()
            );
        }
    }
}
