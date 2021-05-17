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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Model\Catalog;

class Product
{
    public function afterGetIsVirtual(\Magento\Catalog\Model\Product $subject, $result)
    {
        if ($subject->getTypeId() == "booking" || $subject->getTypeId() == "hotelbooking") {
            return true;
        }

        return $result;
    }
}
