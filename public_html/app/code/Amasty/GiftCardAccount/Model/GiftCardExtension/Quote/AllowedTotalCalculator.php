<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Quote;

use Magento\Framework\DataObject;

class AllowedTotalCalculator
{
    /**
     * @var bool
     */
    private $isTaxAllowed = false;

    /**
     * @var bool
     */
    private $isShippingAllowed = false;

    public function __construct(
        \Amasty\GiftCard\Model\ConfigProvider $configProvider
    ) {
        $this->isShippingAllowed = $configProvider->isShippingPaidAllowed();
        $this->isTaxAllowed = $configProvider->isTaxPaidAllowed();
    }

    /**
     * @param DataObject $from
     *
     * @return float|string
     */
    public function getAllowedSubtotal(DataObject $from)
    {
        if (($this->isTaxAllowed) && ($this->isShippingAllowed)) {
            return $from->getSubtotal()
                + $from->getTaxAmount()
                + $from->getDiscountAmount()
                + $from->getShippingAmount()
                + $from->getDiscountTaxCompensationAmount();
        } elseif ((!$this->isTaxAllowed) && ($this->isShippingAllowed)) {
            return $from->getSubtotalWithDiscount()
                + $from->getDiscountTaxCompensationAmount()
                + $from->getShippingAmount()
                + $from->getShippingDiscountTaxCompensationAmount();
        } elseif (($this->isTaxAllowed) && (!$this->isShippingAllowed)) {
            return $from->getSubtotalWithDiscount()
                + $from->getDiscountTaxCompensationAmount()
                + $from->getShippingDiscountAmount()
                + $from->getTaxAmount()
                + $from->getShippingDiscountTaxCompensationAmount();
        } else {
            return $from->getSubtotalWithDiscount()
                + $from->getDiscountTaxCompensationAmount()
                + $from->getShippingDiscountAmount();
        }
    }

    /**
     * @param DataObject $from
     *
     * @return float|string
     */
    public function getAllowedBaseSubtotal(DataObject $from)
    {
        if (($this->isTaxAllowed) && ($this->isShippingAllowed)) {
            return $from->getBaseSubtotal()
                + $from->getBaseTaxAmount()
                + $from->getBaseDiscountAmount()
                + $from->getBaseShippingAmount()
                + $from->getBaseDiscountTaxCompensationAmount();
        } elseif ((!$this->isTaxAllowed) && ($this->isShippingAllowed)) {
            return $from->getBaseSubtotalWithDiscount()
                + $from->getBaseDiscountTaxCompensationAmount()
                + $from->getBaseShippingAmount()
                + $from->getBaseShippingDiscountTaxCompensationAmount();
        } elseif (($this->isTaxAllowed) && (!$this->isShippingAllowed)) {
            return $from->getBaseSubtotalWithDiscount()
                + $from->getBaseDiscountTaxCompensationAmount()
                + $from->getBaseShippingDiscountAmount()
                + $from->getBaseTaxAmount()
                + $from->getBaseShippingDiscountTaxCompensationAmount();
        } else {
            return $from->getBaseSubtotalWithDiscount()
                + $from->getBaseDiscountTaxCompensationAmount()
                + $from->getBaseShippingDiscountAmount();
        }
    }
}
