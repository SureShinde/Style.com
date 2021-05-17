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

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory as InfoCollection;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Slot\CollectionFactory as SlotCollection;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Booked\CollectionFactory as BookedCollection;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\CollectionFactory as QuestionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollection;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Eav\Model\Config as EavConfig;
use Webkul\MpAdvancedBookingSystem\Model\Info;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product\Attribute\Repository as ProductAttributeRepository;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\MpAdvancedBookingSystem\Model\ProductFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Catalog\Model\Product\OptionFactory;
use Webkul\MpAdvancedBookingSystem\Model\QuoteFactory;
use Magento\Framework\App\Cache\ManagerFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Webkul\MpAdvancedBookingSystem\Model\Source\PriceChargedPerOptionsTable;
use Webkul\MpAdvancedBookingSystem\Model\Config\Source\BookingProductType;
use Magento\Catalog\Block\Product\View\Options;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\CleanupReservationData;
use Webkul\Marketplace\Helper\Data as marketplaceHelper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    public $dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    public $dayLabelsFull = [
        '1'=>'Monday',
        '2'=>'Tuesday',
        '3'=>'Wednesday',
        '4'=>'Thursday',
        '5'=>'Friday',
        '6'=>'Saturday',
        '7'=>'Sunday'
    ];

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSizeService;
    protected $marketplaceHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ManagerInterface $messageManager
     * @param FormKey $formKey
     * @param ProductRepositoryInterface $productFactory
     * @param ProductFactory $bookingProductFactory
     * @param OrderFactory $order
     * @param Cart $cart
     * @param OptionFactory $option
     * @param PricingHelper $pricingHelper
     * @param Options $optionsBlock
     * @param ProductCollection $productCollectionFactory
     * @param InfoCollection $infoCollectionFactory
     * @param SlotCollection $slotCollectionFactory
     * @param BookedCollection $bookedCollectionFactory
     * @param QuoteCollection $quoteCollectionFactory
     * @param SetFactory $attributeSetFactory
     * @param JsonHelper $jsonHelper
     * @param StockRegistryInterface $stockRegistry
     * @param TimezoneInterface $timezoneInterface
     * @param \Webkul\MpAdvancedBookingSystem\Logger\Logger $logger
     * @param CheckoutSession $checkoutSession
     * @param QuoteFactory $quote
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param EavConfig $eavConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductAttributeRepository $productAttributeRepository
     * @param \Magento\Framework\Filesystem $filesystem
     * @param ManagerFactory $cacheManagerFactory
     * @param RegionFactory $regionFactory
     * @param PriceChargedPerOptionsTable $pricesChargedPerOptionsTable
     * @param RegionCollection $regionCollectionFactory
     * @param \Magento\Framework\Filesystem\Io\File $filesystemFile
     * @param \Magento\Framework\File\Size $fileSize
     * @param BookingProductType $bookingTypes
     * @param QuestionCollection $questionCollection
     * @param RequestQuantityProcessor $quantityProcessor
     * @param CleanupReservationData $cleanupReservation
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ManagerInterface $messageManager,
        FormKey $formKey,
        ProductRepositoryInterface $productFactory,
        ProductFactory $bookingProductFactory,
        OrderFactory $order,
        Cart $cart,
        OptionFactory $option,
        PricingHelper $pricingHelper,
        Options $optionsBlock,
        ProductCollection $productCollectionFactory,
        InfoCollection $infoCollectionFactory,
        SlotCollection $slotCollectionFactory,
        BookedCollection $bookedCollectionFactory,
        QuoteCollection $quoteCollectionFactory,
        SetFactory $attributeSetFactory,
        JsonHelper $jsonHelper,
        StockRegistryInterface $stockRegistry,
        TimezoneInterface $timezoneInterface,
        \Webkul\MpAdvancedBookingSystem\Logger\Logger $logger,
        CheckoutSession $checkoutSession,
        QuoteFactory $quote,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        EavConfig $eavConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        ProductAttributeRepository $productAttributeRepository,
        \Magento\Framework\Filesystem $filesystem,
        ManagerFactory $cacheManagerFactory,
        RegionFactory $regionFactory,
        PriceChargedPerOptionsTable $pricesChargedPerOptionsTable,
        RegionCollection $regionCollectionFactory,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        \Magento\Framework\File\Size $fileSize,
        BookingProductType $bookingTypes,
        QuestionCollection $questionCollection,
        RequestQuantityProcessor $quantityProcessor,
        CleanupReservationData $cleanupReservation,
        marketplaceHelper $marketplaceHelper
    ) {
        $this->_request = $context->getRequest();
        $this->_messageManager = $messageManager;
        $this->_formKey = $formKey;
        $this->_product = $productFactory;
        $this->_bookingProduct = $bookingProductFactory;
        $this->_order = $order;
        $this->_cart = $cart;
        $this->_option = $option;
        $this->pricingHelper = $pricingHelper;
        $this->optionsBlock = $optionsBlock;
        $this->_productCollection = $productCollectionFactory;
        $this->_infoCollection = $infoCollectionFactory;
        $this->_slotCollection = $slotCollectionFactory;
        $this->_bookedCollection = $bookedCollectionFactory;
        $this->_quoteCollection = $quoteCollectionFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_stockRegistry = $stockRegistry;
        $this->timezone = $timezoneInterface;
        $this->logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->_quote = $quote;
        $this->_storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->eavConfig = $eavConfig;
        $this->httpContext = $httpContext;
        $this->_customerSession = $customerSession;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->filesystem = $filesystem;
        $this->cacheManager = $cacheManagerFactory;
        $this->regionFactory = $regionFactory;
        $this->pricesChargedPerOptionsTable = $pricesChargedPerOptionsTable;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->_filesystemFile = $filesystemFile;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->fileSizeService = $fileSize;
        $this->bookingTypes = $bookingTypes;
        $this->questionCollection = $questionCollection;
        $this->cart = $cart;
        $this->quantityProcessor = $quantityProcessor;
        $this->cleanupReservation = $cleanupReservation;
        $this->marketplaceHelper = $marketplaceHelper;
        parent::__construct($context);
    }

    /**
     * Get Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        try {
            return $this->_formKey->getFormKey();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getFormKey Exception : ".$e->getMessage());
        }
    }

    /**
     * Get Cart
     *
     * @return object
     */
    public function getCart()
    {
        try {
            $cartModel = $this->_cart;
            return $cartModel;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCart Exception : ".$e->getMessage());
        }
    }

    /**
     * getSerializedString
     *
     * @param array $array
     * @return string
     */
    public function getSerializedString($array)
    {
        try {
            return @serialize($array);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getSerializedString Exception : ".$e->getMessage());
        }
    }

    /**
     * Get Current Product Id
     *
     * @param int $type [optional]
     *
     * @return int
     */
    public function getProductId($type = 0)
    {
        try {
            $id = (int) $this->_request->getParam('id');
            if ($type > 1) {
                $id = (int) $this->_request->getParam('product_id');
            }
            return $id;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getProductId Exception : ".$e->getMessage());
        }
    }

    /**
     * Get All Parameters
     *
     * @return int
     */
    public function getParams()
    {
        try {
            return $this->_request->getParams();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getParams Exception : ".$e->getMessage());
        }
    }

    /**
     * Get Product
     *
     * @param int $productId [optional]
     *
     * @return object
     */
    public function getProduct($productId = 0)
    {
        try {
            if ($productId == 0) {
                $productId = $this->getProductId();
            }
            return $this->_product->getById($productId);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getProduct Exception : ".$e->getMessage());
        }
    }

    /**
     * Check Slot Quantities are Available or Not
     */
    public function checkStatus()
    {
        try {
            $cartModel = $this->getCart();
            $quote = $cartModel->getQuote();
            $flag = false;
            foreach ($quote->getAllVisibleItems() as $item) {
                $flag = $this->processItem($item);
            }
            if ($flag || !count($quote->getAllVisibleItems())) {
                $this->getCart()->save();
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_checkStatus Exception : ".$e->getMessage());
        }
    }

    /**
     * Process Quote Item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    public function processItem($item)
    {
        try {
            $this->getCurrentTime();
            $productId = $item->getProductId();
            if ($this->isBookingProduct($productId)) {
                $itemId = $item->getId();
                $data = $this->getAvailableSlotQty($productId, $itemId);
                $itemCustomOptions = $item->getProduct()->getTypeInstance(true)
                ->getOrderOptions(
                    $item->getProduct()
                );
                $itemOptionFromDate = '';
                $itemOptionToDate = '';

                if (!empty($itemCustomOptions['options'])) {
                    $itemOptionDate = '';
                    $itemOptionSlot = '';
                    foreach ($itemCustomOptions['options'] as $itemCustomOption) {
                        if ($itemCustomOption['label'] == 'Booking Date') {
                            $itemOptionDate = $itemCustomOption['option_value'];
                        } elseif ($itemCustomOption['label'] == 'Booking Slot') {
                            $itemOptionSlot = $itemCustomOption['option_value'];
                        } elseif ($itemCustomOption['label'] == 'Booking From') {
                            $itemOptionFromDate = $itemCustomOption['option_value'];
                        } elseif ($itemCustomOption['label'] == 'Booking Till') {
                            $itemOptionToDate = $itemCustomOption['option_value'];
                        } elseif ($itemCustomOption['label'] == 'Event From') {
                            $itemOptionFromDate = $itemCustomOption['option_value'];
                            $timeZone = date('T', strtotime($itemOptionToDate));
                            $itemOptionFromDate = str_replace('-', '', $itemOptionFromDate);
                            $itemOptionFromDate = str_replace(' '.$timeZone, '', $itemOptionFromDate);
                        } elseif ($itemCustomOption['label'] == 'Event To') {
                            $itemOptionToDate = $itemCustomOption['option_value'];
                            $timeZone = date('T', strtotime($itemOptionToDate));
                            $itemOptionToDate = str_replace('-', '', $itemOptionToDate);
                            $itemOptionToDate = str_replace(' '.$timeZone, '', $itemOptionFromDate);
                        } elseif ($itemCustomOption['label'] == 'Rent From') {
                            $itemOptionFromDate = $itemCustomOption['option_value'];
                        } elseif ($itemCustomOption['label'] == 'Rent To') {
                            $itemOptionToDate = $itemCustomOption['option_value'];
                        }
                    }

                    $appointmentType = $this->getProductAttributeSetIdByLabel(
                        'Appointment Booking'
                    );
                    if (isset($data['attribute_set_id']) && $data['attribute_set_id'] == $appointmentType) {
                        $itemOptionDate = $itemCustomOptions['info_buyRequest']['booking_date'];
                        $itemOptionSlot = $itemCustomOptions['info_buyRequest']['booking_time'];
                    }

                    $date = \DateTime::createFromFormat('d F, Y', $itemOptionDate);
                    if ($itemOptionDate) {
                        $tempDate = \DateTime::createFromFormat('d F, Y', $itemOptionDate);
                        if ($tempDate) {
                            $itemOptionDate = $tempDate->format('d-m-Y');
                        } else {
                            $itemOptionDate = date('d-m-Y', strtotime($itemOptionDate));
                        }
                        $itemOptionSlot = date('H:i', strtotime($itemOptionSlot));
                        $fromDateStamp = strtotime($itemOptionDate.' '.$itemOptionSlot);
                        $itemOptionFromDate = date('d-m-Y h:i a', $fromDateStamp);
                        $itemOptionToDate = date('d-m-Y h:i a', $fromDateStamp);
                    } else {
                        $itemOptionFromDate = str_replace(',', '', $itemOptionFromDate);
                        $itemOptionToDate = str_replace(',', '', $itemOptionToDate);
                    }
                }
                $rentType = 0;
                if (!empty($data['rent_type'])) {
                    $rentType = $data['rent_type'];
                }
                if ($data && !empty($data) && isset($data['qty'])) {
                    $requestedQty = $item->getQty();
                    $qty = $data['qty'];

                    $appointmentAttrSetId = $this->getProductAttributeSetIdByLabel(
                        'Appointment Booking'
                    );
                    if (!empty($data['attribute_set_id']) && $data['attribute_set_id']==$appointmentAttrSetId) {
                        $appointmentProduct = $this->getProduct($productId);
                        $data['prevent_scheduling_before'] = $appointmentProduct->getPreventSchedulingBefore();
                    }

                    $flag = $this->isBookingExpired($data);
                    if ($flag) {
                        $this->_messageManager->addError(__('Booking Time Expired'));
                        if ($itemId) {
                            $item->delete();
                        } else {
                            $this->getCart()->removeItem($itemId)->save();
                        }
                        return true;
                    }

                    if (strtotime($itemOptionFromDate) != strtotime($data['booking_from'])) {
                        $bookedDateTimeFormatted = date(
                            "d M, Y h:i a",
                            strtotime($data['booking_from'])
                        );
                        $this->_messageManager->addError(
                            __(
                                'Booking is not available for %1 for dates %2.',
                                $item->getName(),
                                $bookedDateTimeFormatted
                            )
                        );
                        if ($itemId) {
                            $item->delete();
                        } else {
                            $this->getCart()->removeItem($itemId)->save();
                        }
                        return true;
                    }
                    if ($rentType) {
                        $rentPeriod = 0;
                        // for rent type boking
                        $rentPeriodArr = $this->_checkoutSession->getRentPeriod();
                        if ($rentType == Info::RENT_TYPE_HOURLY) {
                            // number of hours for rent
                            $hourDiff = strtotime($data['to_booking_slot']) - strtotime($data['booking_slot']);
                            $rentPeriod = round($hourDiff/(60*60));
                        } elseif ($rentType == Info::RENT_TYPE_DAILY) {
                            // number of days for rent
                            $dateDiff = strtotime($data['to_booking_date']) - strtotime($data['booking_date']);
                            $rentPeriod = round($dateDiff/(60*60*24));
                            $rentPeriod++;
                        }
                        if (!$rentPeriod) {
                            $rentPeriod = 1;
                        }
                        // update rent product price
                        $price = $this->getCovertedPrice($item->getProduct()->getFinalPrice());
                        $item->setCustomPrice($price*$rentPeriod);
                        $item->setOriginalCustomPrice($price*$rentPeriod)->save();
                        $this->_checkoutSession->getQuote()->collectTotals();
                        // $this->_checkoutSession->getQuote()->collectTotals()->save();
                    }

                    if ($qty && $requestedQty > $qty) {
                        $item->setQty($qty)->save();
                        $bookedDateTimeFormatted = date(
                            "d M, Y h:i a",
                            strtotime($data['booking_from'])
                        );
                        if ($rentType) {
                            $toTimeFormated = date(
                                "d M, Y h:i a",
                                strtotime($data['booking_to'])
                            );
                            if ($qty) {
                                $errorMessage = __(
                                    'Only %1 quantity is available for %2 for dates %3 to %4.',
                                    $qty,
                                    $item->getName(),
                                    $bookedDateTimeFormatted,
                                    $toTimeFormated
                                );
                            } else {
                                $errorMessage = __(
                                    '%1 is not available for dates %2 to %3.',
                                    $item->getName(),
                                    $bookedDateTimeFormatted,
                                    $toTimeFormated
                                );
                            }
                        } else {
                            if ($qty) {
                                $errorMessage = __(
                                    'Only %1 quantity is available for %2 for slot %3.',
                                    $qty,
                                    $item->getName(),
                                    $bookedDateTimeFormatted
                                );
                            } else {
                                $errorMessage = __(
                                    '%1 is not available for slot %2.',
                                    $item->getName(),
                                    $bookedDateTimeFormatted
                                );
                            }
                        }
                        $this->_checkoutSession->getQuote()->setHasError(true);
                        $this->_messageManager->addError($errorMessage);
                    }

                    if ($qty <= 0) {
                        if ($itemId) {
                            $item->delete();
                        } else {
                            $this->getCart()->removeItem($itemId)->save();
                        }
                        return true;
                    }
                } elseif ($item->getProductType()=="hotelbooking") {
                    $data = $item->getBuyRequest()->getData();
                    if ($data['hotel_qty'] && $data['hotel_qty'] > 1) {
                        $price = $item->getProduct()->getFinalPrice();
                        $item->setCustomPrice($price*$data['hotel_qty']);
                        $item->setOriginalCustomPrice($price*$data['hotel_qty'])->save();
                        $this->_checkoutSession->getQuote()->collectTotals()->save();
                    }
                } else {
                    $this->_messageManager->addError(__('Booking Time Expired'));
                    if ($itemId) {
                        $item->delete();
                    } else {
                        $this->getCart()->removeItem($itemId)->save();
                    }
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_processItem Exception : ".$e->getMessage());
        }
    }

    /**
     * isBookingExpired
     *
     * @param array $data
     * @return boolean
     */
    public function isBookingExpired($data)
    {
        try {
            if (!empty($data['rent_type']) && $data['rent_type'] == Info::RENT_TYPE_DAILY) {
                if (strtotime(date('Y-m-d')) == strtotime($data['booking_date'])) {
                    return false;
                }
            }
            $appointmentAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            if ($this->getCurrentTime(true) >= strtotime($data['booking_from'])) {
                return true;
            } else {
                if (!empty($data['attribute_set_id']) && $data['attribute_set_id']==$appointmentAttrSetId) {
                    $currentTime = date('Y-m-d H:i:s', $this->getCurrentTime(true));
                    $newtimestamp = strtotime($currentTime.' + '.$data['prevent_scheduling_before'].' minute');
                    if ($newtimestamp >= strtotime($data['booking_from'])) {
                        return true;
                    }
                }
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_isBookingExpired Exception : ".$e->getMessage());
        }
    }

    /**
     * Get Full Action Name
     *
     * @return string
     */
    public function getFullActionName()
    {
        try {
            return $this->_request->getFullActionName();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getFullActionName Exception : ".$e->getMessage());
        }
    }

    /**
     * Check Whether It is Product Page or Not
     *
     * @return boolean
     */
    public function isBookingProductPage()
    {
        try {
            if ($this->getFullActionName() == 'catalog_product_view') {
                $productId = $this->_request->getParam('id');
                if ($this->isBookingProduct($productId)) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_isBookingProductPage Exception : ".$e->getMessage());
            return false;
        }
    }

    /**
     * Check Whether Product is Booking Type or Not
     *
     * @param int     $productId
     * @param boolean $useCollection [optional]
     *
     * @return boolean
     */
    public function isBookingProduct($productId, $useCollection = false)
    {
        try {
            $isProduct =  false;
            if ($useCollection) {
                $collection = $this->_productCollection->create();
                $collection->addFieldToFilter('entity_id', $productId);
                foreach ($collection as $item) {
                    $isProduct = true;
                    $product =  $item;
                    break;
                }

                if (!$isProduct) {
                    return false;
                }
            } else {
                $product = $this->getProduct($productId);
            }

            $productType = $product->getTypeId();
            if ($productType == 'booking' || $productType == 'hotelbooking') {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_isBookingProduct Exception : ".$e->getMessage());
            return false;
        }
    }

    /**
     * Check Cart Configure is Allowed or Not
     *
     * @return boolean
     */
    public function canConfigureCart()
    {
        try {
            $productId = $this->getProductId(1);
            if ($this->isBookingProduct($productId)) {
                return true;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_canConfigureCart Exception : ".$e->getMessage()
            );
        }
        return false;
    }

    /**
     * Get Product's Options
     *
     * @param int $productId [optional]
     *
     * @return json
     */
    public function getProductOptions($productId = '')
    {
        try {
            $array = [];
            $product = $this->getProduct($productId);
            foreach ($product->getOptions() as $option) {
                $optionId = $option->getId();
                $optionTitle = $option->getTitle();
                $array[] = ['id' => $optionId, 'title' => $optionTitle];
            }

            return $array;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getProductOptions Exception : ".$e->getMessage());
            return [];
        }
    }

    /**
     * Get Booking Type
     *
     * @param int $productId
     *
     * @return int
     */
    public function getBookingType($productId)
    {
        try {
            $bookingInfo = $this->getBookingInfo($productId);
            return $bookingInfo['type'];
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getBookingType Exception : ".$e->getMessage());
        }
    }

    /**
     * Get Dropdown For Day Select
     *
     * @param string $name  [optional]
     * @param string $value [optional]
     *
     * @return html
     */
    public function getDaySelectHtml($name = '', $value = '', $isFront = false)
    {
        try {
            $htmlClass = "admin__control-select wk-day-select";
            if ($isFront) {
                $htmlClass = "wk-day-select select";
            }
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $html = "";
            if ($isFront) {
                $html .= "<select class='".$htmlClass."' name='".$name."'>";
            } else {
                $html .= "<select data-form-part='product_form' class='".$htmlClass."' name='".$name."'>";
            }

            foreach ($days as $key => $day) {
                if ($value == strtolower($day)) {
                    $html .= "<option selected value='".strtolower($day)."'>".__($day)."</option>";
                } else {
                    $html .= "<option value='".strtolower($day)."'>".__($day)."</option>";
                }
            }

            $html .= "</select>";
            return $html;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getDaySelectHtml Exception : ".$e->getMessage());
            return "";
        }
    }

    /**
     * Get Slots of Booking Product
     *
     * @param int $productId
     * @param int $parentId  [optional]
     *
     * @return array
     */
    public function getSlots($productId, $parentId = 0)
    {
        try {
            $info = [];
            $bookingInfo = $this->getBookingInfo($productId);
            if (empty($bookingInfo['attribute_set_id'])) {
                if (!empty($bookingInfo['info'])) {
                    $bookingInfo['info']  = $this->getJsonDecodedString(
                        $bookingInfo['info']
                    );
                }
                $result = $this->prepareOptions($bookingInfo, $bookingInfo['type']);
                if (!empty($result)) {
                    $info = $result['slots'];
                }
            } else {
                $info['info'] = $bookingInfo['info'];
                $info['slots'] = $bookingInfo['total_slots'];
                $info['start_date'] = $bookingInfo['start_date'];
                $info['end_date'] = $bookingInfo['end_date'];
                $info['total'] = $bookingInfo['total_slots'];
            }

            return $info;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getSlots Exception : ".$e->getMessage());
            return [];
        }
    }

    /**
     * Get Parent Slot Id of Slot
     *
     * @param int $productId
     *
     * @return int
     */
    public function getParentSlotId($productId)
    {
        try {
            $id = 0;
            $collection = $this->_slotCollection
                ->create()
                ->addFieldToFilter("product_id", $productId)
                ->addFieldToFilter("status", 1);
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $id = $item->getId();
                    break;
                }
            }
            return $id;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getParentSlotId Exception : ".$e->getMessage());
            return 0;
        }
    }

    /**
     * Format Slot Data
     *
     * @param array $slot
     * @param int   $count
     * @param array $bookedSlots [optional]
     *
     * @return array
     */
    public function formatSlot($slot, $bookedSlots = [])
    {
        try {
            $format = "d-m-Y";
            $id = $slot['id'];
            $startTime = $slot['startTime'];
            $endTime = $slot['endTime'];
            $qty = $slot['qty'];
            $bookedQty = 0;
            if (count($bookedSlots) > 0) {
                if ($bookedSlots['is_booked']) {
                    if (array_key_exists($id, $bookedSlots)) {
                        $bookedQty = $bookedSlots[$id];
                    }
                }
            }

            $qty = $qty - $bookedQty;
            $qtyInfo = "";
            if ($qty > 1) {
                $qtyInfo = $qty." slots available";
            } elseif ($qty == 1) {
                $qtyInfo = $qty." slot available";
            }

            $startTime = $this->convertTimeFromSeconds($startTime);

            $endTime = $this->convertTimeFromSeconds($endTime);
            $from = $slot['date']." ".$startTime;
            $strtotimeFrom = strtotime($from);
            $from = date($format.",h:i a", $strtotimeFrom);
            $to = $slot['date']." ".$endTime;
            $to = date($format.",h:i a", strtotime($to));
            $slotTime = $startTime." - ".$endTime;

            $info = [
                'id' => $id,
                'slot' => $slotTime,
                'qty' => $qty,
                'day' => ucfirst($slot['day']),
                'day1' => ucfirst(substr($slot['day'], 0, 3)),
                'date' => date($format, strtotime($slot['date'])),
                'date_formatted' => date("j,F Y", strtotime($slot['date'])),
                'booking_from' => $from,
                'booking_to' => $to
            ];

            if (isset($slot['end_day'])) {
                $strToTimeStart = strtotime($startTime);
                $strToTimeEnd = strtotime($endTime);

                for ($i = 0; $i < 7; $i++) {
                    $weekOfTheDay = strftime("%A", strtotime("last ".$slot['day']." +$i day"));
                    $weekDayNames[] = strtolower($weekOfTheDay);
                }

                if ($slot['day']==$slot['end_day']) {
                    if ($strToTimeStart < $strToTimeEnd) {
                        $endDate = strtotime($slot['date']." ".$endTime);
                        $formated_from_date_raw = date($format.",h:i a", $endDate);
                        if ($endDate) {
                            $info['booking_to'] = $formated_from_date_raw;
                            $info['no_of_days'] = "01";
                        }
                    } else {
                        $tempString = "+7 days ";
                        $endDate = strtotime($tempString, strtotime($slot['date']." ".$endTime));
                        $formated_from_date_raw = date($format.",h:i a", $endDate);
                        if ($endDate) {
                            $info['booking_to'] = $formated_from_date_raw;
                            $info['no_of_days'] = "07";
                        }
                    }
                } else {
                    $dayKey = array_search($slot['day'], $weekDayNames);
                    $endDayKey = array_search($slot['end_day'], $weekDayNames);
                    $noOfDays = $endDayKey - $dayKey;
                    if ($noOfDays > 0) {
                        if ($noOfDays == 1) {
                            $tempString = "+".$noOfDays." days ";
                        } elseif ($noOfDays > 1) {
                            $tempString = "+".$noOfDays." day ";
                        }

                        $endDate = strtotime($tempString, strtotime($slot['date']." ".$endTime));
                        $formated_from_date_raw = date($format.",h:i a", $endDate);
                        // $formated_from_date = date_format(date_create($formated_from_date_raw), "l jS F Y");
                        if ($endDate) {
                            $info['booking_to'] = $formated_from_date_raw;
                            if ($noOfDays < 10) {
                                $noOfDays = "0".$noOfDays;
                            }
                            $info['no_of_days'] = $noOfDays;
                        }
                    }
                }
            }

            return $info;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_formatSlot Exception : ".$e->getMessage());
        }
    }

    /**
     * getCurrentTimeZone
     *
     * @return object
     */
    public function getCurrentTimeZone()
    {
        try {
            $tz = $this->timezone->getConfigTimezone();
            date_default_timezone_set($tz);
            return $tz;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCurrentTimeZone Exception : ".$e->getMessage());
        }
    }

    /**
     * Get Arranged Slots Data of Booking Product
     *
     * @param int $productId
     * @param int $parentId  [optional]
     *
     * @return array
     */
    public function getFormattedSlots($productId, $parentId = 0)
    {
        try {
            $info = [];
            $count = 1;
            if ($parentId == 0) {
                $parentId = $this->getParentSlotId($productId);
                $bookingSlots = $this->getSlots($productId);
            } else {
                $bookingSlots = $this->getSlots($productId, $parentId);
            }

            $bookedSlots = $this->getBookedSlotsQty($parentId);
            if (is_array($bookingSlots)) {
                foreach ($bookingSlots as $date => $slots) {
                    if (is_array($slots)) {
                        foreach ($slots as $slot) {
                            $slot['date'] = $date;
                            $info[$count] = $this->formatSlot($slot, $bookedSlots);
                            $count++;
                        }
                    }
                }
            }

            return $info;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getFormattedSlots Exception : ".$e->getMessage());
            return [];
        }
    }

    /**
     * Get Single Slot Data
     *
     * @param int $slotId
     * @param int $parentId
     * @param int $productId
     * @param array $info
     *
     * @return array
     */
    public function getSlotData($slotId, $parentId, $productId, $info = [])
    {
        try {
            $slotData = [];
            $product = $this->getProduct($productId);
            $productSetId = $product->getAttributeSetId();
            $allowedAttrSetIDs = $this->getAllowedAttrSetIDs();
            $appointmentAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            $eventAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Event Booking'
            );
            $rentalAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            $tableAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Table Booking'
            );
            if (in_array($productSetId, $allowedAttrSetIDs)) {
                $bookingInfo = $this->getBookingInfo($productId);
                $bookingInfoData = $this->getJsonDecodedString($bookingInfo['info']);
                if ($appointmentAttrSetId == $productSetId || $tableAttrSetId == $productSetId) {
                    if (!empty($info['slot_day_index'])) {
                        $slotDayIndex = $info['slot_day_index'];
                        $slotDate = $info['slot_date'];
                        $slotTime = $info['slot_time'];
                        if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
                            $bookedSlot = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId];
                            $bookedDateTimeFrom = date(
                                "d-m-Y",
                                strtotime($slotDate)
                            ).", ".$bookedSlot['time'];
                            $timeTill = date(
                                "h:i a",
                                strtotime(
                                    '+'.$product['slot_duration'].' minutes',
                                    strtotime($bookedSlot['time'])
                                )
                            );
                            $bookedDateTimeTo = date(
                                "d-m-Y",
                                strtotime($slotDate)
                            ).", ".$timeTill;
                            $slotData = [
                                'id' => $bookingInfo['id'],
                                'slot' => $bookedSlot['time'],
                                'qty' => $bookedSlot['qty'],
                                'day' => ucfirst($slotDate),
                                'day1' => ucfirst(substr($slotDate, 0, 3)),
                                'date' => date('d-m-Y', strtotime($slotDate)),
                                'date_formatted' => date("j,F Y", strtotime($slotDate)),
                                'booking_from' => $bookedDateTimeFrom,
                                'booking_to' => $bookedDateTimeTo,
                                'booking_date' => $slotDate,
                                'booking_slot' => $slotTime,
                                'attribute_set_id' => $productSetId
                            ];
                        }
                    }
                } elseif ($eventAttrSetId == $productSetId) {
                    if (!empty($info['parent_slot_id'])) {
                        $optionId = $info['parent_slot_id'];
                        $optionValId = $info['slot_id'];
                        $slotDate = $info['slot_date'];
                        $slotTime = $info['slot_time'];
                        $eventOptions = $this->getEventOptions($product);
                        // get saved event option id
                        $savedOptionId = 0;
                        $savedOptionValues = [];
                        $savedOptionQty[$optionValId] = 0;
                        if (!empty($eventOptions['event_ticket'])) {
                            $savedOptionId = $eventOptions['event_ticket']['option_id'];
                            foreach ($eventOptions['event_ticket']['option_values'] as $key => $value) {
                                if (empty($value['option_type_id'])) {
                                    break;
                                }
                                array_push($savedOptionValues, $value['option_type_id']);
                                if ($value['is_in_stock']) {
                                    $savedOptionQty[$value['option_type_id']] = $value['qty'];
                                }
                            }
                        }
                        $slotDate = $info['slot_date'];
                        $slotTime = $info['slot_time'];
                        $timeFormatted = date(
                            "h:i a",
                            strtotime($slotTime)
                        );
                        $bookedDateTimeFrom = date(
                            "d-m-Y",
                            strtotime($slotDate)
                        ).", ".$timeFormatted;
                        $bookedDateTimeTo = date(
                            "d-m-Y, h:i a",
                            strtotime($product->getEventDateTo())
                        );
                        // if product is added with saved event option id
                        if ($savedOptionId && $savedOptionId===$optionId) {
                            $slotData = [
                                'id' => $bookingInfo['id'],
                                'slot' => $info['slot_time'],
                                'qty' => $savedOptionQty[$optionValId],
                                'day' => '',
                                'day1' => '',
                                'date' => date('d-m-Y', strtotime($slotDate)),
                                'date_formatted' => date("j,F Y", strtotime($slotDate)),
                                'booking_from' => $bookedDateTimeFrom,
                                'booking_to' => $bookedDateTimeTo,
                                'booking_date' => $slotDate,
                                'booking_slot' => $slotTime,
                                'attribute_set_id' => $productSetId
                            ];
                        }
                    }
                } elseif ($rentalAttrSetId == $productSetId) {
                    if ($info['rent_type'] == Info::RENT_TYPE_HOURLY) {
                        if (!empty($info['slot_day_index'])) {
                            $slotDayIndex = $info['slot_day_index'];
                            $slotDate = $info['slot_date'];
                            $slotTime = $info['slot_time'];
                            if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
                                $bookedSlot = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId];
                                $bookedDateTimeFrom = date(
                                    "d-m-Y",
                                    strtotime($slotDate)
                                ).", ".$bookedSlot['time'];
                                $bookedDateTimeTo = date(
                                    "d-m-Y",
                                    strtotime($info['to_slot_date'])
                                ).", ".$info['to_slot_time'];
                                $slotData = [
                                    'id' => $bookingInfo['id'],
                                    'slot' => $bookedSlot['time'],
                                    'qty' => $bookedSlot['qty'],
                                    'day' => ucfirst($slotDate),
                                    'day1' => ucfirst(substr($slotDate, 0, 3)),
                                    'date' => date('d-m-Y', strtotime($slotDate)),
                                    'date_formatted' => date("j,F Y", strtotime($slotDate)),
                                    'booking_from' => $bookedDateTimeFrom,
                                    'booking_to' => $bookedDateTimeTo,
                                    'booking_date' => $slotDate,
                                    'booking_slot' => $slotTime,
                                    'to_booking_date' => $info['to_slot_date'],
                                    'to_booking_slot' => $info['to_slot_time'],
                                    'attribute_set_id' => $productSetId,
                                    'rent_type' => $info['rent_type']
                                ];
                            }
                        }
                    } else {
                        $slotDate = $info['slot_date'];
                        $slotTime = $info['slot_time'];
                        $bookedDateTimeFrom = date(
                            "d-m-Y",
                            strtotime($slotDate)
                        ).", ".$slotTime;
                        $bookedDateTimeTo = date(
                            "d-m-Y",
                            strtotime($info['to_slot_date'])
                        ).", ".$info['to_slot_time'];

                        $totalOrderedQty = $this->getTotalOrderedRentedQty(
                            $productId,
                            $info['item_id'],
                            $slotDate,
                            $info['to_slot_date']
                        );
                        if ($totalOrderedQty > $bookingInfo['available_qty']) {
                            $totalOrderedQty = $bookingInfo['available_qty'];
                        }

                        $slotData = [
                            'id' => $bookingInfo['id'],
                            'slot' => $slotTime,
                            'qty' => $bookingInfo['available_qty'] - $totalOrderedQty,
                            'day' => ucfirst($slotDate),
                            'day1' => ucfirst(substr($slotDate, 0, 3)),
                            'date' => date('d-m-Y', strtotime($slotDate)),
                            'date_formatted' => date("j,F Y", strtotime($slotDate)),
                            'booking_from' => $bookedDateTimeFrom,
                            'booking_to' => $bookedDateTimeTo,
                            'booking_date' => $slotDate,
                            'booking_slot' => $slotTime,
                            'to_booking_date' => $info['to_slot_date'],
                            'to_booking_slot' => $info['to_slot_time'],
                            'attribute_set_id' => $productSetId,
                            'rent_type' => $info['rent_type']
                        ];
                    }
                }
            } else {
                $slots = $this->getFormattedSlots($productId, $parentId);
                if (array_key_exists($slotId, $slots)) {
                    $slotData = $slots[$slotId];
                }
                $slots['attribute_set_id'] = $productSetId;
            }
            return $slotData;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getSlotData Exception : ".$e->getMessage());
            return [];
        }
    }

    /**
     * Get Booking Info
     *
     * @param int $productId
     *
     * @return array
     */
    public function getBookingInfo($productId)
    {
        $bookingInfo = ['is_booking' => false, 'type' => 0];
        try {
            $collection = $this->_infoCollection
                ->create()
                ->addFieldToFilter("product_id", $productId);
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $bookingInfo = $item->getData();
                    $info = $item->getInfo();
                    $bookingInfo['info'] = $info;
                    $bookingInfo['is_booking'] = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getBookingInfo Exception : ".$e->getMessage());
        }
        return $bookingInfo;
    }

    /**
     * Get Booked Slots Quantity
     *
     * @param int $parentSlotId
     *
     * @return array
     */
    public function getBookedSlotsQty($parentSlotId)
    {
        $bookedInfo = ['is_booked' => false];
        try {
            $collection = $this->_bookedCollection
                ->create()
                ->addFieldToFilter("parent_slot_id", $parentSlotId);
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $slotId = $item->getSlotId();
                    $qty = $item->getQty();
                    if (array_key_exists($slotId, $bookedInfo)) {
                        $bookedInfo[$slotId] = $bookedInfo[$slotId] + $qty;
                    } else {
                        $bookedInfo[$slotId] = $qty;
                    }

                    $bookedInfo['is_booked'] = true;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getBookedSlotsQty Exception : ".$e->getMessage());
        }
        return $bookedInfo;
    }

    /**
     * Disable Old Slots
     *
     * @param int $productId
     */
    public function disableSlots($productId)
    {
        try {
            $collection = $this->_slotCollection
                ->create()
                ->addFieldToFilter("product_id", $productId);
            if ($collection->getSize()) {
                foreach ($collection as $slot) {
                    $this->disableSlot($slot);
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_disableSlots Exception : ".$e->getMessage());
        }
    }

    /**
     * Disable Slot
     *
     * @param object $slot
     */
    public function disableSlot($slot)
    {
        try {
            $slot->addData(['status' => 0])
                ->setId($slot->getId())
                ->save();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_disableSlot Exception : ".$e->getMessage());
        }
    }

    /**
     * Get Total Booking Quantity
     *
     * @param int $productId
     *
     * @return int
     */
    public function getTotalBookingQty($productId)
    {
        try {
            $bookingInfo = $this->getBookingInfo($productId);
            if (!$bookingInfo['is_booking']) {
                return 0;
            }
            $productSetId = $bookingInfo['attribute_set_id'];
            $allowedAttrSetIDs = $this->getAllowedAttrSetIDs();
            $totalSlots = $bookingInfo['total_slots'];
            $hotelAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Hotel Booking'
            );
            if (in_array($productSetId, $allowedAttrSetIDs)
                || $productSetId == $hotelAttrSetId
            ) {
                return $totalSlots;
            } else {
                $qty = $bookingInfo['qty'];
                $actionsName = [
                    'sales_order_cancel',
                    'catalog_product_edit',
                    'catalog_product_save',
                    'marketplace_product_edit'
                ];
                if (in_array($this->getFullActionName(), $actionsName)) {
                    return $qty;
                } else {
                    return $qty*$totalSlots;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getTotalBookingQty Exception : ".$e->getMessage());
        }
    }

    /**
     * Enable Booking Custom Option on Product
     *
     * @param int $productId
     */
    public function enableOptions($productId)
    {
        try {
            if ($this->isBookingProduct($productId)) {
                $product = $this->getProduct($productId);
                $productSetId = $product->getAttributeSetId();
                $hotelAttrSetId = $this->getProductAttributeSetIdByLabel(
                    'Hotel Booking'
                );
                if ($productSetId==$hotelAttrSetId) {
                    $this->manageHotelBookingOption($product);
                } else {
                    $this->manageBookingOption($product);
                }
                $this->updateProduct($productId);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_enableOptions Exception : ".$e->getMessage());
        }
    }

    /**
     * Update Product Options
     *
     * @param int $productId
     */
    public function updateProduct($productId)
    {
        try {
            $data = ['has_options' => 1, 'required_options' => 1];
            $product = $this->_bookingProduct->create()->load($productId);
            $product->addData($data)->setId($productId)->save();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_updateProduct Exception : ".$e->getMessage());
        }
    }

    /**
     * Get Added Slot Details
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return array
     */
    public function getDetailsByQuoteItemId($itemId)
    {
        $info = ['error' => true];
        try {
            if ($itemId && $itemId!=="") {
                $collection = $this->_quoteCollection
                    ->create()
                    ->addFieldToFilter("item_id", $itemId);
                foreach ($collection as $item) {
                    $info = $item->getData();
                    $info['error'] = false;
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getDetailsByQuoteItemId Exception : ".$e->getMessage());
        }
        return $info;
    }

    /**
     * Get Added Slot Details
     *
     * @param int $quoteId
     *
     * @return array
     */
    public function getDetailsByQuoteId($quoteId)
    {
        $info = ['error' => true];
        try {
            $collection = $this->_quoteCollection
                ->create()
                ->addFieldToFilter("quote_id", $quoteId);
            foreach ($collection as $item) {
                $info = $item->getData();
                $info['error'] = false;
                break;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getDetailsByQuoteId Exception : ".$e->getMessage());
        }
        return $info;
    }

    /**
     * getAvailableSlotQty
     * Get Available Slot Quantity
     *
     * @param int $productId
     * @param int $itemId
     * @return int
     */
    public function getAvailableSlotQty($productId, $itemId)
    {
        try {
            $info = $this->getDetailsByQuoteItemId($itemId);
            if (!$info['error']) {
                $data = $this->getSlotData(
                    $info['slot_id'],
                    $info['parent_slot_id'],
                    $productId,
                    $info
                );
                return $data;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getAvailableSlotQty Exception : ".$e->getMessage());
        }
        return false;
    }

    /**
     * inCartSlotQty
     * Get Added Quantity of Slot in Cart
     *
     * @param int $slotId
     * @param int $parentId
     * @return int
     */
    public function inCartSlotQty($slotId, $parentId)
    {
        $itemId = 0;
        $qty = 0;
        try {
            $collection = $this->_quoteCollection
                ->create()
                ->addFieldToFilter("slot_id", $slotId)
                ->addFieldToFilter("parent_slot_id", $parentId);
            foreach ($collection as $item) {
                $itemId = $item->getItemId();
                break;
            }

            $cartModel = $this->getCart();
            $quote = $cartModel->getQuote();
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($itemId == $item->getId()) {
                    $qty = $item->getQty();
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_inCartSlotQty Exception : ".$e->getMessage());
        }
        return $qty;
    }

    /**
     * getOrder
     * Get Order Object
     *
     * @param int $orderId
     * @return object
     */
    public function getOrder($orderId)
    {
        try {
            $order = $this->_order->create()->load($orderId);
            return $order;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getOrder Exception : ".$e->getMessage());
        }
    }

    /**
     * formatDate
     * Get Formatted Date
     *
     * @param string $format
     * @param int $timestamp [optional]
     * @return string
     */
    public function formatDate($format, $timestamp = 0)
    {
        try {
            return date($format, $timestamp);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_formatDate Exception : ".$e->getMessage());
        }
    }

    /**
     * daysInMonth
     * Get Days in Month
     *
     * @param string $month [optional]
     * @param string $year  [optional]
     * @return int
     */
    public function daysInMonth($month = '', $year = '')
    {
        try {
            if ($month == '') {
                $month = $this->getCurrentMonth();
            }

            if ($year == '') {
                $year = $this->getCurrentYear();
            }

            $date = $year.'-'.$month.'-01';

            return date('t', strtotime($date));
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_daysInMonth Exception : ".$e->getMessage());
        }
    }

    /**
     * getMonth
     * Get Month Title
     *
     * @param string $month [optional]
     * @param string $year  [optional]
     * @return string
     */
    public function getMonth($month = '', $year = '')
    {
        try {
            if ($month == '') {
                $month = $this->getCurrentMonth();
            }

            if ($year == '') {
                $year = $this->getCurrentYear();
            }

            $date = $year.'-'.$month.'-01';

            return date('F', strtotime($date));
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getMonth Exception : ".$e->getMessage());
        }
    }

    /**
     * getStartDay
     * Get Index of Start Day
     *
     * @param string $month [optional]
     * @param string $year  [optional]
     * @return int
     */
    public function getStartDay($month = '', $year = '')
    {
        try {
            if ($month == '') {
                $month = $this->getCurrentMonth();
            }

            if ($year == '') {
                $year = $this->getCurrentYear();
            }

            $date = $year.'-'.$month.'-01';

            return date('N', strtotime($date));
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getStartDay Exception : ".$e->getMessage());
        }
    }

    /**
     * weeksInMonth
     * Get Number of Weeks in Month
     *
     * @param string $month [optional]
     * @param string $year  [optional]
     * @return int
     */
    public function weeksInMonth($month = '', $year = '')
    {
        try {
            if ($month == '') {
                $month = $this->getCurrentMonth();
            }

            if ($year == '') {
                $year = $this->getCurrentYear();
            }

            $daysInMonths = $this->daysInMonth($month, $year);
            $numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + (int)($daysInMonths / 7);
            $monthEndingDay = date('N', strtotime($year.'-'.$month.'-'.$daysInMonths));
            $monthStartDay = date('N', strtotime($year.'-'.$month.'-01'));
            if ($monthEndingDay < $monthStartDay) {
                ++$numOfweeks;
            }

            return $numOfweeks;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_weeksInMonth Exception : ".$e->getMessage());
        }
    }

    /**
     * getCalendarTitle
     * Get Calendar Title Html Block
     *
     * @param string  $month     [optional]
     * @param string  $year      [optional]
     * @param int     $productId [optional]
     * @param boolean $prev      [optional]
     * @param boolean $next      [optional]
     * @return string
     */
    public function getCalendarTitle($month = '', $year = '', $productId = 0, $prev = false, $next = false)
    {
        try {
            if ($month == '') {
                $month = $this->getCurrentMonth();
            }

            if ($year == '') {
                $year = $this->getCurrentYear();
            }

            $html = "<div class='wk-calendar-title wk-title-'".$productId.">";
            if ($prev) {
                $html .= "<span class='wk-previous-cal'></span>";
            }

            $html .= $this->getMonth($month, $year).' '.$year;
            if ($next) {
                $html .= "<span class='wk-next-cal'></span>";
            }

            $html .= '</div>';

            return $html;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCalendarTitle Exception : ".$e->getMessage());
        }
    }

    /**
     * getCalendarHeader
     * Get Calendar Header Html Block
     *
     * @return string
     */
    public function getCalendarHeader()
    {
        try {
            $html = "<div class='wk-calendar-head'>";
            foreach ($this->dayLabels as $label) {
                $html .= "<div class='wk-calendar-col'>".$label.'</div>';
            }

            $html .= '</div>';
            return $html;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCalendarHeader Exception : ".$e->getMessage());
        }
    }

    /**
     * validateEntry
     * Validate Value
     *
     * @param string $value
     * @param string $defaultValue
     * @return string
     */
    public function validateEntry($value, $defaultValue)
    {
        try {
            if ($value == '') {
                return $defaultValue;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_validateEntry Exception : ".$e->getMessage());
        }
        return $value;
    }

    /**
     * getMonthValue
     * Get Month Value
     *
     * @param int $month
     * @return int
     */
    public function getMonthValue($month)
    {
        try {
            if ($month > 12) {
                $month = 1;
            }
            return $month;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getMonthValue Exception : ".$e->getMessage());
        }
    }

    /**
     * getIndexedMonth
     * Get Indexed Month Value
     *
     * @param int $month
     * @return int|string
     */
    public function getIndexedMonth($month)
    {
        try {
            if ($month < 10) {
                $month = '0'.$month;
            }
            return $month;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getIndexedMonth Exception : ".$e->getMessage());
        }
    }

    /**
     * getIndexedDay
     * Get Indexed Day Value
     *
     * @param int $day
     * @return int|string
     */
    public function getIndexedDay($day)
    {
        try {
            if ($day < 10) {
                $day = '0'.$day;
            }
            return $day;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getIndexedDay Exception : ".$e->getMessage());
        }
    }

    /**
     * getCurrentTime
     *
     * @param boolean $isDate
     * @return int
     */
    public function getCurrentTime($isDate = false)
    {
        try {
            // Date for a specific date/time:
            $date = new \DateTime();

            // Convert timezone
            $tz = new \DateTimeZone($this->getCurrentTimeZone());
            $date->setTimeZone($tz);

            // Output date after
            if ($isDate) {
                return strtotime($date->format('Y-m-d H:i:s'));
            } else {
                return strtotime($date->format('H:i:s'));
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCurrentTime Exception : ".$e->getMessage());
        }
    }

    /**
     * getDayClass
     * Get Day Class
     *
     * @param int $day
     * @param int $month
     * @param int $year
     * @return string
     */
    public function getDayClass($day, $month, $year)
    {
        $dayClass = '';
        try {
            $currentDay = $this->getCurrentDay();
            $currentMonth = $this->getCurrentMonth();
            $currentYear = $this->getCurrentYear();

            if ($year < $currentYear) {
                $dayClass = 'wk-passed-day';
            } elseif ($year == $currentYear) {
                if ($month < $currentMonth) {
                    $dayClass = 'wk-passed-day';
                } elseif ($month == $currentMonth) {
                    if ($day < $currentDay) {
                        $dayClass = 'wk-passed-day';
                    } else {
                        $dayClass = 'wk-available-day';
                    }
                } else {
                    $dayClass = 'wk-available-day';
                }
            } else {
                $dayClass = 'wk-available-day';
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getDayClass Exception : ".$e->getMessage());
        }
        return $dayClass;
    }

    /**
     * createCalendar
     * Create Html of Calendar
     *
     * @param string  $month     [optional]
     * @param string  $year      [optional]
     * @param int     $productId [optional]
     * @param boolean $prev      [optional]
     * @param boolean $next      [optional]
     * @param string  $class     [optional]
     *
     * @return string
     */
    public function createCalendar($month = '', $year = '', $productId = 0, $prev = false, $next = false, $class = '')
    {
        try {
            $month = $this->validateEntry($month, $this->getCurrentMonth());
            $year = $this->validateEntry($year, $this->getCurrentYear());
            $month = $this->getMonthValue($month);
            $html = "<div class='wk-calendar-conatiner ".$class."'>";
            $html .= $this->getCalendarTitle($month, $year, $productId, $prev, $next);
            $html .= "<div class='wk-calendar-content'>";
            $html .= $this->getCalendarHeader();
            $html .= "<div class='wk-calendar-body'>";
            $weeksInMonth = $this->weeksInMonth($month, $year);
            $daysInMonth = $this->daysInMonth($month, $year);
            $month = $this->getIndexedMonth($month);
            $k = 0;
            $bookingClass = '';

            $defaultClass = "wk-calendar-cell wk-day ";
            $parentId = $this->getParentSlotId($productId);
            $slots = $this->getSlots($productId);

            $bookedSlots = $this->getBookedSlotsQty($parentId);
            for ($i = 0; $i < $weeksInMonth; ++$i) {
                $html .= "<div class='wk-calendar-row'>";
                for ($j = 1; $j <= 7; ++$j) {
                    $day = $i * 7 + $j;
                    $startDay = $this->getStartDay($month, $year);
                    if ($day >= $startDay && $k < $daysInMonth) {
                        ++$k;
                        $dateDay = $this->getIndexedDay($k);
                        $date = $year."-".$month."-".$dateDay;
                        $dayClass = $this->getDayClass($k, $month, $year);
                        $html .= "<div class='wk-calendar-col'>";

                        $bookingClass = "slot-not-available";
                        $allBooked = "";

                        if (array_key_exists($date, $slots) && $dayClass==='wk-available-day') {
                            $info = $slots[$date];
                            $allBooked = $this->getAllSlotsBookedClass($info, $bookedSlots);

                            if ($info && count($info) > 0) {
                                $bookingClass = "slot-available";
                            }
                            if (strtotime($this->getCurrentDate()) === strtotime($date)
                                && $bookingClass==="slot-available"
                            ) {
                                $notAvailable = true;
                                foreach ($info as $key => $value) {
                                    $startTime = strtotime($this->convertTimeFromSeconds($value['startTime']));
                                    // $endTime = strtotime(gmdate("H:i:s", $value['endTime']));
                                    if ($startTime > $this->getCurrentTime()) {
                                        $notAvailable = false;
                                        break;
                                    }
                                }
                                if ($notAvailable) {
                                    $bookingClass = "slot-not-available";
                                }
                            }
                        }

                        $html .= "<div data-date='".$date."' class='"
                            .$defaultClass.$bookingClass.$allBooked.' '.$dayClass
                            ."'>";
                        $html .= $k;
                        $html .= '</div>';
                        $html .= '</div>';
                    } else {
                        $html .= "<div class='wk-calendar-col'></div>";
                    }
                }

                $html .= '</div>';
            }

            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_createCalendar Exception : ".$e->getMessage());
        }
    }

    /**
     * convertTimeFromSeconds
     *
     * @param int $seconds
     * @return string
     */
    public function convertTimeFromSeconds($seconds)
    {
        try {
            $hour = floor($seconds/60);
            $minute = floor($seconds%60);
            if ($minute <= 9) {
                $minute = "0".$minute;
            }
            $time = $hour.":".$minute;
            return $time;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_convertTimeFromSeconds Exception : ".$e->getMessage());
        }
    }

    /**
     * getAllSlotsBookedClass
     *
     * @param array $info
     * @param array $bookedSlots
     * @return string
     */
    private function getAllSlotsBookedClass($info, $bookedSlots)
    {
        try {
            $allBooked = true;
            if ($bookedSlots['is_booked']==1) {
                foreach ($info as $key => $value) {
                    if (array_key_exists($value['id'], $bookedSlots)) {
                        $actualQty = $value['qty'] - $bookedSlots[$value['id']];
                        if ($actualQty > 0) {
                            $allBooked = false;
                            break;
                        }
                    } else {
                        $allBooked = false;
                        break;
                    }
                }
            } else {
                $allBooked = false;
            }

            if ($allBooked) {
                return " booked-slot";
            } else {
                return "";
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getAllSlotsBookedClass Exception : ".$e->getMessage());
            return "";
        }
    }

    /**
     * getCurrentDate
     * Get Current Date
     *
     * @return string
     */
    public function getCurrentDate()
    {
        try {
            return date('Y-m-d');
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCurrentDate Exception : ".$e->getMessage());
        }
    }

    /**
     * getCurrentDay
     * Get Current Day
     *
     * @return int
     */
    public function getCurrentDay()
    {
        try {
            return date('d');
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCurrentDay Exception : ".$e->getMessage());
        }
    }

    /**
     * getCurrentMonth
     * Get Current Month
     *
     * @return int
     */
    public function getCurrentMonth()
    {
        try {
            return date('m');
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCurrentMonth Exception : ".$e->getMessage());
        }
    }

    /**
     * getCurrentYear
     * Get Current Year
     *
     * @return int
     */
    public function getCurrentYear()
    {
        try {
            return date('Y');
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCurrentYear Exception : ".$e->getMessage());
        }
    }

    /**
     * getCalendar
     * Get Calendar Html
     *
     * @param string  $month     [optional]
     * @param string  $year      [optional]
     * @param int     $productId [optional]
     * @param boolean $prev      [optional]
     * @param boolean $next      [optional]
     * @param string  $class     [optional]
     *
     * @return string
     */
    public function getCalendar($month = '', $year = '', $productId = 0, $prev = false, $next = false, $class = '')
    {
        try {
            if ($month == '') {
                $month = $this->getCurrentMonth();
            }

            if ($year == '') {
                $year = $this->getCurrentYear();
            }

            return $this->createCalendar($month, $year, $productId, $prev, $next, $class);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCalendar Exception : ".$e->getMessage());
        }
    }

    /**
     * getAllCalendars
     * Get Calendar for Booking Product
     *
     * @param int $productId
     * @return string
     */
    public function getAllCalendars($productId)
    {
        try {
            $bookingInfo = $this->getBookingInfo($productId);
            if ($bookingInfo['is_booking']) {
                $startDate = $bookingInfo['start_date'];
                $endDate = $bookingInfo['end_date'];
            } else {
                $startDate = "";
                $endDate = "";
            }

            $html = "";
            $startMonth = (int) date('m', strtotime($startDate));
            $startYear = (int) date('Y', strtotime($startDate));
            $endMonth = (int) date('m', strtotime($endDate));
            $endYear = (int) date('Y', strtotime($endDate));
            $arr = [];
            $diff = $endYear - $startYear;
            if ($diff > 0) {
                $total = 12*$diff;
                $total = $total + $endMonth;
                $count = 0;
                $totalMonths = $total - $startMonth;
                $year = $startYear;
                for ($i = $startMonth; $i <= $total; $i++) {
                    $month = $i%12;
                    $month = $this->resetMonth($month);
                    $prev = $this->isPrevAllowed($count, $totalMonths);
                    $next = $this->isNextAllowed($count, $totalMonths);
                    $class = ($count == 0) ? 'wk-current-month' : '';
                    $html .= $this->getCalendar($month, $year, $productId, $prev, $next, $class);
                    $year = $this->resetYear($month, $year);
                    $count++;
                }
            } else {
                $count = 0;
                $totalMonths = $endMonth - $startMonth;
                for ($month = $startMonth; $month <= $endMonth; $month++) {
                    $prev = $this->isPrevAllowed($count, $totalMonths, 1);
                    $next = $this->isNextAllowed($count, $totalMonths, 1);
                    $class = ($count == 0) ? 'wk-current-month' : '';
                    $html .= $this->getCalendar($month, $endYear, $productId, $prev, $next, $class);
                    $count++;
                }
            }

            return $html;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getAllCalendars Exception : ".$e->getMessage());
            return "";
        }
    }

    /**
     * getCalendarClass
     * Get Calendar Class
     *
     * @param int $month
     * @param int $year[optional]
     * @return string
     */
    public function getCalendarClass($month, $year = "")
    {
        $class = "";
        try {
            $currentYear = $this->getCurrentYear();
            $currentMonth = $this->getCurrentMonth();
            if ($year != "") {
                if ($month == $currentMonth && $year == $currentYear) {
                    $class = 'wk-current-month';
                }
            } else {
                if ($month == $currentMonth) {
                    $class = 'wk-current-month';
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getCalendarClass Exception : ".$e->getMessage());
        }
        return $class;
    }

    /**
     * isPrevAllowed
     * Check Whether Previous Button Allowed or Not
     *
     * @param int $count
     * @param int $totalMonths
     * @param int $type[Optional]
     * @return bool
     */
    public function isPrevAllowed($count, $totalMonths, $type = 0)
    {
        try {
            if ($type == 1) {
                if ($totalMonths == 0) {
                    return false;
                }
            }

            if ($count > 0) {
                return true;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_isPrevAllowed Exception : ".$e->getMessage());
        }
        return false;
    }

    /**
     * isNextAllowed
     * Check Whether Next Button Allowed or Not
     *
     * @param int $count
     * @param int $totalMonths
     * @param int $type[Optional]
     * @return bool
     */
    public function isNextAllowed($count, $totalMonths, $type = 0)
    {
        try {
            if ($type == 1) {
                if ($totalMonths == 0) {
                    return false;
                }
            }

            if ($count == $totalMonths) {
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_isNextAllowed Exception : ".$e->getMessage());
        }
        return true;
    }

    /**
     * resetMonth
     * Reset Month Value
     *
     * @param int $month
     * @return int
     */
    public function resetMonth($month)
    {
        try {
            if ($month == 0) {
                $month = 12;
            }

            return $month;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_resetMonth Exception : ".$e->getMessage());
        }
    }

    /**
     * resetYear
     * Reset Year Value
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    public function resetYear($month, $year)
    {
        try {
            if ($month == 12) {
                $year++;
            }

            return $year;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_resetYear Exception : ".$e->getMessage());
        }
    }

    /**
     * deleteHotelOption
     * Delete Hotel Booking Option
     *
     * @param object $option
     * @return void|string
     */
    public function deleteHotelOption($option)
    {
        try {
            $allowedTitle = [
                "Booking From",
                "Booking Till",
                // "Rooms",
                "Adults",
                "Kids"
            ];
            if (in_array($option->getTitle(), $allowedTitle)) {
                if ($option->getType() == "field") {
                    return $option->getTitle();
                } else {
                    $option->delete();
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_deleteHotelOption Exception : ".$e->getMessage());
        }
    }

    /**
     * deleteOption
     * Delete Booking Option
     *
     * @param object $option
     * @return void|string
     */
    public function deleteOption($option)
    {
        try {
            $allowedTitle = [
                "Booking From",
                "Booking Till",
                "Booking Date",
                "Booking Slot",
                "Event From",
                "Event To",
                "Event Location",
                "Rent From",
                "Rent To",
                "Special Request/Notes",
                "Charged Per"
            ];
            if (in_array($option->getTitle(), $allowedTitle)) {
                if ($option->getType() == "field"
                    || ($option->getType() == "area"
                    && $option->getTitle() == "Special Request/Notes"
                    )
                ) {
                    return $option->getTitle();
                } else {
                    $option->delete();
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_deleteOption Exception : ".$e->getMessage());
        }
    }

    /**
     * manageHotelBookingOption
     * Mange Hotel Booking Custom Options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function manageHotelBookingOption($product)
    {
        try {
            $optionTitles = [];
            $options = [];
            $i = 1;

            // Creating Custom Options
            $bookingOptions = [
                "Booking From",
                "Booking Till",
                "Adults",
                "Kids"
            ];

            foreach ($product->getOptions() as $option) {
                $optionTitles[] = $this->deleteHotelOption($option);
            }

            foreach ($bookingOptions as $opt) {
                if (!in_array($opt, $optionTitles)) {
                    $isRequire = 1;
                    if ($opt == "Kids") {
                        $isRequire = 0;
                    }
                    $options[] = [
                        'sort_order' => $i,
                        'title' => $opt,
                        'price_type' => 'fixed',
                        'price' => '',
                        'type' => 'field',
                        'is_require' => $isRequire,
                    ];
                }
                $i++;
            }

            foreach ($options as $arrayOption) {
                $this->createOption($arrayOption, $product);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_manageHotelBookingOption Exception : ".$e->getMessage());
        }
    }

    /**
     * manageBookingOption
     * Mange Booking Custom Options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function manageBookingOption($product)
    {
        try {
            $optionTitles = [];
            $productSetId = $product->getAttributeSetId();
            $appointmentType = $this->getProductAttributeSetIdByLabel('Appointment Booking');
            $eventType = $this->getProductAttributeSetIdByLabel('Event Booking');
            $rentType = $this->getProductAttributeSetIdByLabel('Rental Booking');
            $tableType = $this->getProductAttributeSetIdByLabel('Table Booking');
            foreach ($product->getOptions() as $option) {
                $title = $option->getTitle();
                if ($title!='Event Tickets' && $title!='Choose Rent Type') {
                    $optionTitles[] = $this->deleteOption($option);
                }
            }

            // Creating Custom Options
            $bookingOption1 = "Booking From";
            $bookingOption2 = "Booking Till";
            if ($productSetId == $appointmentType || $productSetId == $tableType) {
                $bookingOption1 = "Booking Date";
                $bookingOption2 = "Booking Slot";
            } elseif ($productSetId == $eventType) {
                $bookingOption1 = "Event From";
                $bookingOption2 = "Event To";
                $bookingOption3 = "Event Location";
            } elseif ($productSetId == $rentType) {
                $bookingOption1 = "Rent From";
                $bookingOption2 = "Rent To";
            }
            $options = [];
            if (!in_array($bookingOption1, $optionTitles)) {
                $options[] = [
                    'sort_order' => 98,
                    'title' => $bookingOption1,
                    'price_type' => 'fixed',
                    'price' => '',
                    'type' => 'field',
                    'is_require' => 1,
                ];
            }

            if (!in_array($bookingOption2, $optionTitles)) {
                $options[] = [
                    'sort_order' => 99,
                    'title' => $bookingOption2,
                    'price_type' => 'fixed',
                    'price' => '',
                    'type' => 'field',
                    'is_require' => 1,
                ];
            }

            if (isset($bookingOption3) && !in_array($bookingOption3, $optionTitles)) {
                $options[] = [
                    'sort_order' => 100,
                    'title' => $bookingOption3,
                    'price_type' => 'fixed',
                    'price' => '',
                    'type' => 'field',
                    'is_require' => 0,
                ];
            }

            if ($productSetId == $tableType) {
                $bookingOption3 = "Special Request/Notes";
                $bookingOption4 = "Charged Per";

                if (!in_array($bookingOption3, $optionTitles)) {
                    $options[] = [
                        'sort_order' => 100,
                        'title' => $bookingOption3,
                        'price_type' => 'fixed',
                        'price' => '',
                        'type' => 'area',
                        'is_require' => 0,
                    ];
                }

                if (!in_array($bookingOption4, $optionTitles)) {
                    $options[] = [
                        'sort_order' => 101,
                        'title' => $bookingOption4,
                        'price_type' => 'fixed',
                        'price' => '',
                        'type' => 'field',
                        'is_require' => 1,
                    ];
                }
            }

            foreach ($options as $arrayOption) {
                $this->createOption($arrayOption, $product);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_manageBookingOption Exception : ".$e->getMessage());
        }
    }

    /**
     * createOption
     *
     * @param array $arrayOption
     * @param \Magento\Catalog\Model\Product $product
     * @return void|boolean
     */
    public function createOption($arrayOption, $product)
    {
        try {
            if (empty($arrayOption[0]['option_id'])) {
                if ($product->getHasOptions() != 1) {
                    $product->setHasOptions(1);
                    $product->getResource()->save($product);
                }
                $option = $this->_option
                    ->create()
                    ->setProductId($product->getId())
                    ->setStoreId($product->getStoreId())
                    ->addData($arrayOption);
                $option->save();
                $product->addOption($option);
            } elseif (is_array($arrayOption)) {
                // Saved existed option data
                if ($product->getHasOptions() != 1) {
                    $product->setHasOptions(1);
                    $product->getResource()->save($product);
                }
                $bookingOptions = [];
                foreach ($arrayOption as $bookingOptionData) {
                    if (!empty($bookingOptionData['is_delete'])) {
                        continue;
                    }

                    if (empty($bookingOptionData['option_id'])) {
                        $bookingOptionData['option_id'] = null;
                    }

                    if (isset($bookingOptionData['values'])) {
                        $bookingOptionData['values'] = array_filter(
                            $bookingOptionData['values'],
                            function ($valueData) {
                                return empty($valueData['is_delete']);
                            }
                        );
                    }
                    $bookingOption = $this->_option->create(['data' => $bookingOptionData]);
                    $bookingOption->setProductSku($product->getSku())->save();
                    $bookingOptions[] = $bookingOption;
                }
                $product->setOptions($bookingOptions);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_createOption Exception : ".$e->getMessage());
        }
    }

    /**
     * getDataByField
     * Get First Object From Collection
     *
     * @param array|int|string $values
     * @param array|string $fields
     * @param object $collection
     * @return $object
     */
    public function getDataByField($values, $fields, $collection)
    {
        $item = false;
        try {
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $field = $fields[$key];
                    $collection = $collection->addFieldToFilter($field, $value);
                }
            } else {
                $collection = $collection->addFieldToFilter($fields, $values);
            }
            foreach ($collection as $item) {
                return $item;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getDataByField Exception : ".$e->getMessage());
        }
        return $item;
    }

    /**
     * checkBookingProduct
     *
     * @param int $productId
     * @return void
     */
    public function checkBookingProduct($productId)
    {
        try {
            if ($this->isBookingProduct($productId)) {
                $product = $this->getProduct($productId);
                $slots = $this->getSlots($productId);
                if ($product->getTypeId()!=="hotelbooking") {
                    $count = count($slots);
                    if ($count <= 0) {
                        $this->setOutOfStock($productId);
                    } else {
                        $qty = $this->getTotalBookingQty($productId);
                        $this->setInStock($productId, $qty);
                        $this->cleanReservationData($product->getSku());
                    }
                } else {
                    $qty = $this->getTotalBookingQty($productId);
                    $this->setInStock($productId, $qty);
                    $this->cleanReservationData($product->getSku());
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_checkBookingProduct Exception : ".$e->getMessage());
        }
    }

    public function cleanReservationData($sku)
    {
        if (!empty($sku)) {
            $this->cleanupReservation->execute($sku);
        }
    }

    /**
     * getStockData
     *
     * @param int $productId
     * @return object
     */
    public function getStockData($productId)
    {
        try {
            $stockItem = $this->_stockRegistry->getStockItem($productId);
            return $stockItem;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getStockData Exception : ".$e->getMessage());
        }
    }

    /**
     * setInStock
     *
     * @param [int] $productId
     * @param [int] $qty
     * @return void
     */
    public function setInStock($productId, $qty)
    {
        try {
            $stockItem = $this->_stockRegistry->getStockItem($productId);
            $stockItem->setData('is_in_stock', 1);
            $stockItem->setData('qty', $qty);
            $stockItem->save();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_setInStock Exception : ".$e->getMessage());
        }
    }

    /**
     * setOutOfStock
     *
     * @param int $productId
     * @return void
     */
    public function setOutOfStock($productId)
    {
        try {
            $stockItem = $this->_stockRegistry->getStockItem($productId);
            $stockItem->setData('is_in_stock', 0);
            $stockItem->setData('qty', 0);
            $stockItem->save();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_setOutOfStock Exception : ".$e->getMessage());
        }
    }

    /**
     * deleteInfo
     *
     * @param int $productId
     * @return void
     */
    public function deleteInfo($productId)
    {
        try {
            $collection = $this->_infoCollection
                ->create()
                ->addFieldToFilter("product_id", $productId);
            foreach ($collection as $item) {
                $item->delete();
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_deleteInfo Exception : ".$e->getMessage());
        }
    }

    /**
     * prepareOptions
     * Prepare Options
     *
     * @param array $data
     * @param int   $bookingType
     * @return array
     */
    public function prepareOptions($data, $bookingType)
    {
        $result = [];
        try {
            if ($bookingType == 1) {
                $result = $this->prepareManyBookingOptions($data);
            } elseif ($bookingType == 2) {
                $result = $this->prepareOneBookingOptions($data);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_prepareOptions Exception : ".$e->getMessage());
        }
        return $result;
    }

    /**
     * prepareManyBookingOptions
     * Prepare Many Booking Options
     *
     * @param array $data
     * @return array
     */
    public function prepareManyBookingOptions($data)
    {
        try {
            $slots = [];
            $count = 1;
            $info = $data['info'];
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $slotTime = $data['info']['time_slot'];
            $breakTime = $data['info']['break_time'];
            $qty = $data['qty'];
            $numOfDays = $this->getDateDifference($startDate, $endDate);
            for ($i = 0; $i <= $numOfDays; $i++) {
                $date = strtotime("+$i day", strtotime($startDate));
                $day = strtolower(date("l", $date));
                $date = strtolower(date("Y-m-d", $date));
                $status = $info[$day]['status'];
                if ($status == 1) {
                    $startHour = $info[$day]['start_hour'];
                    $startMinute = $info[$day]['start_minute'];
                    $endHour = $info[$day]['end_hour'];
                    $endMinute = $info[$day]['end_minute'];
                    $startCount = $startHour*60 + $startMinute;
                    $endCount = $endHour*60 + $endMinute;
                    $st = $startCount;
                    $diff = $endCount - $startCount;
                    while ($diff >= $slotTime) {
                        $slots[$date][] = [
                            'startTime' => $st,
                            'endTime' => $st + $slotTime,
                            'qty' => $qty,
                            'id' => $count,
                            'day' => $day
                        ];
                        $st = $st+$slotTime+$breakTime;
                        $diff = $diff - ($breakTime + $slotTime);
                        $count++;
                    }
                }
            }

            $bookingInfo = $data['info'];
            $bookingInfo['time_slot'] = $slotTime;
            $bookingInfo['break_time'] = $breakTime;
            $result = [];
            $result['info'] = $bookingInfo;
            $result['slots'] = $slots;
            $result['start_date'] = $startDate;
            $result['end_date'] = $endDate;
            $result['total'] = $count-1;
            return $result;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_prepareManyBookingOptions Exception : ".$e->getMessage());
        }
    }

    /**
     * prepareOneBookingOptions
     * Prepare One Booking Options
     *
     * @param array $data
     * @return array
     */
    public function prepareOneBookingOptions($data)
    {
        try {
            $slots = [];
            $count = 1;
            $info = $data['info'];
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $qty = $data['qty'];
            $numOfDays = $this->getDateDifference($startDate, $endDate);
            $startData = $data['info']['start'];
            $endData = $data['info']['end'];
            $startDays = $startData['day'];
            $startHours = $startData['hour'];
            $startMinutes = $startData['minute'];
            $endDays = $endData['day'];
            $endHours = $endData['hour'];
            $endMinutes = $endData['minute'];
            for ($i = 0; $i <= $numOfDays; $i++) {
                $date = strtotime("+$i day", strtotime($startDate));
                $day = strtolower(date("l", $date));
                $date = strtolower(date("Y-m-d", $date));
                if (!empty($startDays) && is_array($startDays)) {
                    foreach ($startDays as $key => $startDay) {
                        if ($day == $startDay) {
                            $st = $startHours[$key]*60 + $startMinutes[$key];
                            $et = $endHours[$key]*60 + $endMinutes[$key];
                            $slots[$date][] = [
                                'startTime' => $st,
                                'endTime' => $et,
                                'qty' => $qty,
                                'id' => $count,
                                'day' => $day,
                                'end_day' => $endDays[$key]
                            ];
                            $count++;
                        }
                    }
                }
            }

            $bookingInfo = ['start' => $startData, 'end' => $endData];
            $result = [];
            $result['info'] = $bookingInfo;
            $result['slots'] = $slots;
            $result['total'] = $count-1;
            return $result;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_prepareOneBookingOptions Exception : ".$e->getMessage());
        }
    }

    /**
     * getDateDifference
     * Get Difference  of Dates
     *
     * @param string $firstDate
     * @param string $lastDate
     * @return int
     */
    public function getDateDifference($firstDate, $lastDate)
    {
        try {
            $date1 = date_create($firstDate);
            $date2 = date_create($lastDate);
            $diff = date_diff($date1, $date2);
            $numOfDays = (int)$diff->format("%R%a");
            return $numOfDays;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getDateDifference Exception : ".$e->getMessage());
        }
    }

    /**
     * logDataInLogger
     * generates log with data
     *
     * @param string $data
     * @return void
     */
    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }

    /**
     * getProductAttributeSetIdByLabel
     * Get product Set Id By Label
     *
     * @param string $attributeSetName
     * @return int
     */
    public function getProductAttributeSetIdByLabel($attributeSetName)
    {
        try {
            $entityType = $this->eavConfig->getEntityType(
                \Magento\Catalog\Model\Product::ENTITY
            );
            $entityTypeId = $entityType->getId();
            $attributeSet = $this->attributeSetFactory->create();
            $setCollection = $attributeSet->getResourceCollection()
                ->addFieldToFilter('entity_type_id', $entityTypeId)
                ->addFieldToFilter('attribute_set_name', $attributeSetName)
                ->load();
            $attributeSet = $setCollection->fetchItem();
            $attributeSetId = 0;
            if ($attributeSet) {
                $attributeSetId = $attributeSet->getId();
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getProductAttributeSetIdByLabel Exception : ".$e->getMessage());
            return $attributeSetId = 0;
        }
        return $attributeSetId;
    }

    /**
     * getAllowedAttrSetIDs
     * Get product allowed setids array
     *
     * @return array
     */
    public function getAllowedAttrSetIDs()
    {
        try {
            $appointmentType = $this->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            $eventType = $this->getProductAttributeSetIdByLabel(
                'Event Booking'
            );
            $rentalType = $this->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            $hotelType = $this->getProductAttributeSetIdByLabel(
                'Hotel Booking'
            );
            $tableType = $this->getProductAttributeSetIdByLabel(
                'Table Booking'
            );
            if ($appointmentType) {
                $allowedAttrSetIDs[] = $appointmentType;
            }
            if ($eventType) {
                $allowedAttrSetIDs[] = $eventType;
            }
            if ($rentalType) {
                $allowedAttrSetIDs[] = $rentalType;
            }
            if ($hotelType) {
                $allowedAttrSetIDs[] = $hotelType;
            }
            if ($tableType) {
                $allowedAttrSetIDs[] = $tableType;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getAllowedAttrSetIDs Exception : ".$e->getMessage());
            $allowedAttrSetIDs = [];
        }
        return $allowedAttrSetIDs;
    }

    /**
     * getAllowedAttrSetIDsArray
     * Get product allowed set Ids array
     *
     * @return array
     */
    public function getAllowedAttrSetIDsArray()
    {
        try {
            $default = $this->getProductAttributeSetIdByLabel(
                'Default'
            );
            $appointmentType = $this->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            $eventType = $this->getProductAttributeSetIdByLabel(
                'Event Booking'
            );
            $rentalType = $this->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            $hotelType = $this->getProductAttributeSetIdByLabel(
                'Hotel Booking'
            );
            $tableType = $this->getProductAttributeSetIdByLabel(
                'Table Booking'
            );
            if ($default) {
                $allowedAttrSetIDs[] = [
                    'value' => $default,
                    'label' => 'Default'
                ];
            }
            if ($appointmentType) {
                $allowedAttrSetIDs[] = [
                    'value' => $appointmentType,
                    'label' => 'Appointment Booking'
                ];
            }
            if ($eventType) {
                $allowedAttrSetIDs[] = [
                    'value' => $eventType,
                    'label' => 'Event Booking'
                ];
            }
            if ($rentalType) {
                $allowedAttrSetIDs[] = [
                    'value' => $rentalType,
                    'label' => 'Rental Booking'
                ];
            }
            if ($hotelType) {
                $allowedAttrSetIDs[] = [
                    'value' => $hotelType,
                    'label' => 'Hotel Booking'
                ];
            }
            if ($tableType) {
                $allowedAttrSetIDs[] = [
                    'value' => $tableType,
                    'label' => 'Table Booking'
                ];
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getAllowedAttrSetIDsArray Exception : ".$e->getMessage());
            $allowedAttrSetIDs = [];
        }
        return $allowedAttrSetIDs;
    }

    /**
     * getJsonEcodedString
     * Encodes array into string
     *
     * @param array $data
     * @return string
     */
    public function getJsonEcodedString($data)
    {
        try {
            // $this->logDataInLogger("Helper_Data_getJsonEcodedString : ".print_r($data, true));
            return $this->jsonHelper->jsonEncode($data);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getJsonEcodedString Exception : ".$e->getMessage());
            return $data;
        }
    }

    /**
     * getJsonDecodedString
     * Decodes string into array
     *
     * @param string $data
     * @return array
     */
    public function getJsonDecodedString($data)
    {
        try {
            if (is_array($data)) {
                return $data;
            }
            return $this->jsonHelper->jsonDecode($data);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getJsonDecodedString Exception : ".$e->getMessage());
            return $data;
        }
    }

    /**
     * getDayIndexId
     * Get Week Day Index
     *
     * @param string $label
     * @return int
     */
    public function getDayIndexId($label)
    {
        $index = 0;
        try {
            $dayArrayData = array_flip($this->dayLabelsFull);
            if (!empty($dayArrayData[$label])) {
                $index = $dayArrayData[$label];
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getDayIndexId Exception : ".$e->getMessage());
        }
        return $index;
    }

    /**
     * getDayLabel
     * Get Week Day Label
     *
     * @param int $index
     * @return string
     */
    public function getDayLabel($index)
    {
        $label = '';
        try {
            $dayArrayData = $this->dayLabelsFull;
            if (!empty($dayArrayData[$index])) {
                $label = $dayArrayData[$index];
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getDayLabel Exception : ".$e->getMessage());
        }
        return $label;
    }

    /**
     * getValidBookingDates
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array|string
     */
    public function getValidBookingDates($product)
    {
        $preventDuration = $product['prevent_scheduling_before'];

        $bookingAvailableFrom = date(
            'm/d/Y H:i:s',
            strtotime(
                '+'.$preventDuration.' minutes',
                $this->getCurrentTime()
            )
        );
        $bookingAvailableTo = '';
        try {
            if (!$product['available_every_week']) {
                $frmFlag = 0;
                $toFlag = 0;
                if ($product['booking_available_from'] != 'Invalid date') {
                    if (strtotime($product['booking_available_from']) > strtotime($bookingAvailableFrom)) {
                        $bookingAvailableFrom = $product['booking_available_from'];
                        $frmFlag = 1;
                    }
                }
                if ($product['booking_available_to'] != 'Invalid date') {
                    $bookingAvailableTo = $product['booking_available_to'];
                    if (strtotime($bookingAvailableTo)< strtotime(date('m/d/Y'))) {
                        $bookingAvailableTo = $bookingAvailableFrom;
                        $toFlag = 1;
                    }
                }
                if ($toFlag === 1 && $frmFlag === 0) {
                    if ((strtotime($bookingAvailableFrom) === strtotime($bookingAvailableTo))) {
                        return '';
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getValidBookingDates Exception : ".$e->getMessage());
        }
        return [
            'booking_available_from' => $bookingAvailableFrom,
            'booking_available_to' => $bookingAvailableTo
        ];
    }

    /**
     * processBookingSave
     * Process Slot Data
     *
     * @param array  $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $isThrowError
     * @return void
     */
    public function processBookingSave($data, $product, $item, $isThrowError = 1)
    {
        if (!empty($data['booking_date']) && !empty($data['booking_time'])) {
            $error = 0;
            $currentTime = $this->getCurrentTime();
            $errorMessage = __('Invalid booking dates.');
            $selectedBookingDate = $data['booking_date'];
            $selectedBookingTime = $data['booking_time'];
            $bookedSlotDate = date(
                "d M, Y",
                strtotime($selectedBookingDate)
            )." ".$selectedBookingTime;

            if (empty($data['slot_day_index'])) {
                $data['parent_slot_id'] = 0;
                $data['slot_id'] = 0;
                $data['slot_day_index'] = 0;
            }
            $parentSlotId = $data['parent_slot_id'];
            $slotId = $data['slot_id'];
            $slotDayIndex = $data['slot_day_index'];

            // Check if selected booking dates are available or not
            $productId = $product->getId();
            $bookingInfo = $this->getBookingInfo($productId);
            $bookingSlotData = $this->getJsonDecodedString(
                $bookingInfo['info']
            );
            $slotData = [];
            $bookedData = $this->getBookedAppointmentDates($productId);
            if (empty($bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotId])) {
                $errorMessage = __('Invalid booking dates.');
                if ($isThrowError) {
                    $this->_checkoutSession->getQuote()->setHasError(true);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        $errorMessage
                    );
                } else {
                    $this->_messageManager->addError($errorMessage);
                }
            } else {
                $slotData = $bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotId];

                $bookedDay = date("l", strtotime($selectedBookingDate));
                $bookedDayIndex = $this->getDayIndexId($bookedDay);
                if (!empty($bookingSlotData[$bookedDayIndex])) {
                    // if selected time slot is available
                    if (!empty($slotData['time'])) {
                        $selectedBookingTime = $slotData['time'];
                    } else {
                        $error = 1;
                    }
                    if (!$error) {
                        // check if selected booking dates are for today then slot is available or not
                        if (strtotime($selectedBookingDate)===strtotime(date('m/d/Y'))) {
                            if (!($currentTime <= strtotime($selectedBookingTime))) {
                                $error = 1;
                                $errorMessage = __(
                                    'Slot %1 is not available.',
                                    $bookedSlotDate
                                );
                                if ($isThrowError) {
                                    $this->_checkoutSession->getQuote()->setHasError(true);
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        $errorMessage
                                    );
                                } else {
                                    $this->_messageManager->addError($errorMessage);
                                }
                            }
                        }
                        if (!$error) {
                            // get valid available booking dates
                            $validBookingDates = $this->getValidBookingDates($product);
                            $bookingAvailableFrom = $validBookingDates['booking_available_from'];
                            $bookingAvailableTo = $validBookingDates['booking_available_to'];
                            // check if selected booking dates are correct or not
                            $selectedBookingDateTime = date(
                                "m/d/Y",
                                strtotime($selectedBookingDate)
                            )." ".$selectedBookingTime;
                            if (!(strtotime($bookingAvailableFrom) <= strtotime($selectedBookingDateTime))) {
                                $error = 1;
                                $errorMessage = __('Invalid booking dates.');
                                if ($isThrowError) {
                                    $this->_checkoutSession->getQuote()->setHasError(true);
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        $errorMessage
                                    );
                                } else {
                                    $this->_messageManager->addError($errorMessage);
                                }
                            }
                            if (!$product['available_every_week']) {
                                if (!(strtotime($selectedBookingDate)<=strtotime($bookingAvailableTo))) {
                                    $error = 1;
                                    $errorMessage = __('Invalid booking dates.');
                                    if ($isThrowError) {
                                        $this->_checkoutSession->getQuote()->setHasError(true);
                                        throw new \Magento\Framework\Exception\LocalizedException(
                                            $errorMessage
                                        );
                                    } else {
                                        $this->_messageManager->addError($errorMessage);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $error = 1;
                }
                if ($error) {
                    if ($isThrowError) {
                        $this->_checkoutSession->getQuote()->setHasError(true);
                        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
                    } else {
                        $this->_messageManager->addError($errorMessage);
                    }
                }
                $availableQty = 0;
                if (!empty($slotData['qty'])) {
                    $availableQty = $slotData['qty'];
                }
                if (!empty($bookedData[strtotime($selectedBookingDate)][$selectedBookingTime])) {
                    $bookedQty = $bookedData[strtotime($selectedBookingDate)][$selectedBookingTime];
                    if ($bookedQty > $availableQty) {
                        $availableQty = 0;
                    } else {
                        $availableQty = $availableQty - $bookedQty;
                    }
                }
                $requestedQty = $item->getQty();
                if (!$availableQty) {
                    $errorMessage = __(
                        '%1 quantity is not available for slot %2.',
                        $item->getName(),
                        $bookedSlotDate
                    );

                    $item->setHasError(true);
                    $item->setMessage([$errorMessage]);
                    if ($item->getId()) {
                        $item->delete();
                    }
                } else {
                    if ($requestedQty > $availableQty) {
                        $item->setQty($availableQty)->save();
                        $error = 1;

                        $errorMessage = __(
                            'Only %1 quantity is available for %2 for slot %3.',
                            $availableQty,
                            $item->getName(),
                            $bookedSlotDate
                        );
                        $item->getQuote()->collectTotals()->save();
                        $this->_messageManager->addError($errorMessage);
                    }
                    // save slot item data in booking quote table
                    if ($itemId = $item->getId()) {
                        $collection = $this->_quoteCollection->create();
                        $bookingQuote = $this->getDataByField($itemId, 'item_id', $collection);
                        if (!empty($item->getQuoteId())) {
                            $quoteId = $item->getQuoteId();
                        } else {
                            $quoteId = $this->_checkoutSession->getQuote()->getId();
                        }
                        if (!$bookingQuote) {
                            $data =  [
                                'item_id' => $itemId,
                                'slot_id' => $slotId,
                                'parent_slot_id' => $parentSlotId,
                                'slot_day_index' => $slotDayIndex,
                                'slot_date' => $selectedBookingDate,
                                'slot_time' => $selectedBookingTime,
                                'quote_id' => $quoteId,
                                'product_id' => $productId
                            ];
                            $this->_quote->create()->setData($data)->save();
                        }
                    }
                }
            }
        }

        return $error;
    }

    /**
     * checkItemQtyAvilableForHotel
     * Process Hotel Booking Save Data
     *
     * @param array  $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @param object $bookingQuote
     * @return void
     */
    public function checkItemQtyAvilableForHotel($data, $product, $item, $bookingQuote)
    {
        $result = $this->getBookedHotelDates($product);
        $error = false;
        $errorMessage = __("something went wrong !!!");

        if (!empty($data['selected_configurable_option'])
            && !empty($data['options'])
        ) {
            $selectedProductId = $data['selected_configurable_option'];
            // Check if selected booking options are available or not
            $bookingDateOptions = $this->getHotelBookingDateOptions($product);
            $bookingFromDate = $bookingToDate = 0;
            $actualAssociatedQty = $this->getStockData($selectedProductId)->getQty();

            if (!empty($bookingDateOptions)) {
                foreach ($bookingDateOptions as $optionId => $optionValues) {
                    if ($optionValues['title'] == "Booking From") {
                        $bookingFromDate = $optionId;
                    } elseif ($optionValues['title'] == "Booking Till") {
                        $bookingToDate = $optionId;
                    }
                }
            }

            if (isset($data['options'][$bookingFromDate])
                && isset($data['options'][$bookingToDate])
            ) {
                $roomBookingFrom = $data['options'][$bookingFromDate];
                $roomBookingTo = $data['options'][$bookingToDate];
                $errorMessage = __(
                    'Room(s) are not available during %1 to %2.',
                    $roomBookingFrom,
                    $roomBookingTo
                );
                $selectedBookingDateFrom = strtotime($roomBookingFrom);
                $selectedBookingDateTo = strtotime($roomBookingTo);

                if (isset($result[$selectedProductId]['booked_dates'])) {
                    $_array = [];
                    $bookedDatesArr = $result[$selectedProductId]['booked_dates'];
                    foreach ($bookedDatesArr as $bookedDate => $qtyAvailable) {
                        $bookedDatesStr = strtotime($bookedDate);
                        if ($bookedDatesStr >= $selectedBookingDateFrom && $bookedDatesStr <= $selectedBookingDateTo) {
                            $_array[] = $qtyAvailable;
                        }
                    }
                    if (count($_array)>0) {
                        $actualQtyAvailable = min($_array);
                        if ($actualQtyAvailable!=="" && $actualQtyAvailable == 0) {
                            $error = true;
                        } elseif ($actualQtyAvailable
                            && $actualQtyAvailable > 0
                            && $item->getProduct()->getCartQty() > $actualQtyAvailable
                        ) {
                            $error = true;
                            if ($actualQtyAvailable > 0) {
                                $errorMessage = __(
                                    'Only %1 Room(s) are available during %2 to %3.',
                                    $actualQtyAvailable,
                                    $roomBookingFrom,
                                    $roomBookingTo
                                );
                            }
                        }
                    }
                }

                if (!$error) {
                    if ($bookingQuote->getSlotDate() && $bookingQuote->getToSlotDate()
                        && !(($actualAssociatedQty - $bookingQuote->getQty()) >= $data['qty'])
                    ) {
                        $error = true;
                        if ($actualAssociatedQty - $bookingQuote->getQty() > 0) {
                            $errorMessage = __(
                                'Only %1 Room(s) are available during %2 to %3.',
                                $actualAssociatedQty - $bookingQuote->getQty(),
                                $roomBookingFrom,
                                $roomBookingTo
                            );
                        }
                    }
                }
            }
        }
        if ($error) {
            $this->_checkoutSession->getQuote()->setHasError(true);
            throw new \Magento\Framework\Exception\LocalizedException(
                $errorMessage
            );
        }
    }

    /**
     * checkIsHotelBookedForDateRange
     * Check Hotel Is booked for selected Date range or not
     *
     * @param array  $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @return void
     */
    public function checkIsHotelBookedForDateRange($data, $product, $item)
    {
        $result = $this->getBookedHotelDates($product);
        $error = false;
        $errorMessage = __("something went wrong !!!");
        if (!empty($data['selected_configurable_option'])
            && !empty($data['options'])
        ) {
            // Check if selected booking options are available or not
            $bookingDateOptions = $this->getHotelBookingDateOptions($product);
            $bookingFromDate = $bookingToDate = 0;
            $selectedProductId = $data['selected_configurable_option'];
            $actualAssociatedQty = $this->getStockData($selectedProductId)->getQty();

            if (!empty($bookingDateOptions)) {
                foreach ($bookingDateOptions as $optionId => $optionValues) {
                    if ($optionValues['title'] == "Booking From") {
                        $bookingFromDate = $optionId;
                    } elseif ($optionValues['title'] == "Booking Till") {
                        $bookingToDate = $optionId;
                    }
                }
            }

            if (isset($data['options'][$bookingFromDate]) && isset($data['options'][$bookingToDate])) {
                $errorMessage = __(
                    'Room(s) are not available during %1 to %2.',
                    $data['options'][$bookingFromDate],
                    $data['options'][$bookingToDate]
                );
                $selectedBookingDateFrom = strtotime($data['options'][$bookingFromDate]);
                $selectedBookingDateTo = strtotime($data['options'][$bookingToDate]);

                if (isset($result[$selectedProductId]['booked_dates'])) {
                    $_array = [];
                    foreach ($result[$selectedProductId]['booked_dates'] as $bookedDate => $qtyAvailable) {
                        $bookedDatesStr = strtotime($bookedDate);
                        if ($bookedDatesStr >= $selectedBookingDateFrom && $bookedDatesStr <= $selectedBookingDateTo) {
                            $_array[] = $qtyAvailable;
                        }
                    }
                    if (count($_array)>0) {
                        $actualQtyAvailable = min($_array);
                        if ($actualQtyAvailable!=="" && $actualQtyAvailable == 0) {
                            $error = true;
                        } elseif ($actualQtyAvailable
                            && $actualQtyAvailable > 0
                            && $item->getProduct()->getCartQty() > $actualQtyAvailable
                        ) {
                            $error = true;
                            if ($actualQtyAvailable > 0) {
                                $errorMessage = __(
                                    'Only %1 Room(s) are available during %2 to %3.',
                                    $actualQtyAvailable,
                                    $data['options'][$bookingFromDate],
                                    $data['options'][$bookingToDate]
                                );
                            }
                        }
                    }
                }

                if (!$error) {
                    $items =  $this->_checkoutSession->getQuote()->getAllVisibleItems();

                    foreach ($items as $tempItem) {
                        if ($tempItem->getProductType()=="hotelbooking"
                            && $tempItem->getId()
                        ) {
                            $itemData = $tempItem->getBuyRequest()->getData();
                            $tempSelectedProductId = $itemData['selected_configurable_option'];

                            if ($tempSelectedProductId == $selectedProductId
                                && isset($itemData['options'][$bookingFromDate])
                                && isset($itemData['options'][$bookingToDate])
                            ) {
                                $tempSelectedBookingDateFrom = strtotime($itemData['options'][$bookingFromDate]);
                                $tempSelectedBookingDateTo = strtotime($itemData['options'][$bookingToDate]);

                                $remainingQty = $actualAssociatedQty - $tempItem->getQty();

                                if ($selectedBookingDateTo < $tempSelectedBookingDateFrom
                                    || $selectedBookingDateFrom > $tempSelectedBookingDateTo
                                ) {
                                } elseif ($remainingQty >= $item->getProduct()->getCartQty()) {
                                } else {
                                    $error = true;
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($error) {
            $this->_checkoutSession->getQuote()->setHasError(true);
            throw new \Magento\Framework\Exception\LocalizedException(
                $errorMessage
            );
        }
    }

    /**
     * getAvailableHotelDates
     *
     * @param \Magento\Catalog\Model\Product  $product
     * @return array
     */
    public function getAvailableHotelDates($product)
    {
        $data = [];
        try {
            $info = $this->getBookingInfo($product->getId());
            if (isset($info['is_booking']) && $info['is_booking'] && isset($info['info'])) {
                $info  = $this->getJsonDecodedString(
                    $info['info']
                );
                if (!empty($info)) {
                    foreach ($info as $id => $childData) {
                        $data[$id] = $childData['qty'];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getAvailableHotelDates Exception : ".$e->getMessage());
        }
        return $data;
    }

    /**
     * getBookedHotelDates
     *
     * @param \Magento\Catalog\Model\Product  $product
     * @return array
     */
    public function getBookedHotelDates($product)
    {
        $data = [];
        try {
            $collection = $this->_bookedCollection
                ->create()
                ->addFieldToFilter("product_id", $product->getId());
            $info = $this->getBookingInfo($product->getId());
            if (isset($info['is_booking']) && $info['is_booking'] && isset($info['info'])) {
                $info  = $this->getJsonDecodedString(
                    $info['info']
                );
            }
            if ($collection->getSize()) {
                foreach ($collection as $bookedData) {
                    $rangeDates = $this->calculateBookedDatesFromRange(
                        $bookedData->getBookingFrom(),
                        $bookedData->getBookingToo()
                    );

                    if (!isset($data[$bookedData->getChildProductId()])) {
                        $data[$bookedData->getChildProductId()]['booked_dates'] = [];
                        $availableQty = $this->getStockData($bookedData->getChildProductId())->getQty();
                        if (array_key_exists($bookedData->getChildProductId(), $info)) {
                            $availableQty = (int)$info[$bookedData->getChildProductId()]['qty'];
                        }
                    }
                    foreach ($rangeDates as $dates) {
                        $bookedQty = (int)$bookedData->getQty();
                        if (isset($data[$bookedData->getChildProductId()]['booked_dates'][$dates])) {
                            if ($data[$bookedData->getChildProductId()]['booked_dates'][$dates] > $bookedQty) {
                                $data[$bookedData->getChildProductId()]['booked_dates'][$dates] -= $bookedQty;
                            } else {
                                $data[$bookedData->getChildProductId()]['booked_dates'][$dates] = 0;
                            }
                        } else {
                            $remainingQty = $availableQty - $bookedQty;
                            $data[$bookedData->getChildProductId()]['booked_dates'][$dates] = $remainingQty;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getBookedHotelDates Exception : ".$e->getMessage());
        }
        return $data;
    }

    /**
     * calculateBookedDatesFromRange
     *
     * @param string $bookedFrom
     * @param string $bookedTo
     * @return array
     */
    public function calculateBookedDatesFromRange($bookedFrom, $bookedTo)
    {
        $range = [];
        $format = "d M, Y";
        try {
            $begin = new \DateTime($bookedFrom);
            $end = new \DateTime($bookedTo);

            $interval = new \DateInterval('P1D'); // 1 Day
            $dateRange = new \DatePeriod($begin, $interval, $end);

            foreach ($dateRange as $date) {
                $range[] = $date->format($format);
            }
            // $range[] = date($format, strtotime($bookedTo));
            if (empty($range)) {
                $range[] = date($format, strtotime($bookedFrom));
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_calculateBookedDatesFromRange Exception : ".$e->getMessage());
        }
        return $range;
    }

    /**
     * processHotelBookingSave
     * Process Hotel Booking Save Data
     *
     * @param array                           $data
     * @param \Magento\Catalog\Model\Product  $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int                             $isThrowError
     * @return void
     */
    public function processHotelBookingSave($data, $product, $item, $isThrowError = 1)
    {
        if (!empty($data['selected_configurable_option'])
            && !empty($data['options'])
        ) {
            if (isset($data['hotel_qty'])
                && $data['hotel_qty'] > 1
            ) {
                $price = $item->getProduct()->getFinalPrice();
                $item->setCustomPrice($price*$data['hotel_qty']);
                $item->setOriginalCustomPrice($price*$data['hotel_qty']);
            }

            // Check if selected booking options are available or not
            $error = 0;
            $errorMessage = __('Invalid booking dates.');
            $productId = $product->getId();
            $bookingDateOptions = $this->getHotelBookingDateOptions($product);
            $bookingFromDate = $bookingToDate = 0;

            if (!empty($bookingDateOptions)) {
                foreach ($bookingDateOptions as $optionId => $optionValues) {
                    if ($optionValues['title'] == "Booking From") {
                        $bookingFromDate = $optionId;
                    } elseif ($optionValues['title'] == "Booking Till") {
                        $bookingToDate = $optionId;
                    }
                }
            }

            if (isset($data['options'][$bookingFromDate]) && isset($data['options'][$bookingToDate])) {
                $selectedBookingDateFrom = $data['options'][$bookingFromDate];
                $selectedBookingDateTo = $data['options'][$bookingToDate];
                if (!$error) {
                    // get valid available booking dates
                    $bookingAvailableFrom = $this->getCurrentDate();
                    // check if selected booking dates are correct or not
                    if (!(strtotime($bookingAvailableFrom)<=strtotime($selectedBookingDateFrom))) {
                        $error = 1;
                        $errorMessage = __('Invalid booking dates.');
                        if ($isThrowError) {
                            $this->_checkoutSession->getQuote()->setHasError(true);
                            throw new \Magento\Framework\Exception\LocalizedException(
                                $errorMessage
                            );
                        } else {
                            $this->_messageManager->addError($errorMessage);
                        }
                    }
                } else {
                    $error = 1;
                }
                if ($error) {
                    if ($isThrowError) {
                        $this->_checkoutSession->getQuote()->setHasError(true);
                        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
                    } else {
                        $this->_messageManager->addError($errorMessage);
                    }
                }
                // save slot item data in booking quote table
                if ($itemId = $item->getId()) {
                    $collection = $this->_quoteCollection->create();
                    $bookingQuote = $this->getDataByField($itemId, 'item_id', $collection);
                    if (!empty($item->getQuoteId())) {
                        $quoteId = $item->getQuoteId();
                    } else {
                        $quoteId = $this->_checkoutSession->getQuote()->getId();
                    }
                    if (!$bookingQuote) {
                        $data =  [
                            'item_id' => $itemId,
                            'slot_id' => 0,
                            'parent_slot_id' => 0,
                            'slot_day_index' => 0,
                            'slot_date' => $selectedBookingDateFrom,
                            'to_slot_date' => $selectedBookingDateTo,
                            'quote_id' => $quoteId,
                            'qty' => $item->getQty(),
                            'product_id' => $productId
                        ];
                        $this->_quote->create()->setData($data)->save();
                    }
                }
            }
        }
    }

    /**
     * processEventBookingSave
     * Process Event Booking Save Data
     *
     * @param array  $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $isThrowError
     * @return void
     */
    public function processEventBookingSave($data, $product, $item, $isThrowError = 1)
    {
        if (!empty($data['options'])) {
            $errorMessage = __('Invalid Tickets.');
            // Check if selected booking options are available or not
            $productId = $product->getId();
            $bookingInfo = $this->getBookingInfo($productId);
            $eventOptionsData = [];
            $eventOptions = $this->getEventOptions($product);
            if (!empty($eventOptions['event_ticket'])) {
                $eventOptionsData = $eventOptions['event_ticket'];
            }
            $eventDateStart = date(
                'Y-m-d',
                strtotime($bookingInfo['start_date'])
            );
            $eventTimeStart =  date(
                'h:i a',
                strtotime($bookingInfo['start_date'])
            );
            $eventDateEnd = date(
                'Y-m-d',
                strtotime($bookingInfo['end_date'])
            );
            $eventTimeEnd =  date(
                'h:i a',
                strtotime($bookingInfo['end_date'])
            );
            $savedOptionId = 0;
            // get saved event option id
            if (!empty($eventOptionsData['option_id'])) {
                $savedOptionId = $eventOptionsData['option_id'];
            }

            // if product is added with saved event option id
            if (!empty($data['options'][$savedOptionId])) {
                // if product option have only one ticket value
                if (count($data['options'][$savedOptionId]) == 1) {
                    $optionValId = $data['options'][$savedOptionId][0];
                    $savedOptionValues = [];
                    $savedOptionQty = [];
                    $savedOptionTitle = [];
                    $savedOptionInStock = [];

                    // check for booked data
                    $bookedData = $this->getBookedEventData($productId, $bookingInfo, $savedOptionId, $optionValId);

                    foreach ($eventOptionsData['option_values'] as $key => $value) {
                        if (empty($value['option_type_id'])) {
                            break;
                        }
                        array_push($savedOptionValues, $value['option_type_id']);
                        $savedOptionQty[$value['option_type_id']] = $value['qty'];
                        $savedOptionTitle[$value['option_type_id']] = $value['title'];
                        $savedOptionInStock[$value['option_type_id']] = $value['is_in_stock'];
                    }
                    // if product option ticket value is exist or not
                    if (!in_array($optionValId, $savedOptionValues)) {
                        $errorMessage = __('Tickets is not available.');
                        if ($isThrowError) {
                            $this->_checkoutSession->getQuote()->setHasError(true);
                            throw new \Magento\Framework\Exception\LocalizedException(
                                $errorMessage
                            );
                        } else {
                            $this->_messageManager->addError($errorMessage);
                        }
                    }
                    if (!empty($savedOptionQty[$optionValId])) {
                        $availableQty = $savedOptionQty[$optionValId];
                    } else {
                        $availableQty = 0;
                    }

                    if (!empty($bookedData[$savedOptionId][$optionValId])) {
                        $bookedQty = $bookedData[$savedOptionId][$optionValId];
                        if ($bookedQty > $availableQty) {
                            $availableQty = 0;
                        } else {
                            $availableQty = $availableQty - $bookedQty;
                        }
                    }
                    if (empty($savedOptionInStock[$optionValId]) || !$availableQty) {
                        $errorMessage = __(
                            'Ticket for "%1" is out of stock.',
                            $item->getName()
                        );

                        $item->setHasError(true);
                        $item->setMessage([$errorMessage]);
                        if ($item->getId()) {
                            $item->delete();
                        }
                    } else {
                        if ($item->getQty()>$availableQty) {
                            $item->setQty($availableQty)->save();
                            if ($item->getId()) {
                                $itemData = [$item->getId() => ['qty' => $availableQty]];
                                $this->_cart->updateItems($itemData)->save();
                            }
                            if (empty($savedOptionTitle[$optionValId])) {
                                $savedOptionTitle[$optionValId] = '';
                            }
                            $errorMessage = __(
                                'Only %1 quantity is available for %2 ticket "%3".',
                                $availableQty,
                                $item->getName(),
                                $savedOptionTitle[$optionValId]
                            );
                            if ($isThrowError) {
                                $this->_checkoutSession->getQuote()->setHasError(true);
                                throw new \Magento\Framework\Exception\LocalizedException(
                                    $errorMessage
                                );
                            } else {
                                $this->_messageManager->addError($errorMessage);
                                $this->_checkoutSession->getQuote()->collectTotals();
                            }
                        }
                        // save slot item data in booking quote table
                        if ($itemId = $item->getId()) {
                            $collection = $this->_quoteCollection->create();
                            $bookingQuote = $this->getDataByField($itemId, 'item_id', $collection);
                            if (!empty($item->getQuoteId())) {
                                $quoteId = $item->getQuoteId();
                            } else {
                                $quoteId = $this->_checkoutSession->getQuote()->getId();
                            }
                            if (!$bookingQuote) {
                                $data =  [
                                    'item_id' => $itemId,
                                    'slot_id' => $optionValId,
                                    'parent_slot_id' => $savedOptionId,
                                    'slot_day_index' => '',
                                    'slot_date' => $eventDateStart,
                                    'slot_time' => $eventTimeStart,
                                    'to_slot_date' => $eventDateEnd,
                                    'to_slot_time' => $eventTimeEnd,
                                    'quote_id' => $quoteId,
                                    'qty' => $item->getQty(),
                                    'product_id' => $productId
                                ];
                                $this->_quote->create()->setData($data)->save();
                            }
                        }
                    }
                }
            } else {
                if ($isThrowError) {
                    $this->_checkoutSession->getQuote()->setHasError(true);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        $errorMessage
                    );
                } else {
                    $this->_messageManager->addError($errorMessage);
                }
            }
        }
    }

    /**
     * processRentBookingSave
     * Process Rent Booking Save
     *
     * @param array  $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $isThrowError
     * @return void
     */
    public function processRentBookingSave($data, $product, $item, $isThrowError = 1)
    {
        if (!empty($data['options']) && !empty($data['booking_date_from'])) {
            $rentOptions = $this->getRentOptions($product);
            $currentTime = $this->getCurrentTime();
            $rentFromOptId = '';
            if (!empty($rentOptions['rent_from']['option_id'])) {
                $rentFromOptId = $rentOptions['rent_from']['option_id'];
            }
            $rentToOptId = '';
            if (!empty($rentOptions['rent_to']['option_id'])) {
                $rentToOptId = $rentOptions['rent_to']['option_id'];
            }
            $fromDateFromated = '';
            if (!empty($data['options'][$rentFromOptId])) {
                $fromDateFromated = $data['options'][$rentFromOptId];
            }
            $toDateFromated = '';
            if (!empty($data['options'][$rentToOptId])) {
                $toDateFromated = $data['options'][$rentToOptId];
            }
            $error = 0;
            $errorMessage = __('Invalid booking dates.');
            if (empty($data['slot_day_index'])) {
                $data['parent_slot_id'] = 0;
                $data['slot_id'] = 0;
                $data['slot_day_index'] = 0;
                $data['booking_from_time'] = 0;
                $data['booking_to_time'] = 0;
            }
            $parentSlotId = $data['parent_slot_id'];
            $slotId = $data['slot_id'];
            $slotIdFrom = $data['booking_from_time'];
            $slotIdTo = $data['booking_to_time'];
            $slotDayIndex = $data['slot_day_index'];

            // Check if selected booking dates are available or not
            $productId = $product->getId();
            $bookingInfo = $this->getBookingInfo($productId);

            $availableSavedQty = $bookingInfo['available_qty'];
            $bookingSlotData = $this->getJsonDecodedString(
                $bookingInfo['info']
            );
            $isSlotExisted = 0;
            $slotDataFrom = [];
            $slotDataTo = [];
            $rentType = Info::RENT_TYPE_DAILY;
            if ($slotDayIndex) {
                $rentType = Info::RENT_TYPE_HOURLY;
                if (!empty($bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotIdFrom])
                    && !empty($bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotIdTo])
                ) {
                    $isSlotExisted = 1;
                }
            }

            $bookedSlotFromDate = $data['booking_date_from'];
            if ($rentType == Info::RENT_TYPE_HOURLY) {
                $bookedSlotToDate = $data['booking_date_from'];
                $selectedBookingFromTime = '';
                $selectedBookingToTime = '';
            } else {
                $bookedSlotToDate = $data['booking_date_to'];
                $selectedBookingFromTime = date(
                    "h:i a",
                    strtotime($bookedSlotFromDate)
                );
                $selectedBookingToTime = '11:59 pm';
            }
            $selectedBookingFromDate = date(
                "Y-m-d",
                strtotime($bookedSlotFromDate)
            );
            $selectedBookingToDate = date(
                "Y-m-d",
                strtotime($bookedSlotToDate)
            );

            if ($rentType == Info::RENT_TYPE_HOURLY && !$isSlotExisted) {
                $errorMessage = __('Invalid booking dates.');
                if ($isThrowError) {
                    $this->_checkoutSession->getQuote()->setHasError(true);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        $errorMessage
                    );
                } else {
                    $this->_messageManager->addError($errorMessage);
                }
            } elseif ($rentType == Info::RENT_TYPE_DAILY) {
                // get valid available booking dates
                $validBookingDates = $this->getValidBookingDates($product);
                $bookingAvailableFrom = $validBookingDates['booking_available_from'];
                $bookingAvailableTo = $validBookingDates['booking_available_to'];
                // check if selected booking dates are correct or not
                if (!(strtotime($bookingAvailableFrom)<=strtotime($bookedSlotFromDate))) {
                    $error = 1;
                    $errorMessage = __('Invalid booking dates.');
                    if ($isThrowError) {
                        $this->_checkoutSession->getQuote()->setHasError(true);
                        throw new \Magento\Framework\Exception\LocalizedException(
                            $errorMessage
                        );
                    } else {
                        $this->_messageManager->addError($errorMessage);
                    }
                }
                if (!$product['available_every_week']) {
                    if (!(strtotime($bookedSlotToDate)<=strtotime($bookingAvailableTo))) {
                        $error = 1;
                        $errorMessage = __('Invalid booking dates.');
                        if ($isThrowError) {
                            $this->_checkoutSession->getQuote()->setHasError(true);
                            throw new \Magento\Framework\Exception\LocalizedException(
                                $errorMessage
                            );
                        } else {
                            $this->_messageManager->addError($errorMessage);
                        }
                    }
                }
                $totalOrderedQty = $this->getTotalOrderedRentedQty(
                    $productId,
                    $item->getId(),
                    $selectedBookingFromDate,
                    $selectedBookingToDate
                );
                if ($totalOrderedQty > $availableSavedQty) {
                    $totalOrderedQty = $availableSavedQty;
                }
                $availableSavedQty = $availableSavedQty - $totalOrderedQty;
                if ($availableSavedQty <= 0) {
                    $errorMessage = __(
                        '%1 is not available for dates %2 to %3.',
                        $item->getName(),
                        $fromDateFromated,
                        $toDateFromated
                    );

                    $item->setHasError(true);
                    $item->setMessage([$errorMessage]);
                    if ($item->getId()) {
                        $item->delete();
                    }
                } else {
                    $requestedQty = $item->getQty();
                    if ($requestedQty > $availableSavedQty) {
                        $item->setQty($availableSavedQty)->save();
                        $this->getCart()->save();
                        $error = 1;
                        $errorMessage = __(
                            'Only %1 quantity is available for %2 for dates %3 to %4.',
                            $availableSavedQty,
                            $item->getName(),
                            $fromDateFromated,
                            $toDateFromated
                        );
                        if ($isThrowError) {
                            $this->_checkoutSession->getQuote()->setHasError(true);
                            throw new \Magento\Framework\Exception\LocalizedException(
                                $errorMessage
                            );
                        } else {
                            $this->_messageManager->addError($errorMessage);
                        }

                        if ($item->getId()) {
                            //to update cart item data in booking quote table
                            $collection = $this->_quoteCollection->create();
                            $bookingQuote = $this->getDataByField($item->getId(), 'item_id', $collection);
                            if (!empty($item->getQuoteId())) {
                                $quoteId = $item->getQuoteId();
                            } else {
                                $quoteId = $this->_checkoutSession->getQuote()->getId();
                            }
                            if (!$bookingQuote) {
                                $data =  [
                                    'item_id' => $item->getId(),
                                    'slot_id' => $slotId,
                                    'parent_slot_id' => $parentSlotId,
                                    'slot_day_index' => $slotDayIndex,
                                    'slot_date' => $selectedBookingFromDate,
                                    'slot_time' => $selectedBookingFromTime,
                                    'to_slot_date' => $selectedBookingToDate,
                                    'to_slot_time' => $selectedBookingToTime,
                                    'rent_type' => $rentType,
                                    'quote_id' => $quoteId,
                                    'qty' => $item->getQty(),
                                    'product_id' => $productId
                                ];
                                $this->_quote->create()->setData($data)->save();
                            }
                        } else {
                            $this->getCart()->removeItem($item->getId())->save();
                        }
                    }
                    // save slot item data in booking quote table
                    if ($error !==1 && $itemId = $item->getId()) {
                        $collection = $this->_quoteCollection->create();
                        $bookingQuote = $this->getDataByField($itemId, 'item_id', $collection);
                        if (!empty($item->getQuoteId())) {
                            $quoteId = $item->getQuoteId();
                        } else {
                            $quoteId = $this->_checkoutSession->getQuote()->getId();
                        }
                        if (!$bookingQuote) {
                            $data =  [
                                'item_id' => $itemId,
                                'slot_id' => 0,
                                'parent_slot_id' => 0,
                                'slot_day_index' => 0,
                                'slot_date' => $selectedBookingFromDate,
                                'slot_time' => $selectedBookingFromTime,
                                'to_slot_date' => $selectedBookingToDate,
                                'to_slot_time' => $selectedBookingToTime,
                                'rent_type' => $rentType,
                                'quote_id' => $quoteId,
                                'qty' => $item->getQty(),
                                'product_id' => $productId
                            ];
                            $this->_quote->create()->setData($data)->save();
                        }
                    }
                }
            } elseif ($rentType == Info::RENT_TYPE_HOURLY && $isSlotExisted) {
                $bookingSlotDataArr = $bookingSlotData[$slotDayIndex][$parentSlotId];
                $slotDataFrom = $bookingSlotDataArr['slots_info'][$slotIdFrom];
                $slotDataTo = $bookingSlotDataArr['slots_info'][$slotIdTo];

                $bookedDay = date("l", strtotime($selectedBookingFromDate));
                $bookedDayIndex = $this->getDayIndexId($bookedDay);
                if (!empty($bookingSlotData[$bookedDayIndex])) {
                    // if selected time slot is available
                    if (empty($slotDataFrom['time']) && empty($slotDataTo['time'])) {
                        $error = 1;
                    } else {
                        $selectedBookingFromTime = $slotDataFrom['time'];
                        $selectedBookingToTime = $slotDataTo['time'];
                    }
                    if (!$error) {
                        // check if selected booking dates are for today then slot is available or not
                        if (strtotime($selectedBookingFromDate)===strtotime(date('m/d/Y'))) {
                            if (!($currentTime <= strtotime($selectedBookingFromTime))) {
                                $error = 1;
                                $errorMessage = __(
                                    'Rent from %1 to %2 is not available.',
                                    $fromDateFromated,
                                    $toDateFromated
                                );
                                if ($isThrowError) {
                                    $this->_checkoutSession->getQuote()->setHasError(true);
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        $errorMessage
                                    );
                                } else {
                                    $this->_messageManager->addError($errorMessage);
                                }
                            }
                        }
                        if (!$error) {
                            // get valid available booking dates
                            $validBookingDates = $this->getValidBookingDates($product);
                            $bookingAvailableFrom = $validBookingDates['booking_available_from'];
                            $bookingAvailableTo = $validBookingDates['booking_available_to'];
                            // check if selected booking dates are correct or not
                            if (!(strtotime($bookingAvailableFrom)<=strtotime($bookedSlotFromDate))) {
                                $error = 1;
                                $errorMessage = __('Invalid booking dates.');
                                if ($isThrowError) {
                                    $this->_checkoutSession->getQuote()->setHasError(true);
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        $errorMessage
                                    );
                                } else {
                                    $this->_messageManager->addError($errorMessage);
                                }
                            }
                            if (!$product['available_every_week']) {
                                if (!(strtotime($selectedBookingToDate)<=strtotime($bookingAvailableTo))) {
                                    $error = 1;
                                    $errorMessage = __('Invalid booking dates.');
                                    if ($isThrowError) {
                                        $this->_checkoutSession->getQuote()->setHasError(true);
                                        throw new \Magento\Framework\Exception\LocalizedException(
                                            $errorMessage
                                        );
                                    } else {
                                        $this->_messageManager->addError($errorMessage);
                                    }
                                }
                            }

                            $totalOrderedQty = $this->getTotalOrderedRentedQty(
                                $productId,
                                $item->getId(),
                                $selectedBookingFromDate,
                                $selectedBookingToDate
                            );
                            if ($totalOrderedQty > $availableSavedQty) {
                                $totalOrderedQty = $availableSavedQty;
                            }
                            $availableSavedQty = $availableSavedQty - $totalOrderedQty;
                            if ($availableSavedQty <= 0) {
                                $errorMessage = __(
                                    '%1 is not available from %2 to %3.',
                                    $item->getName(),
                                    $fromDateFromated,
                                    $toDateFromated
                                );

                                $item->setHasError(true);
                                $item->setMessage([$errorMessage]);
                                if ($item->getId()) {
                                    $item->delete();
                                }
                                // $error = 1;
                                return;
                            }
                        }
                    }
                } else {
                    $error = 1;
                }
                if ($error) {
                    if ($isThrowError) {
                        $this->_checkoutSession->getQuote()->setHasError(true);
                        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
                    } else {
                        $this->_messageManager->addError($errorMessage);
                    }
                }
                $availableFromQty = 0;
                $availableToQty = 0;
                $availableQty = 0;
                if (!empty($slotDataFrom['qty']) && !empty($slotDataTo['qty'])) {
                    $availableFromQty = $slotDataFrom['qty'];
                    $availableToQty = $slotDataTo['qty'];
                    $qtyDiff = $availableToQty - $availableFromQty;
                    if ($qtyDiff) {
                        $availableQty = $availableFromQty;
                    } else {
                        $availableQty = $availableToQty;
                    }
                }
                if ($availableQty <= 0) {
                    $errorMessage = __(
                        '%1 is not available for dates %2 to %3.',
                        $item->getName(),
                        $fromDateFromated,
                        $toDateFromated
                    );

                    $item->setHasError(true);
                    $item->setMessage([$errorMessage]);
                    if ($item->getId()) {
                        $item->delete();
                    }
                } else {
                    $requestedQty = $item->getQty();
                    if ($requestedQty > $availableFromQty || $requestedQty > $availableToQty) {
                        $item->setQty($availableQty)->save();
                        $this->getCart()->save();

                        $error = 1;
                        $errorMessage = __(
                            'Only %1 quantity is available for %2 for dates %3 to %4.',
                            $availableQty,
                            $item->getName(),
                            $fromDateFromated,
                            $toDateFromated
                        );
                        if ($isThrowError) {
                            $this->_checkoutSession->getQuote()->setHasError(true);
                            throw new \Magento\Framework\Exception\LocalizedException(
                                $errorMessage
                            );
                        } else {
                            $this->_messageManager->addError($errorMessage);
                            $this->_checkoutSession->getQuote()->collectTotals();
                        }
                    }
                    // save slot item data in booking quote table
                    if ($itemId = $item->getId()) {
                        $collection = $this->_quoteCollection->create();
                        $bookingQuote = $this->getDataByField($itemId, 'item_id', $collection);
                        if (!empty($item->getQuoteId())) {
                            $quoteId = $item->getQuoteId();
                        } else {
                            $quoteId = $this->_checkoutSession->getQuote()->getId();
                        }
                        if (!$bookingQuote) {
                            $data =  [
                                'item_id' => $itemId,
                                'slot_id' => $slotId,
                                'parent_slot_id' => $parentSlotId,
                                'slot_day_index' => $slotDayIndex,
                                'slot_date' => $selectedBookingFromDate,
                                'slot_time' => $selectedBookingFromTime,
                                'to_slot_date' => $selectedBookingToDate,
                                'to_slot_time' => $selectedBookingToTime,
                                'rent_type' => $rentType,
                                'quote_id' => $quoteId,
                                'qty' => $item->getQty(),
                                'product_id' => $productId
                            ];
                            $this->_quote->create()->setData($data)->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * processDefaultSlotData
     * Process Deafult Type Booking Slot Data
     *
     * @param array  $data
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int    $productId
     * @param int    $itemId
     * @param int    $isThrowError
     *
     * @return void
     */
    public function processDefaultSlotData($data, $item, $productId, $itemId, $isThrowError = 1)
    {
        $parentId = $this->getParentSlotId($productId);
        $slotId = (int) $data['slot_id'];
        $result = $this->processSlotData($data, $productId);
        if ($result['error']) {
            $this->_messageManager->addNotice(__($result['msg']));
            if ($isThrowError) {
                $this->_checkoutSession->getQuote()->setHasError(true);
                throw new \Magento\Framework\Exception\LocalizedException(__($result['msg']));
            }
        } else {
            if ($itemId > 0) {
                $collection = $this->_quoteCollection->create();
                $tempitem = $this->getDataByField($itemId, 'item_id', $collection);

                if (!$tempitem) {
                    $data =  [
                        'item_id' => $itemId,
                        'slot_id' => $slotId,
                        'parent_slot_id' => $parentId,
                        'quote_id' => $item->getQuoteId(),
                        'product_id' => $productId
                    ];
                    $this->_quote->create()->setData($data)->save();
                }
            }
        }
    }

    /**
     * processSlotData
     * Process Slot Data
     *
     * @param array $data
     * @param int $productId
     *
     * @return array
     */
    public function processSlotData($data, $productId)
    {
        $result = ['error' => false];
        try {
            if (empty($data['parent_id']) || empty($data['slot_id'])) {
                return $result;
            }
            $parentId = $this->getParentSlotId($productId);

            if ($parentId != $data['parent_id']) {
                $msg = __('There was some error while processing your request');
                $result = ['error' => true, 'msg' => $msg];
            }

            $slotId = (int) $data['slot_id'];
            if ($slotId == 0) {
                $msg = __('There was some error while processing your request');
                $result = ['error' => true, 'msg' => $msg];
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_processSlotData Exception : ".$e->getMessage());
        }
        return $result;
    }

    /**
     * currencyByStore
     * Returns price converted to current currency rate
     *
     * @param object $store
     * @param boolean $status
     * @return float
     */
    public function currencyByStore($price, $store, $status = false)
    {
        return $this->pricingHelper->currencyByStore($price, $store, $status);
    }

    /**
     * getJsonConfig
     * Returns option price amount
     *
     * @param float $customOptionPrice
     * @param float $price
     * @return float
     */
    public function getOptionPriceAmount($customOptionPrice, $price)
    {
        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        return $customOptionPrice->getCustomAmount($price, null, $context);
    }

    /**
     * Returns option json data
     *
     * @return json data
     */
    public function getJsonConfig()
    {
        return $this->optionsBlock->getJsonConfig();
    }

    /**
     * getOptionValueJsonConfig
     * Returns option json data
     *
     * @return array
     */
    public function getOptionValueJsonConfig()
    {
        $options = $this->getJsonConfig();
        return $optionsArr = $this->getJsonDecodedString($options);
    }

    /**
     * getEventOptions
     * Returns option json data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getEventOptions($product)
    {
        $eventOptions = [];
        $eventOptions['event_from'] = [];
        $eventOptions['event_to'] = [];
        $eventOptions['event_ticket'] = [];
        try {
            foreach ($product->getProductOptionsCollection() as $option) {
                if ($option['title']=='Event From') {
                    $optionId = $option->getId();
                    $eventOptions['event_from'] = [
                        'option_id'=>$optionId,
                        'option_values'=>[]
                    ];
                }
                if ($option['title']=='Event To') {
                    $optionId = $option->getId();
                    $eventOptions['event_to'] = [
                        'option_id'=>$optionId,
                        'option_values'=>[]
                    ];
                }
                if ($option['title']=='Event Tickets') {
                    $optionId = $option->getId();
                    $optionValues = $option->getValues();
                    $eventOptions['event_ticket'] = [
                        'option_id'=>$optionId,
                        'option_values'=>$optionValues
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getEventOptions Exception : ".$e->getMessage());
        }
        return $eventOptions;
    }

    /**
     * getHotelbookingOptions
     * Returns option json data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getHotelbookingOptions($product)
    {
        $optionData = [];
        try {
            foreach ($product->getProductOptionsCollection() as $option) {
                if (($option['title']=='Adults' || $option['title']=='Kids')
                    && $option['type'] == "field"
                ) {
                    $optionId = $option->getId();
                    $optionValues = $option->getData();
                    $optionData[$optionId] = $optionValues;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getHotelbookingOptions Exception : ".$e->getMessage());
        }
        return $optionData;
    }

    /**
     * getHotelBookingDateOptions
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getHotelBookingDateOptions($product)
    {
        $optionData = [];
        try {
            foreach ($product->getProductOptionsCollection() as $option) {
                if (($option['title']=='Booking From' || $option['title']=='Booking Till')
                    && $option['type'] == "field"
                ) {
                    $optionId = $option->getId();
                    $optionValues = $option->getData();
                    $optionData[$optionId] = $optionValues;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getHotelBookingDateOptions Exception : ".$e->getMessage());
        }
        return $optionData;
    }

    /**
     * getRentOptions
     * Returns option json data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getRentOptions($product)
    {
        $rentOptions = [];
        $rentOptions['rent_from'] = [];
        $rentOptions['rent_to'] = [];
        $rentOptions['choose_rent_type'] = [];
        try {
            foreach ($product->getProductOptionsCollection() as $option) {
                if ($option['title']=='Rent From') {
                    $optionId = $option->getId();
                    $rentOptions['rent_from'] = [
                        'option_id'=>$optionId,
                        'option_values'=>[]
                    ];
                }
                if ($option['title']=='Rent To') {
                    $optionId = $option->getId();
                    $rentOptions['rent_to'] = [
                        'option_id'=>$optionId,
                        'option_values'=>[]
                    ];
                }
                if ($option['title']=='Choose Rent Type') {
                    $optionId = $option->getId();
                    $optionValues = $option->getValues();
                    $rentOptions['choose_rent_type'] = [
                        'option_id'=>$optionId,
                        'option_values'=>$optionValues
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getRentOptions Exception : ".$e->getMessage());
        }
        return $rentOptions;
    }

    /**
     * getMediaUrl
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * getConfigValue
     * function to get Config Data.
     *
     * @param string|false $field
     * @return string|boolean
     */
    public function getConfigValue($field = false)
    {
        try {
            if ($field) {
                return $this->scopeConfig->getValue(
                    'mpadvancedbookingsystem/settings/'.$field,
                    ScopeInterface::SCOPE_STORE
                );
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getConfigValue Exception : ".$e->getMessage());
            return false;
        }
    }

    /**
     * getMpConfigValue
     * function to get Config Data.
     *
     * @param string|false $field
     * @return string|boolean
     */
    public function getMpConfigValue($field = false)
    {
        try {
            if ($field) {
                return $this->scopeConfig->getValue(
                    'mpadvancedbookingsystem/mp_settings/'.$field,
                    ScopeInterface::SCOPE_STORE
                );
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getMpConfigValue Exception : ".$e->getMessage());
            return false;
        }
    }

    /**
     * getHotelAddress
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getHotelAddress($product)
    {
        $address = [];
        try {
            if ($product["hotel_address"]) {
                $address[] = $product["hotel_address"];
            }
            if ($product["location"]) {
                $address[] = $product["location"];
            }
            if ($product["hotel_state"]) {
                $address[] = $product["hotel_state"];
            }
            if ($product["hotel_country"]) {
                $address[] = $product["hotel_country"];
            }
            if (count($address)>0) {
                $address = implode(", ", $address);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getHotelAddress Exception : ".$e->getMessage());
        }
        return $address;
    }

    /**
     * getCustomerId
     * Return Customer id.
     *
     * @return bool|0|1
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * isCustomerLoggedIn
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * getQuestionStatuses
     * Get question statuses with their codes
     *
     * @return array
     */
    public function getQuestionStatuses()
    {
        return [
            \Webkul\MpAdvancedBookingSystem\Model\Question::STATUS_APPROVED => __('Enabled'),
            \Webkul\MpAdvancedBookingSystem\Model\Question::STATUS_PENDING => __('Pending'),
            \Webkul\MpAdvancedBookingSystem\Model\Question::STATUS_NOT_APPROVED => __('Disabled')
        ];
    }

    /**
     * getReviewStatusesOptionArray
     * Get question statuses as option array
     *
     * @return array
     */
    public function getReviewStatusesOptionArray()
    {
        $result = [];
        try {
            foreach ($this->getQuestionStatuses() as $value => $label) {
                $result[] = ['value' => $value, 'label' => $label];
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getReviewStatusesOptionArray Exception : ".$e->getMessage());
        }
        return $result;
    }

    /**
     * getProductAttribute
     *
     * @param string $attrCode
     * @return object|boolean
     */
    public function getProductAttribute($attrCode)
    {
        try {
            return $this->productAttributeRepository->get($attrCode);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getProductAttribute Exception : ".$e->getMessage());
            return false;
        }
    }

    /**
     * readDirectory
     *
     * @param int $productId
     * @param int $optionId
     * @return string|boolean
     */
    public function readDirectory($productId, $optionId)
    {
        try {
            $path = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath(
                'catalog/product/'.$productId.'/'.$optionId.'/'
            );

            if (is_dir($path)) {
                $imagePaths = $this->filesystem->getDirectoryRead(
                    DirectoryList::MEDIA
                )->read($path);
                if (!empty($imagePaths) && is_array($imagePaths) && isset($imagePaths[0])) {
                    return $this->getMediaUrl()."/".$imagePaths[0];
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_readDirectory Exception : ".$e->getMessage());
            return false;
        }
    }

    /**
     * getCovertedPrice
     *
     * @param float $price
     * @return float
     */
    public function getCovertedPrice($price)
    {
        $price = $this->priceCurrency->convert(
            $price,
            $this->_checkoutSession->getQuote()->getStore()
        );
        return $price;
    }

    /**
     * getTotalOrderedRentedQty
     *
     * @param int $productId
     * @param int $itemId
     * @param string $fromDate
     * @param string $toDate
     * @return int
     */
    public function getTotalOrderedRentedQty($productId, $itemId, $fromDate, $toDate)
    {
        try {
            $collection = $this->_quoteCollection->create();
            $collection->addFieldToFilter('product_id', $productId);
            $collection->addFieldToFilter('slot_date', $fromDate);
            $collection->addFieldToFilter('rent_type', ['in' => [1,2]]);
            if ($itemId) {
                $collection->addFieldToFilter('item_id', ['neq' => $itemId]);
            }
            $totalOrderedQtyColl = $collection->getTotalOrderedQty();
            $totalOrderedQty = 0;
            foreach ($totalOrderedQtyColl as $key => $value) {
                $totalOrderedQty = $totalOrderedQty+$value->getQty();
            }
            $collection = $this->_quoteCollection->create();
            $collection->addFieldToFilter('product_id', $productId);
            $collection->addFieldToFilter('rent_type', ['in' => [1, 2]]);
            if ($itemId) {
                $collection->addFieldToFilter(
                    'item_id',
                    ['neq' => $itemId]
                );
            }
            /* one day is counted by same date in FROM and TO date
            two days are counted if two consecutive days are in FROM and TO date
            (e.g. 2 July,2018 - 3 July, 2018) and so on */
            $collection
            ->getSelect()
            ->where(
                'main_table.slot_date < "'.$fromDate.'" AND main_table.to_slot_date >= "'.$fromDate.'"'
            );
            $totalOrderedQtyColl = $collection->getTotalOrderedQty();
            foreach ($totalOrderedQtyColl as $key => $value) {
                $totalOrderedQty = $totalOrderedQty+$value->getQty();
            }
            $collection = $this->_quoteCollection->create();
            $collection->addFieldToFilter('product_id', $productId);
            $collection->addFieldToFilter('rent_type', ['in' => [1, 2]]);
            if ($itemId) {
                $collection->addFieldToFilter(
                    'item_id',
                    ['neq' => $itemId]
                );
            }
            $collection
            ->getSelect()
            ->where(
                'main_table.slot_date >= "'.$toDate.'" AND main_table.to_slot_date < "'.$toDate.'"'
            );
            $totalOrderedQtyColl = $collection->getTotalOrderedQty();
            foreach ($totalOrderedQtyColl as $key => $value) {
                $totalOrderedQty = $totalOrderedQty+$value->getQty();
            }
            return $totalOrderedQty;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getTotalOrderedRentedQty Exception : ".$e->getMessage());
            return $totalOrderedQty = 0;
        }
    }

    /**
     * processTableBookingSave
     * Process Slot Data for Table type Booking
     *
     * @param array  $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $isThrowError
     */
    public function processTableBookingSave($data, $product, $item, $isThrowError = 1)
    {
        if (!empty($data['booking_date']) && !empty($data['booking_time'])) {
            $error = 0;
            $currentTime = $this->getCurrentTime();
            $errorMessage = __('Invalid booking dates.');
            $selectedBookingDate = $data['booking_date'];
            $selectedBookingTime = $data['booking_time'];
            $bookedSlotDate = date(
                "d M, Y",
                strtotime($selectedBookingDate)
            )." ".$selectedBookingTime;
            if (empty($data['slot_day_index'])) {
                $data['slot_day_index'] = 0;
            }
            if (empty($data['slot_id'])) {
                $data['slot_id'] = 0;
            }
            if (empty($data['parent_slot_id'])) {
                $data['parent_slot_id'] = 0;
            }
            $parentSlotId = $data['parent_slot_id'];
            $slotId = $data['slot_id'];
            $slotDayIndex = $data['slot_day_index'];

            // Check if selected booking dates are available or not
            $productId = $product->getId();
            $bookingInfo = $this->getBookingInfo($productId);
            $bookingSlotData = $this->getJsonDecodedString(
                $bookingInfo['info']
            );
            $slotData = [];
            $bookedData = $this->getBookedAppointmentDates($productId);
            if (empty($bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotId])) {
                $errorMessage = __('Invalid booking dates.');
                if ($isThrowError) {
                    $this->_checkoutSession->getQuote()->setHasError(true);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        $errorMessage
                    );
                } else {
                    $this->_messageManager->addError($errorMessage);
                }
            } else {
                $slotData = $bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotId];
                $bookedDay = date("l", strtotime($selectedBookingDate));
                $bookedDayIndex = $this->getDayIndexId($bookedDay);

                if (!empty($bookingSlotData[$bookedDayIndex])) {
                    // if selected time slot is available
                    if (!empty($slotData['time'])) {
                        $selectedBookingTime = $slotData['time'];
                    } else {
                        $error = 1;
                    }
                    if (!$error) {
                        // check if selected booking dates are for today then slot is available or not
                        if (strtotime($selectedBookingDate)===strtotime(date('m/d/Y'))) {
                            if (!($currentTime <= strtotime($selectedBookingTime))) {
                                $error = 1;
                                $errorMessage = __(
                                    'Slot %1 is not available.',
                                    $bookedSlotDate
                                );
                                if ($isThrowError) {
                                    $this->_checkoutSession->getQuote()->setHasError(true);
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        $errorMessage
                                    );
                                } else {
                                    $this->_messageManager->addError($errorMessage);
                                }
                            }
                        }
                        if (!$error) {
                            // get valid available booking dates
                            $bookingAvailableFrom = date('m/d/Y');
                            $bookingAvailableTo = '';

                            // check if selected booking dates are correct or not
                            if (!(strtotime($bookingAvailableFrom)<=strtotime($selectedBookingDate))) {
                                $error = 1;
                                $errorMessage = __('Invalid booking dates.');
                                if ($isThrowError) {
                                    $this->_checkoutSession->getQuote()->setHasError(true);
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        $errorMessage
                                    );
                                } else {
                                    $this->_messageManager->addError($errorMessage);
                                }
                            }
                        }
                    }
                } else {
                    $error = 1;
                }
                if ($error) {
                    if ($isThrowError) {
                        $this->_checkoutSession->getQuote()->setHasError(true);
                        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
                    } else {
                        $this->_messageManager->addError($errorMessage);
                    }
                }
                $availableQty = 0;
                if (!empty($slotData['qty'])) {
                    $availableQty = $slotData['qty'];
                }
                if (!empty($bookedData[strtotime($selectedBookingDate)][$selectedBookingTime])) {
                    $bookedQty = $bookedData[strtotime($selectedBookingDate)][$selectedBookingTime];
                    if ($bookedQty > $availableQty) {
                        $availableQty = 0;
                    } else {
                        $availableQty = $availableQty - $bookedQty;
                    }
                }
                $requestedQty = $item->getQty();
                if (!empty($data['charged_per_count']) && $data['charged_per_count'] > 1) {
                    $requestedQty = $requestedQty * $data['charged_per_count'];
                }
                if (!$availableQty) {
                    $errorMessage = __(
                        '%1 quantity is not available for slot %2.',
                        $item->getName(),
                        $bookedSlotDate
                    );

                    $item->setHasError(true);
                    $item->setMessage([$errorMessage]);
                    if ($item->getId()) {
                        $item->delete();
                    }
                } else {
                    if ($requestedQty > $availableQty) {
                        if (!empty($data['charged_per_count']) && $data['charged_per_count'] > 1) {
                            $availableQty = $availableQty / $data['charged_per_count'];
                            $availableQty = (int) $availableQty;
                        }
                        $item->setQty($availableQty)->save();
                        $error = 1;
                        $errorMessage = __(
                            'Only %1 quantity is available for %2 for slot %3.',
                            $availableQty,
                            $item->getName(),
                            $bookedSlotDate
                        );

                        $cartData = [$item->getId() => ['qty' => (int) $availableQty]];
                        $this->_updateShoppingCart($cartData);
                        $this->_messageManager->addError($errorMessage);
                        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
                    }
                    // save slot item data in booking quote table
                    if ($itemId = $item->getId()) {
                        $collection = $this->_quoteCollection->create();
                        $bookingQuote = $this->getDataByField($itemId, 'item_id', $collection);
                        if (!empty($item->getQuoteId())) {
                            $quoteId = $item->getQuoteId();
                        } else {
                            $quoteId = $this->_checkoutSession->getQuote()->getId();
                        }
                        if (!$bookingQuote) {
                            $data =  [
                                'item_id' => $itemId,
                                'slot_id' => $slotId,
                                'parent_slot_id' => $parentSlotId,
                                'slot_day_index' => $slotDayIndex,
                                'slot_date' => $selectedBookingDate,
                                'slot_time' => $selectedBookingTime,
                                'quote_id' => $quoteId,
                                'product_id' => $productId
                            ];
                            $this->_quote->create()->setData($data)->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * getMaxGuestsAvailable
     *
     * @return array
     */
    public function getMaxGuestsAvailable()
    {
        $guestsArr = [];
        try {
            $items =  $this->_checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($items as $item) {
                $productAttrSetId = $item->getProduct()->getAttributeSetId();
                $tableAttrSetId = $this->getProductAttributeSetIdByLabel(
                    'Table Booking'
                );
                if ($this->isBookingProduct($item->getProductId())
                    && $productAttrSetId == $tableAttrSetId
                ) {
                    $data = $item->getBuyRequest()->getData();
                    $selectedBookingDate = $data['booking_date'];
                    $selectedBookingTime = $data['booking_time'];
                    $bookedSlotDate = strtotime($selectedBookingDate . " " . $selectedBookingTime);
                    $noOfGuests = $item->getQty();
                    if (!empty($data['charged_per_count'])
                        && $data['charged_per_count'] > 1
                    ) {
                        $noOfGuests = $noOfGuests * $data['charged_per_count'];
                    }
                    if (!empty($guestsArr[$item->getProductId()])) {
                        if (!empty($guestsArr[$item->getProductId()][$bookedSlotDate])) {
                            $guestsArr[$item->getProductId()][$bookedSlotDate] += $noOfGuests;
                        } else {
                            $guestsArr[$item->getProductId()][$bookedSlotDate] = $noOfGuests;
                        }
                    } else {
                        $guestsArr[$item->getProductId()] = [
                            $bookedSlotDate => $noOfGuests
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getMaxGuestsAvailable Exception : ".$e->getMessage());
        }
        return $guestsArr;
    }

    /**
     * checkIsCapacityAvailableForDateRange
     * Check is Guests Capacity available for selected Date range or not
     *
     * @param array  $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Sales\Model\Order\Item $item
     */
    public function checkIsCapacityAvailableForDateRange($data, $product, $item)
    {
        $noOfGuestsRequested = $item->getProduct()->getCartQty();
        if (!empty($data['charged_per_count'])
            && $data['charged_per_count'] > 1
        ) {
            $noOfGuestsRequested = $noOfGuestsRequested * $data['charged_per_count'];
        }
        $error = false;
        $errorMessage = __("something went wrong !!!");
        $tableAttrSetId = $this->getProductAttributeSetIdByLabel(
            'Table Booking'
        );

        if (!empty($data['booking_date']) && !empty($data['booking_time'])) {
            // Check if capacity is available or not
            $selectedBookingDate = $data['booking_date'];
            $selectedBookingTime = $data['booking_time'];
            $bookedSlotDate = $selectedBookingDate." ".$selectedBookingTime;

            $formattedBookedSlotDate = date(
                "d M, Y",
                strtotime($selectedBookingDate)
            )." ".$selectedBookingTime;

            $maxCapacity = $product->getMaxCapacity();
            $info = $this->getBookingInfo($product->getId());

            if (!empty($info['is_booking']) && $info['is_booking'] && !empty($info['info'])) {
                $info  = $this->getJsonDecodedString(
                    $info['info']
                );
            }
            if (!empty($info[$data['slot_day_index']][$data['parent_slot_id']]["slots_info"][$data['slot_id']])) {
                $maxCapacity = $info[$data['slot_day_index']][$data['parent_slot_id']]["slots_info"][$data['slot_id']][
                    'qty'
                ];
            }
            $availability = $maxCapacity;
            if ($product['price_charged_per_table'] == 2) {
                $availability = $availability / ($data['charged_per_count'] > 1 ? $data['charged_per_count'] : 1);
            }
            $errorMessage = __(
                'Only %1 Tables are left at %2 slot.',
                $availability,
                $formattedBookedSlotDate
            );
            if ($maxCapacity <= 0) {
                $error = true;
                $errorMessage = __(
                    'All Tables are reserved at %1.',
                    $formattedBookedSlotDate
                );
            }
            if (!$error) {
                $items =  $this->_checkoutSession->getQuote()->getAllVisibleItems();

                foreach ($items as $tempItem) {
                    if ($this->isBookingProduct($tempItem->getProductId())
                        && $tempItem->getProduct()->getAttributeSetId() == $tableAttrSetId
                    ) {
                        $itemData = $tempItem->getBuyRequest()->getData();
                        $tempSelectedBookingDate = $itemData['booking_date'];
                        $tempSelectedBookingTime = $itemData['booking_time'];
                        $tempBookedSlotDate = $tempSelectedBookingDate ." ". $tempSelectedBookingTime;
                        $noOfGuests = $tempItem->getQty();
                        if (!empty($itemData['charged_per_count'])
                            && $itemData['charged_per_count'] > 1
                        ) {
                            $noOfGuests = $noOfGuests * $itemData['charged_per_count'];
                        }
                        if ($tempBookedSlotDate) {
                            if (strtotime($bookedSlotDate) !== strtotime($tempBookedSlotDate)) {
                            } elseif (($maxCapacity - $noOfGuests) >= $noOfGuestsRequested) {
                            } else {
                                $error = true;
                                if ($maxCapacity - $noOfGuests <= 0) {
                                    $errorMessage = __(
                                        'All Tables are reserved at %1.',
                                        $formattedBookedSlotDate
                                    );
                                } else {
                                    $availability = $maxCapacity - $noOfGuests;
                                    if ($product['price_charged_per_table'] == 2) {
                                        $availability = $availability / (
                                            $itemData['charged_per_count'] > 1 ? $itemData['charged_per_count'] : 1
                                        );
                                    }
                                    $errorMessage = __(
                                        'Only %1 Tables are left at %2 slot.',
                                        $availability,
                                        $formattedBookedSlotDate
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($error) {
            $this->_checkoutSession->getQuote()->setHasError(true);
            throw new \Magento\Framework\Exception\LocalizedException(
                $errorMessage
            );
        }
    }

    /**
     * checkItemQtyAvilableForTable
     * Process Table Booking Save Data
     *
     * @param array  $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Sales\Model\Order\Item $item
     * @param $bookingQuote
     */
    public function checkItemQtyAvilableForTable($data, $product, $item, $bookingQuote)
    {
        $noOfGuestsRequested = $data['qty'];
        if (!empty($data['charged_per_count'])
            && $data['charged_per_count'] > 1
        ) {
            $noOfGuestsRequested = $noOfGuestsRequested * $data['charged_per_count'];
        }
        $error = false;
        $errorMessage = __("something went wrong !!!");
        $tableAttrSetId = $this->getProductAttributeSetIdByLabel(
            'Table Booking'
        );

        if (!empty($data['booking_date']) && !empty($data['booking_time'])) {
            // Check if capacity is available or not
            $selectedBookingDate = $data['booking_date'];
            $selectedBookingTime = $data['booking_time'];
            $bookedSlotDate = $selectedBookingDate." ".$selectedBookingTime;

            $formattedBookedSlotDate = date(
                "d M, Y",
                strtotime($selectedBookingDate)
            )." ".$selectedBookingTime;

            $maxCapacity = $product->getMaxCapacity();
            $info = $this->getBookingInfo($product->getId());

            if (!empty($info['is_booking'])
                && $info['is_booking']
                && !empty($info['info'])
            ) {
                $info  = $this->getJsonDecodedString(
                    $info['info']
                );
            }
            if (!empty($info[$data['slot_day_index']][$data['parent_slot_id']]["slots_info"][$data['slot_id']])) {
                $maxCapacity = $info[$data['slot_day_index']][$data['parent_slot_id']]["slots_info"][$data['slot_id']][
                    'qty'
                ];
            }
            $availability = $maxCapacity;
            if ($product['price_charged_per_table'] == 2) {
                $availability = $availability / ($data['charged_per_count'] > 1 ? $data['charged_per_count'] : 1);
            }
            $errorMessage = __(
                'Only %1 Tables are left at %2 slot.',
                $availability,
                $formattedBookedSlotDate
            );
            if ($maxCapacity <= 0) {
                $error = true;
                $errorMessage = __(
                    'All Tables are reserved at %1.',
                    $formattedBookedSlotDate
                );
            }

            if (!$error) {
                $noOfGuests = $bookingQuote->getQty();
                $itemData = $item->getBuyRequest()->getData();
                if (!empty($itemData['charged_per_count'])
                    && $itemData['charged_per_count'] > 1
                ) {
                    $noOfGuests = $noOfGuests * $itemData['charged_per_count'];
                }
                $noOfGuestsTotal = $noOfGuests + $noOfGuestsRequested;
                if ($maxCapacity < $noOfGuestsTotal) {
                    $error = true;
                    if ($maxCapacity - $noOfGuests <= 0) {
                        $errorMessage = __(
                            'All Tables are reserved at %1.',
                            $formattedBookedSlotDate
                        );
                    } else {
                        $availability = $maxCapacity - $noOfGuests;
                        if ($product['price_charged_per_table'] == 2) {
                            $availability = $availability / (
                                $itemData['charged_per_count'] > 1 ? $itemData['charged_per_count'] : 1
                            );
                        }
                        $errorMessage = __(
                            'Only %1 Tables are left at %2 slot.',
                            $availability,
                            $formattedBookedSlotDate
                        );
                    }
                }
            }
        }

        if ($error) {
            $this->_checkoutSession->getQuote()->setHasError(true);
            throw new \Magento\Framework\Exception\LocalizedException(
                $errorMessage
            );
        }
    }

    /**
     * Check available quantity for Rent booking
     *
     * @param array $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param string|int $bookingQuote
     * @return void
     */
    public function checkItemQtyAvilableForRent($data, $product, $item, $bookingQuote)
    {

        if ($rentType) {
            $rentPeriod = 0;
            // for rent type boking
            $rentPeriodArr = $this->_checkoutSession->getRentPeriod();
            if ($rentType == Info::RENT_TYPE_HOURLY) {
                // number of hours for rent
                $hourDiff = strtotime($data['to_booking_slot']) - strtotime($data['booking_slot']);
                $rentPeriod = round($hourDiff/(60*60));
            } elseif ($rentType == Info::RENT_TYPE_DAILY) {
                // number of days for rent
                $dateDiff = strtotime($data['to_booking_date']) - strtotime($data['booking_date']);
                $rentPeriod = round($dateDiff/(60*60*24));
                $rentPeriod++;
            }
            if (!$rentPeriod) {
                $rentPeriod = 1;
            }
            // update rent product price
            $price = $this->getCovertedPrice($item->getProduct()->getFinalPrice());
            $item->setCustomPrice($price*$rentPeriod);
            $item->setOriginalCustomPrice($price*$rentPeriod)->save();
            $this->_checkoutSession->getQuote()->collectTotals();
            // $this->_checkoutSession->getQuote()->collectTotals()->save();
        }

        if ($qty && $requestedQty > $qty) {
            $item->setQty($qty)->save();
            $bookedDateTimeFormatted = date(
                "d M, Y h:i a",
                strtotime($data['booking_from'])
            );
            if ($rentType) {
                $toTimeFormated = date(
                    "d M, Y h:i a",
                    strtotime($data['booking_to'])
                );
                if ($qty) {
                    $errorMessage = __(
                        'Only %1 quantity is available for %2 for dates %3 to %4.',
                        $qty,
                        $item->getName(),
                        $bookedDateTimeFormatted,
                        $toTimeFormated
                    );
                } else {
                    $errorMessage = __(
                        '%1 is not available for dates %2 to %3.',
                        $item->getName(),
                        $bookedDateTimeFormatted,
                        $toTimeFormated
                    );
                }
            } else {
                if ($qty) {
                    $errorMessage = __(
                        'Only %1 quantity is available for %2 for slot %3.',
                        $qty,
                        $item->getName(),
                        $bookedDateTimeFormatted
                    );
                } else {
                    $errorMessage = __(
                        '%1 is not available for slot %2.',
                        $item->getName(),
                        $bookedDateTimeFormatted
                    );
                }
            }
            $this->_checkoutSession->getQuote()->setHasError(true);
            $this->_messageManager->addError($errorMessage);
        }
    }

    /**
     * Check available quantity for Rent booking
     *
     * @param array $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param string|int $bookingQuote
     * @return void
     */
    public function checkItemQtyAvilableForAppointment($data, $product, $item, $bookingQuote)
    {

        if (!empty($data['booking_date']) && !empty($data['booking_time'])) {
            $error = 0;
            $currentTime = $this->getCurrentTime();
            $errorMessage = __('Invalid booking dates.');
            $selectedBookingDate = $data['booking_date'];
            $selectedBookingTime = $data['booking_time'];
            $bookedSlotDate = date(
                "d M, Y",
                strtotime($selectedBookingDate)
            )." ".$selectedBookingTime;

            if (empty($data['slot_day_index'])) {
                $data['parent_slot_id'] = 0;
                $data['slot_id'] = 0;
                $data['slot_day_index'] = 0;
            }
            $parentSlotId = $data['parent_slot_id'];
            $slotId = $data['slot_id'];
            $slotDayIndex = $data['slot_day_index'];

            // Check if selected booking dates are available or not
            $productId = $product->getId();
            $bookingInfo = $this->getBookingInfo($productId);
            $bookingSlotData = $this->getJsonDecodedString(
                $bookingInfo['info']
            );
            $slotData = [];
            $bookedData = $this->getBookedAppointmentDates($productId);
            if (empty($bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotId])) {
                $this->_checkoutSession->getQuote()->setHasError(true);
                throw new \Magento\Framework\Exception\LocalizedException(
                    $errorMessage
                );
            } else {
                $slotData = $bookingSlotData[$slotDayIndex][$parentSlotId]['slots_info'][$slotId];

                $bookedDay = date("l", strtotime($selectedBookingDate));
                $bookedDayIndex = $this->getDayIndexId($bookedDay);
                if (!empty($bookingSlotData[$bookedDayIndex])) {
                    if (empty($slotData['time'])) {
                        $error = 1;
                    }
                    if (!$error) {
                        if (strtotime($selectedBookingDate)===strtotime(date('m/d/Y'))) {
                            if (!($currentTime <= strtotime($selectedBookingTime))) {
                                $error = 1;
                                $errorMessage = __(
                                    'Slot %1 is not available.',
                                    $bookedSlotDate
                                );
                                $this->_checkoutSession->getQuote()->setHasError(true);
                                throw new \Magento\Framework\Exception\LocalizedException(
                                    $errorMessage
                                );
                            }
                        }
                        if (!$error) {
                            // get valid available booking dates
                            $validBookingDates = $this->getValidBookingDates($product);
                            $bookingAvailableFrom = $validBookingDates['booking_available_from'];
                            $bookingAvailableTo = $validBookingDates['booking_available_to'];
                            // check if selected booking dates are correct or not
                            if (!(strtotime($bookingAvailableFrom)<=strtotime($selectedBookingDate))) {
                                $error = 1;
                                $errorMessage = __('Invalid booking dates.');
                                $this->_checkoutSession->getQuote()->setHasError(true);
                                throw new \Magento\Framework\Exception\LocalizedException(
                                    $errorMessage
                                );
                            }
                            if (!$product['available_every_week']) {
                                if (!(strtotime($selectedBookingDate)<=strtotime($bookingAvailableTo))) {
                                    $error = 1;
                                    $errorMessage = __('Invalid booking dates.');
                                    $this->_checkoutSession->getQuote()->setHasError(true);
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        $errorMessage
                                    );
                                }
                            }
                        }
                    }
                } else {
                    $error = 1;
                }

                if ($error) {
                    $this->_checkoutSession->getQuote()->setHasError(true);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        $errorMessage
                    );
                }
                $availableQty = 0;
                if (!empty($slotData['qty'])) {
                    $availableQty = $slotData['qty'];
                }
                if (!empty($bookedData[strtotime($selectedBookingDate)][$selectedBookingTime])) {
                    $bookedQty = $bookedData[strtotime($selectedBookingDate)][$selectedBookingTime];
                    if ($bookedQty > $availableQty) {
                        $availableQty = 0;
                    } else {
                        $availableQty = $availableQty - $bookedQty;
                    }
                }
                $requestedQty = $item->getQty();
                if (!$availableQty) {
                    $errorMessage = __(
                        '%1 quantity is not available for slot %2.',
                        $item->getName(),
                        $bookedSlotDate
                    );

                    $item->setHasError(true);
                    $item->setMessage([$errorMessage]);
                    if ($item->getId()) {
                        $item->delete();
                    }
                    $this->_checkoutSession->getQuote()->setHasError(true);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        $errorMessage
                    );
                } else {
                    if ($requestedQty > $availableQty) {
                        $item->setQty($availableQty)->save();
                        $error = 1;

                        $errorMessage = __(
                            'Only %1 quantity is available for %2 for slot %3 %4.',
                            $availableQty,
                            $item->getName(),
                            date(
                                "d M, Y",
                                strtotime($selectedBookingDate)
                            ),
                            trim($selectedBookingTime)
                        );
                        $this->_checkoutSession->getQuote()->collectTotals()->save();
                        $this->_checkoutSession->getQuote()->setHasError(true);
                        throw new \Magento\Framework\Exception\LocalizedException(
                            $errorMessage
                        );
                    }
                }
            }
        }
    }

    /**
     * clearCache
     *
     * @return void
     */
    public function clearCache()
    {
        try {
            $cacheManager = $this->cacheManager->create();
            $availableTypes = $cacheManager->getAvailableTypes();
            $cacheManager->clean($availableTypes);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_clearCache Exception : ".$e->getMessage());
        }
    }

    /**
     * getRegionName
     *
     * @param int $regionId
     * @return string|int
     */
    public function getRegionName($regionId)
    {
        try {
            $region = $this->regionFactory->create()->load($regionId);
            if (!empty($region->getId())) {
                return $region->getName();
            } else {
                return $regionId;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getRegionName Exception : ".$e->getMessage());
            return $regionId;
        }
    }

    /**
     * getBookedAppointmentDates
     *
     * @param int $productId
     * @return array
     */
    public function getBookedAppointmentDates($productId)
    {
        $sellerId = $this->marketplaceHelper->getSellerIdByProductId($productId);
        $products = $this->marketplaceHelper->getSellerProducts($sellerId);
        $data = [];
        try {
            $collection = $this->_bookedCollection
                ->create()
                ->addFieldToFilter("product_id", array('in' => $products));
            if ($collection->getSize()) {
                foreach ($collection as $bookedData) {
                    $date = strtotime($bookedData->getSlotDate());
                    //$time = $bookedData->getSlotTime();
                    $bookingFrom = $bookedData->getBookingFrom();
                    $bookingTo = $bookedData->getBookingToo();
                    $timeFrom = strtotime($bookingFrom);
                    $timeToo = strtotime($bookingTo);
                    for ($timeFrom; $timeFrom<$timeToo; $timeFrom += 900) {
                        $time = date("h:i a",$timeFrom);
                        if (!empty($data[$date])) {
                            if (!empty($data[$date][$time])) {
                                $data[$date][$time] += (int)$bookedData->getQty();
                            } else {
                                $data[$date][$time] = (int)$bookedData->getQty();
                            }
                        } else {
                            $data[$date][$time] = (int)$bookedData->getQty();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getBookedAppointmentDates Exception : ".$e->getMessage());
        }
        return $data;
    }

    /**
     * getBookedEventData
     *
     * @param int $productId
     * @param array $bookingInfo
     * @param int $parentSlotId
     * @param int $slotId
     * @return array
     */
    public function getBookedEventData($productId, $bookingInfo, $parentSlotId, $slotId)
    {
        $data = [];
        try {
            $collection = $this->_bookedCollection->create()
                ->addFieldToFilter("product_id", $productId)
                ->addFieldToFilter("parent_slot_id", $parentSlotId)
                ->addFieldToFilter("slot_id", $slotId)
                ->addFieldToFilter("booking_from", ['eq' => $bookingInfo['start_date']])
                ->addFieldToFilter("booking_too", ['eq' => $bookingInfo['end_date']]);

            if ($collection->getSize()) {
                foreach ($collection as $bookedData) {
                    $parentSlotId = $bookedData->getParentSlotId();
                    $slotId = $bookedData->getSlotId();
                    if (!empty($data[$parentSlotId])) {
                        if (!empty($data[$parentSlotId][$slotId])) {
                            $data[$parentSlotId][$slotId] += (int)$bookedData->getQty();
                        } else {
                            $data[$parentSlotId][$slotId] = (int)$bookedData->getQty();
                        }
                    } else {
                        $data[$parentSlotId][$slotId] = (int)$bookedData->getQty();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getBookedEventData Exception : ".$e->getMessage());
        }
        return $data;
    }

    /**
     * getTableChargedPer
     *
     * @return array|boolean
     */
    public function getTableChargedPer()
    {
        try {
            return $this->pricesChargedPerOptionsTable->getOptionArray();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getTableChargedPer Exception : ".$e->getMessage());
            return false;
        }
    }

    /**
     * getHotelStateOptions
     *
     * @param string $countryCode
     * @return object|boolean
     */
    public function getHotelStateOptions($countryCode)
    {
        try {
            return $this->regionCollectionFactory->create()->addCountryFilter($countryCode);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getHotelStateOptions Exception : ".$e->getMessage());
            return false;
        }
    }

    /**
     * getHotelCountryOptions
     * @return array all countries
     */
    public function getHotelCountryOptions()
    {
        $options = [];
        try {
            $attribute = $this->getProductAttribute('hotel_country');
            $options = $attribute->getOptions();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getHotelCountryOptions Exception : ".$e->getMessage());
        }
        return $options;
    }

    /**
     * getEventImagesJson
     * Get event chart image data.
     *
     * @param string $eventChartImage
     * @return json
     */
    public function getEventImagesJson($eventChartImage)
    {
        try {
            if ($eventChartImage && !empty($eventChartImage)) {
                $eventImages = [];
                $eventChartImageBasePath = 'mpadvancedbookingsystem/eventChartImage/';
                $eventChartImageUrl = $this->getMediaUrl().$eventChartImageBasePath;

                $baseImgPath = $this->_mediaDirectory->getAbsolutePath(
                    $eventChartImageBasePath
                );

                $eventChartImagePath = $this->_mediaDirectory->getAbsolutePath(
                    $eventChartImageBasePath
                ).$eventChartImage;

                $eventImages[0]['type'] = 'image';
                $eventImages[0]['file'] = $eventChartImage;
                $eventImages[0]['name'] = $eventChartImage;
                $eventImages[0]['path'] = $baseImgPath;
                $eventImages[0]['url'] = $eventChartImageUrl.$eventChartImage;
                if ($this->_filesystemFile->fileExists($eventChartImagePath)) {
                    $eventImages[0]['size'] = filesize($eventChartImagePath);
                } else {
                    $eventImages[0]['size'] = 0;
                }

                return $this->getJsonEcodedString($eventImages);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getEventImagesJson Exception : ".$e->getMessage());
        }
        return '[]';
    }

    /**
     * getFileSizeService
     * @return \Magento\Framework\File\Size
     */
    public function getFileSizeService()
    {
        return $this->fileSizeService;
    }

    /**
     * getAllBookingTypes
     *
     * @return array
     */
    public function getAllBookingTypes()
    {
        return array_keys($this->bookingTypes->getOptionArray());
    }

    /**
     * getAttributeSetAndProductTypeForBooking
     *
     * @param string $bookingType
     * @return array
     */
    public function getAttributeSetAndProductTypeForBooking($bookingType)
    {
        $arr = [];
        try {
            if ($bookingType == 'default') {
                $arr = [
                    'set' => $this->getProductAttributeSetIdByLabel(
                        'Default'
                    ) ?? 0,
                    'booking_type' => 'booking',
                    'type' => 'virtual'
                ];
            } elseif ($bookingType == 'appointment') {
                $arr = [
                    'set' => $this->getProductAttributeSetIdByLabel(
                        'Appointment Booking'
                    ) ?? 0,
                    'booking_type' => 'booking',
                    'type' => 'virtual'
                ];
            } elseif ($bookingType == 'event') {
                $arr = [
                    'set' => $this->getProductAttributeSetIdByLabel(
                        'Event Booking'
                    ) ?? 0,
                    'booking_type' => 'booking',
                    'type' => 'virtual'
                ];
            } elseif ($bookingType == 'rental') {
                $arr = [
                    'set' => $this->getProductAttributeSetIdByLabel(
                        'Rental Booking'
                    ) ?? 0,
                    'booking_type' => 'booking',
                    'type' => 'virtual'
                ];
            } elseif ($bookingType == 'table') {
                $arr = [
                    'set' => $this->getProductAttributeSetIdByLabel(
                        'Table Booking'
                    ) ?? 0,
                    'booking_type' => 'booking',
                    'type' => 'virtual'
                ];
            } elseif ($bookingType == 'hotel') {
                $arr = [
                    'set' => $this->getProductAttributeSetIdByLabel(
                        'Hotel Booking'
                    ) ?? 0,
                    'booking_type' => 'hotelbooking',
                    'type' => 'configurable'
                ];
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data_getAttributeSetAndProductTypeForBooking Exception : ".$e->getMessage());
        }
        return $arr;
    }

    /**
     * getAllowedBookingProductTypes
     * Options getter.
     *
     * @return array
     */
    public function getAllowedBookingProductTypes()
    {
        $allowedproducts = [];
        try {
            $allowedTypes = explode(',', $this->getMpConfigValue('booking_types'));
            $data = $this->bookingTypes->getOptionArray();

            if (!empty($allowedTypes)) {
                foreach ($allowedTypes as $type) {
                    if (!empty($data[$type])) {
                        array_push(
                            $allowedproducts,
                            ['value' => $type, 'label' => $data[$type]]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_getAllowedBookingProductTypes Exception : ".$e->getMessage()
            );
        }
        return $allowedproducts;
    }

    /**
     * getAskedQuestions
     *
     * @param int $productId
     * @return array
     */
    public function getAskedQuestions($productId)
    {
        $ids = [];
        try {
            $collection = $this->questionCollection->create()->addEntityFilter($productId);
            if ($collection->getSize()) {
                $ids = $collection->getAllIds();
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_getAskedQuestions Exception : ".$e->getMessage()
            );
        }
        return $ids;
    }

    /**
     * getUnvailableDates
     *
     * @param array $bookingSlotData
     * @param string $bookingAvailableFrom
     * @param string $bookingAvailableTo
     * @return array
     */
    public function getUnvailableDates(
        $bookingSlotData,
        $bookingAvailableFrom,
        $bookingAvailableTo
    ) {
        $unavailableDates = [];
        try {
            $startDate = strtotime($bookingAvailableFrom);
            $endDate = strtotime($bookingAvailableTo);
            foreach ($bookingSlotData as $dayIndex => $slotData) {
                if (empty($slotData)) {
                    $startDateTimeStamp = strtotime($this->dayLabelsFull[$dayIndex], $startDate);
                    for ($i = $startDateTimeStamp; $i <= $endDate; $i = strtotime('+1 week', $i)) {
                        $unavailableDates[] = date('d M, Y', $i);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_getUnvailableDates Exception : ".$e->getMessage()
            );
        }
        return $unavailableDates;
    }

    /**
     * Update customer's shopping cart
     *
     * @return void
     */
    protected function _updateShoppingCart($cartData)
    {
        if (is_array($cartData)) {
            if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                $this->cart->getQuote()->setCustomerId(null);
            }
            $cartData = $this->quantityProcessor->process($cartData);
            $cartData = $this->cart->suggestItemsQty($cartData);
            $this->cart->updateItems($cartData)->save();
        }
    }
}
