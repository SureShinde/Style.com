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

namespace Webkul\MpAdvancedBookingSystem\Model\Config\Product;

class Price implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get Option Array
     */
    public function toOptionArray($type = '')
    {
        if ($type) {
            return [
                ['value' => 'fixed', 'label' => __('Fixed')]
            ];
        }
        return [
            ['value' => 'fixed', 'label' => __('Fixed')],
            // ['value' => 'percent', 'label' => __('Percent')]
        ];
    }
}
