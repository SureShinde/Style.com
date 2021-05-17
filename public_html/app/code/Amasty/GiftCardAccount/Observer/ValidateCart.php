<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountValidator;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;

class ValidateCart implements ObserverInterface
{
    /**
     * @var GiftCardAccountValidator
     */
    private $gCardAccountValidator;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var GiftCardCartProcessor
     */
    private $cardCartProcessor;

    public function __construct(
        GiftCardAccountValidator $gCardAccountValidator,
        Repository $accountRepository,
        GiftCardCartProcessor $cardCartProcessor
    ) {
        $this->gCardAccountValidator = $gCardAccountValidator;
        $this->accountRepository = $accountRepository;
        $this->cardCartProcessor = $cardCartProcessor;
    }

    public function execute(Observer $observer)
    {
        /** @var CartInterface $quote */
        $quote = $observer->getEvent()->getCart()->getQuote();

        if (!$quote->getExtensionAttributes() || !$quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            return;
        }
        $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();

        if (!$this->gCardAccountValidator->isGiftCardApplicableToCart($quote) && $gCardQuote->getGiftCards()) {
            $this->cardCartProcessor->removeAllGiftCardsFromCart($quote);

            return;
        }

        foreach ($gCardQuote->getGiftCards() as $card) {
            $account = $this->accountRepository->getById((int)$card[GiftCardCartProcessor::GIFT_CARD_ID]);

            if (!$this->gCardAccountValidator->validateCode($account, $quote)) {
                $this->cardCartProcessor->removeFromCart($account, $quote);
            }
        }
    }
}
