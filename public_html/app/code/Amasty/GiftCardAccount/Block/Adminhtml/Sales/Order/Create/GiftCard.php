<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Adminhtml\Sales\Order\Create;

use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Magento\Framework\View\Element\Template;

class GiftCard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $sessionQuote;

    public function __construct(
        Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * @return array
     */
    public function getGiftCards(): array
    {
        $quote = $this->sessionQuote->getQuote();

        if (!$quote->getExtensionAttributes() || ! $quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            return [];
        }
        $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();
        $cards = $gCardQuote->getGiftCards();

        return array_column($cards, GiftCardCartProcessor::GIFT_CARD_CODE);
    }
}
