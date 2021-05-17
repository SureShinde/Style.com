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

namespace Webkul\MpAdvancedBookingSystem\Model\Config\Source;

/**
 * Used in creating options for getting booking product type value.
 */
class BookingProductType
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $data = [
            'default' => __('Default Booking'),
            'hotel' => __('Hotel Booking'),
            'appointment' => __('Appointment Booking'),
            'event' => __('Event Booking'),
            'rental' => __('Rental Booking'),
            'table' => __('Table Booking')
        ];

        return $data;
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
