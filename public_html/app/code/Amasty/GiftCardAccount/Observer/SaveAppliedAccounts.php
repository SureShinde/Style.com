<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveAppliedAccounts implements ObserverInterface
{
    /**
     * @var GiftCardAccount\Repository
     */
    private $accountRepository;

    public function __construct(GiftCardAccount\Repository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param Observer $observer
     * @return void|null
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getExtensionAttributes() || !$order->getExtensionAttributes()->getAmGiftcardOrder()) {
            return null;
        }

        /** @var GiftCardOrderInterface $gCardOrder */
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();

        try {
            foreach ($gCardOrder->getAppliedAccounts() as $appliedAccount) {
                $this->accountRepository->save($appliedAccount);
            }
        } catch (\Exception $e) {
            null;
        }
    }
}
