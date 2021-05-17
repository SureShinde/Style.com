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
namespace Webkul\MpAdvancedBookingSystem\Helper;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\App\ObjectManager;

class Options extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Logger\Logger
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Webkul\MpAdvancedBookingSystem\Logger\Logger        $logger
     * @param \Magento\Framework\Stdlib\ArrayManager     $arrayManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\MpAdvancedBookingSystem\Logger\Logger $logger,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->arrayManager = $arrayManager;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get Product Options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array $data
     */
    public function getRentalProductOptions($product)
    {
        $options = [];
        $optionIndex = 0;
        $optionDailyValIndex = '';
        $optionHourlyValIndex = '';
        try {
            $customOptions = $product->getOptions() ?: [];
            /** @var \Magento\Catalog\Model\Product\Option $customOption */
            foreach ($customOptions as $key => $customOption) {
                $customOptionData = $customOption->getData();
                if ($customOptionData['title'] == 'Choose Rent Type') {
                    $optionIndex = $key;
                    $optionId = $customOptionData['option_id'];
                } else {
                    break;
                }
                $customOptionData['is_use_default'] = !$customOption->getData(
                    'store_title'
                );
                $options[$key] = $this->formatPriceByPath(
                    'price',
                    $customOptionData
                );
                $customOptionValues = $customOption->getValues() ?: [];
                $i = 0;
                foreach ($customOptionValues as $customOptionValue) {
                    $customOptionValue->setData(
                        'is_use_default',
                        !$customOptionValue->getData('store_title')
                    );
                    if ($customOptionValue['title'] == 'Daily Basis') {
                        $optionDailyValIndex = $i;
                    } elseif ($customOptionValue['title'] == 'Hourly Basis') {
                        $optionHourlyValIndex = $i;
                    }
                    $i++;
                }
                /** @var \Magento\Catalog\Model\Product\Option $value */
                foreach ($customOptionValues as $customOptionValue) {
                    $options[$key]['values'][] = $this->formatPriceByPath(
                        'price',
                        $customOptionValue->getData()
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                $e->getMessage()
            );
        }

        return [
            'options' => $options,
            'option_index' => $optionIndex,
            'option_daily_val_index' => $optionDailyValIndex,
            'option_hourly_val_index' => $optionHourlyValIndex
        ];
    }

    /**
     * Formated price
     *
     * @param string $path
     * @param array $data
     * @return array $data
     */
    public function formatPriceByPath($path, array $data)
    {
        try {
            $dataVal = $this->arrayManager->get($path, $data);

            if (is_numeric($dataVal)) {
                $data = $this->arrayManager->replace(
                    $path,
                    $data,
                    $this->formatPrice($dataVal)
                );
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                $e->getMessage()
            );
        }

        return $data;
    }

    /**
     * Format price according to the locale of the currency
     *
     * @param mixed $dataVal
     * @return string
     */
    protected function formatPrice($dataVal)
    {
        try {
            if (!is_numeric($dataVal)) {
                return null;
            }

            $store = $this->storeManager->getStore();
            $currency = $this->getLocaleCurrency()->getCurrency(
                $store->getBaseCurrencyCode()
            );
            $dataVal = $currency->toCurrency(
                $dataVal,
                ['display' => \Magento\Framework\Currency::NO_SYMBOL]
            );
        } catch (\Exception $e) {
            $this->logDataInLogger(
                $e->getMessage()
            );
        }
        return $dataVal;
    }

    /**
     * @return \Magento\Framework\Locale\CurrencyInterface
     */
    private function getLocaleCurrency()
    {
        if ($this->localeCurrency === null) {
            $this->localeCurrency = ObjectManager::getInstance()->get(
                CurrencyInterface::class
            );
        }
        return $this->localeCurrency;
    }

    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }
}
