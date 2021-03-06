<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model;

use Amasty\GiftCardAccount\Model\Config\Source\CheckoutPosition;
use Amasty\GiftCardAccount\Model\Config\Source\CheckoutViewType;

class ConfigProvider extends \Amasty\GiftCard\Model\ConfigProvider
{
    /**#@+
     * Constants defined for xpath of system configuration
     */
    const XPATH_CHECKOUT_POSITION = 'gift_card_account/checkout_position';
    const XPATH_CHECKOUT_VIEW = 'gift_card_account/checkout_view_type';
    /**#@-*/

    /**
     * @param null $storeId
     * @return int
     */
    public function getCouponCheckoutPosition($storeId = null): int
    {
        return (int)$this->getValue(self::XPATH_CHECKOUT_POSITION, $storeId)
            ?? CheckoutPosition::CHECKOUT_DISCOUNTS;
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getCouponCheckoutView($storeId = null): int
    {
        return (int)$this->getValue(self::XPATH_CHECKOUT_VIEW, $storeId)
            ?? CheckoutViewType::DROPDOWN;
    }
}
