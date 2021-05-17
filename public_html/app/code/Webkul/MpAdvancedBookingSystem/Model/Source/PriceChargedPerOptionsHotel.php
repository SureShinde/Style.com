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

namespace Webkul\MpAdvancedBookingSystem\Model\Source;

use Magento\Framework\DataObject;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DB\Ddl\Table;

class PriceChargedPerOptionsHotel extends ShowContactButtonToOptions
{
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            1 => __('Night')
        ];
    }
}
