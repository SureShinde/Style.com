<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Model\Source;

class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{

    const STRIPE_ACTION_AUTHORIZE = 'stripe_authorize';
    const STRIPE_ACTION_AUTHORIZE_CAPTURE = 'stripe_authorize_capture';

    /**
     * Possible actions on order place
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::STRIPE_ACTION_AUTHORIZE,
                'label' => __('Authorize'),
            ],
            [
                'value' => self::STRIPE_ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture'),
            ]
        ];
    }
}
