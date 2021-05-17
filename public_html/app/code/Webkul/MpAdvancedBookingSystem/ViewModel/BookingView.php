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
namespace Webkul\MpAdvancedBookingSystem\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Checkout\Helper\Cart;
use Webkul\MpAdvancedBookingSystem\Helper\Data;
use Webkul\MpAdvancedBookingSystem\Helper\Customer;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Catalog\Helper\Output as OutputHelper;
use Webkul\MpAdvancedBookingSystem\Helper\Options as OptionsHelper;

class BookingView implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;
    
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Customer
     */
    protected $customerHelper;
    
    public function __construct(
        Http $httpRequest,
        UrlInterface $urlInterface,
        Json $jsonSerializer,
        PricingHelper $pricingHelper,
        Cart $cartHelper,
        Data $helper,
        Customer $customerHelper,
        WishlistHelper $wishlistHelper,
        JsonHelper $jsonHelper,
        MpHelper $mpHelper,
        OutputHelper $outputHelper,
        OptionsHelper $optionsHelper
    ) {
        $this->httpRequest = $httpRequest;
        $this->urlInterface = $urlInterface;
        $this->jsonSerializer = $jsonSerializer;
        $this->pricingHelper = $pricingHelper;
        $this->cartHelper = $cartHelper;
        $this->helper = $helper;
        $this->customerHelper = $customerHelper;
        $this->wishlistHelper = $wishlistHelper;
        $this->jsonHelper = $jsonHelper;
        $this->mpHelper = $mpHelper;
        $this->outputHelper = $outputHelper;
        $this->optionsHelper = $optionsHelper;
    }

    /**
     * Return MpAdvancedBookingSystem Data Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Return MpAdvancedBookingSystem Customer Helper
     *
     * @return object
     */
    public function getCustomerHelper()
    {
        return $this->customerHelper;
    }
    
    /**
     * Return Request Http Object
     *
     * @return object
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }
    
    /**
     * Return Pricing Data Helper
     *
     * @return object
     */
    public function getPricingHelper()
    {
        return $this->pricingHelper;
    }

    /**
     * Return Wishlist Data Helper
     *
     * @return object
     */
    public function getWishlistHelper()
    {
        return $this->wishlistHelper;
    }

    /**
     * Return Json Data Helper
     *
     * @return object
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * Return Marketplace Data Helper
     *
     * @return object
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    /**
     * Return Options Helper
     *
     * @return object
     */
    public function getOptionsHelper()
    {
        return $this->optionsHelper;
    }

    /**
     * Return Catalog Output Helper
     *
     * @return object
     */
    public function getOutputHelper()
    {
        return $this->outputHelper;
    }

    /**
     * Return JSON object from Data Array
     *
     * @param array $data
     * @return object
     */
    public function getJsonEncoded($data)
    {
        return $this->jsonSerializer->serialize($data);
    }

    /**
     * Return Submit Question URL
     *
     * @return string $submitQuestionUrl
     */
    public function getSubmitQuestionUrl()
    {
        $submitQuestionUrl = $this->urlInterface->getUrl(
            "mpadvancebooking/hotelbooking/submitquestion",
            [
                "_secure" => $this->getHttpRequest()->isSecure()
            ]
        );
        return $submitQuestionUrl;
    }

    /**
     * Return Submit Answer URL
     *
     * @return string $submitAnswerUrl
     */
    public function getSubmitAnswerUrl()
    {
        $submitAnswerUrl = $this->urlInterface->getUrl(
            "mpadvancebooking/hotelbooking/submitanswer",
            [
                "_secure" => $this->getHttpRequest()->isSecure()
            ]
        );
        return $submitAnswerUrl;
    }

    /**
     * Return Contact URL
     *
     * @return string $contactUrl
     */
    public function getContactUrl()
    {
        $submitAnswerUrl = $this->urlInterface->getUrl(
            "mpadvancebooking/booking/contact",
            [
                "_secure" => $this->getHttpRequest()->isSecure()
            ]
        );
        return $submitAnswerUrl;
    }

    /**
     * Return Default Booking Product Data
     *
     * @param object $product
     * @return object
     */
    public function getDefaultBookingProductData($product)
    {
        $productId = $product->getId();
        $options = $this->helper->getProductOptions();
        
        $bookingInfo = $this->helper->getBookingInfo($productId);
        
        $data = [
            "slots" => $this->helper->getFormattedSlots($productId),
            "parentId" => $this->helper->getParentSlotId($productId),
            "formKey" => $this->helper->getFormKey(),
            "productId" => $productId,
            "options" => $options,
            "slotsUrl" => $this->urlInterface->getUrl('mpadvancebooking/booking/slots'),
            "cartUrl" => $this->cartHelper->getAddUrl($product),
            "booking_type" => $bookingInfo['type']
        ];
        
        return $this->getJsonEncoded($data);
    }
}
