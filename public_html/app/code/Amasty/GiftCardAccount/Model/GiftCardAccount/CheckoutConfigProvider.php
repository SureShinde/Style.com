<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Magento\Checkout\Model\ConfigProviderInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var GiftCardAccountValidator
     */
    private $giftCardAccountValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        GiftCardAccountValidator $giftCardAccountValidator,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->giftCardAccountValidator = $giftCardAccountValidator;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $config['isGiftCardEnabled'] = $this->giftCardAccountValidator
            ->isGiftCardApplicableToCart($this->checkoutSession->getQuote());

        return $config;
    }
}
