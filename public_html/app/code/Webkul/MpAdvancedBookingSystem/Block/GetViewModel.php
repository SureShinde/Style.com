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
namespace Webkul\MpAdvancedBookingSystem\Block;

/**
 * MpAdvancedBookingSystem GetViewModel Block
 */
class GetViewModel extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\ViewModel\BookingView
     */
    protected $bookingViewModel;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpAdvancedBookingSystem\ViewModel\BookingView $bookingViewModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpAdvancedBookingSystem\ViewModel\BookingView $bookingViewModel,
        array $data = []
    ) {
        $this->bookingViewModel = $bookingViewModel;
        parent::__construct($context, $data);
    }

    /**
     * Get MpAdvancedBookingSystem View Model
     *
     * @return object \Webkul\MpAdvancedBookingSystem\ViewModel\BookingView
     */
    public function getBookingViewModel()
    {
        return $this->bookingViewModel;
    }
}
