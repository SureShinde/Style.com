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

class BeforeViewCart implements ObserverInterface
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(\Webkul\MpAdvancedBookingSystem\Helper\Data $helper)
    {
        $this->_bookingHelper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->_bookingHelper->checkStatus();
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_BeforeViewCart execute : ".$e->getMessage()
            );
        }
    }
}
