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
namespace Webkul\MpAdvancedBookingSystem\Model\Plugin;

use Webkul\MpAdvancedBookingSystem\Model\Product\Type\Hotelbooking;

/**
 * Class PriceBackend
 *
 *  Make price validation optional for hotelbooking product
 */
class PriceBackend
{
    /**
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Price $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $object
     * @return bool
     */
    public function aroundValidate(
        \Magento\Catalog\Model\Product\Attribute\Backend\Price $subject,
        \Closure $proceed,
        $object
    ) {
        if ($object instanceof \Magento\Catalog\Model\Product
            && $object->getTypeId() == Hotelbooking::TYPE_CODE
        ) {
            return true;
        } else {
            return $proceed($object);
        }
    }
}
