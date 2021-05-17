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

namespace Webkul\MpAdvancedBookingSystem\Pricing\Price;

class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice
{
    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface
     */
    protected $priceResolver;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface                      $saleableItem
     * @param float                                                             $quantity
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface         $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface                 $priceCurrency
     * @param \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface $priceResolver
     */
    public function __construct(
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        $quantity,
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface $priceResolver
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->priceResolver = $priceResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (!isset($this->values[$this->product->getId()])) {
            $this->values[$this->product->getId()] = $this->priceResolver->resolvePrice(
                $this->product
            );
        }

        return $this->values[$this->product->getId()];
    }
}
