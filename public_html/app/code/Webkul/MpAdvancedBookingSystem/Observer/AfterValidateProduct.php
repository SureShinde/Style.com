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
namespace Webkul\MpAdvancedBookingSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterValidateProduct implements ObserverInterface
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * After validate product event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productType = $product->getTypeId();
        $productSetId = $product->getAttributeSetId();
        $eventAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
            'Event Booking'
        );
        if ($productType == "booking" && $productSetId == $eventAttrSetId) {
            $this->validateEventDate($product);
        }

        $defaultAttrSetId = $this->helper->getProductAttributeSetIdByLabel(
            'Default'
        );
        $productData = $this->request->getParams();
        if ($productType == "booking" && $productSetId == $defaultAttrSetId) {
            $this->validateDefaultBooking($productData);
        }
    }

    /**
     * validateEventDate validate event date range
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    private function validateEventDate($product)
    {
        $throw = false;
        try {
            if ($product->getEventDateTo()) {
                if ($product->getEventDateTo() <= $product->getEventDateFrom()) {
                    $throw = true;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_AfterValidateProduct_validateEventDate Exception : ".$e->getMessage()
            );
        }
        if ($throw) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Make sure the Event To Date is later than or the same as the Event From Date.')
            );
        }
    }

    /**
     * validateDefaultBooking
     *
     * @param array $productData
     * @return void
     */
    private function validateDefaultBooking($productData)
    {
        $throw = false;
        try {
            if (!empty($productData['info'])) {
                foreach ($productData['info'] as $day => $data) {
                    $throw = $this->validateDefaultBookingTime($data);
                    if ($throw === true) {
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_AfterValidateProduct_validateDefaultBooking Exception : ".$e->getMessage()
            );
        }
        if ($throw) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Make sure the End Time is later than the Start Time for the enabled days.')
            );
        }
    }

    private function validateDefaultBookingTime($data)
    {
        if ((($data['start_hour'] > $data['end_hour'])
            || ($data['start_hour'] == $data['end_hour'] && $data['start_minute'] >= $data['end_minute']))
        && ($data['status'] == "1")
        ) {
            return true;
        }
        return false;
    }
}
