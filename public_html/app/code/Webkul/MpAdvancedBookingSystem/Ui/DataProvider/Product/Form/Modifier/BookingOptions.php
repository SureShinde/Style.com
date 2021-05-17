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
namespace Webkul\MpAdvancedBookingSystem\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutFactory;
use Webkul\MpAdvancedBookingSystem\Helper\Data as HelperData;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\AssociatedProducts;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Media;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\Modal;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Booking;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Appointment\Booking as AppointmentBooking;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Appointment\Contact as AppointmentContact;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Event\Contact as EventContact;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Rental\Booking as RentalBooking;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Rental\Contact as RentalContact;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Hotel\Contact as HotelContact;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Hotel\CheckTime;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Hotel\Amenities;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Table\Booking as TableBooking;
use Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Table\Contact as TableContact;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurableAttributeSetHandler;

class BookingOptions extends CustomOptions
{
    /**#@+
     * Group values
     */
    const GROUP_BOOKING_OPTIONS_NAME = 'booking_custom_options';

    const GROUP_BOOKING_OPTIONS_PREVIOUS_NAME = 'contact_tab';
    const GROUP_BOOKING_OPTIONS_DEFAULT_SORT_ORDER = 6;
    const GROUP_HOTELBOOKING_OPTIONS_NAME = 'hotelbooking_custom_options';
    const CONFIGURABLE_MATRIX = 'configurable-matrix';
    const ASSOCIATED_PRODUCT_LISTING = 'configurable_associated_product_listing';
    const GROUP_CONFIGURABLE = 'configurable';
    const ASSOCIATED_PRODUCT_MODAL = 'configurable_associated_product_modal';
    const CODE_GROUP_PRICE = 'container_price';
    const ATTRIBUTE_SET_HANDLER_MODAL = 'configurable_attribute_set_handler_modal';
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_EVENT_DATE = 'event_date_container';
    const FIELD_EVENT_DATE_FROM = 'event_date_from';
    const FIELD_EVENT_DATE_TO = 'event_date_to';
    const FIELD_EVENT_CHART_AVAILABLE = 'event_chart_available';
    const FIELD_EVENT_CHART_IMAGE = 'event_chart_image';
    const FIELD_PRICE_CHARGED_PER = 'price_charged_per';
    const FIELD_IS_MULTIPLE_TICKET = 'is_multiple_tickets';
    const FIELD_STOCK_NAME = 'qty';
    const FIELD_DESCRIPTION_NAME = 'description';
    const FIELD_IS_IN_STOCK_NAME = 'is_in_stock';
    const FIELD_NEAR_BY_MAP = 'show_nearby_map';
    const FIELD_PRICE_CHARGED_PER_HOTEL = 'price_charged_per_hotel';
    const FIELD_ENABLE_ASK_QUES = 'ask_a_ques_enable';
    const FIELD_HOTEL_CHECK_IN_OUT_TIME = 'hotel_check_in_out_container';
    /**#@-*/

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $productOptionsConfig;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Price
     */
    protected $productOptionsPrice;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystemFile;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var string
     */
    private static $advancedPricingButton = 'advanced_pricing_button';

    /**
     * @var AssociatedProducts
     */
    private $associatedProducts;

    /**
     * @var \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet
     */
    protected $attributeSet;

    /**
     * @var string
     */
    private $formName = 'product_form';

    /**
     * @var string
     */
    private $dataScopeName = "product_form.product_form";

    /**
     * @var string
     */
    private $dataSourceName = "product_form.product_form_data_source";

    /**
     * @param LocatorInterface      $locator
     * @param RequestInterface      $request
     * @param LayoutFactory         $layoutFactory
     * @param HelperData            $helperData
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface       $productOptionsConfig
     * @param ProductOptionsPrice   $productOptionsPrice
     * @param UrlInterface          $urlBuilder
     * @param ArrayManager          $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        RequestInterface $request,
        LayoutFactory $layoutFactory,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        ConfigInterface $productOptionsConfig,
        ProductOptionsPrice $productOptionsPrice,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        AssociatedProducts $associatedProducts,
        AttributeSet $attributeSet,
        ConfigurableAttributeSetHandler $configAttributeHandler
    ) {
        $this->locator = $locator;
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->productOptionsConfig = $productOptionsConfig;
        $this->productOptionsPrice = $productOptionsPrice;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->filesystemFile = $filesystemFile;
        $this->associatedProducts = $associatedProducts;
        $this->attributeSet = $attributeSet;
        $this->configAttributeHandler = $configAttributeHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $options = [];
        $eventOptions = [];
        $booking = [
            'Booking Date',
            'Booking From',
            'Booking Till',
            "Booking Slot",
            'Adults',
            'Kids',
            'Event From',
            'Event To',
            'Event Location',
            'Rent To',
            'Rent From'
        ];
        $customOptions = $this->locator->getProduct()->getOptions() ?: [];

        /** @var \Magento\Catalog\Model\Product\Option $customOption */
        foreach ($customOptions as $key => $customOption) {
            $customOptionData = $customOption->getData();
            if (in_array($customOptionData['default_title'], $booking)) {
                continue;
            }
            $customOptionData[static::FIELD_IS_USE_DEFAULT] = !$customOption->getData(
                static::FIELD_STORE_TITLE_NAME
            );
            $options[$key] = $this->formatPriceByPath(
                static::FIELD_PRICE_NAME,
                $customOptionData
            );
            $customOptionValues = $customOption->getValues() ?: [];

            foreach ($customOptionValues as $customOptionValue) {
                $customOptionValue->setData(
                    static::FIELD_IS_USE_DEFAULT,
                    !$customOptionValue->getData(static::FIELD_STORE_TITLE_NAME)
                );
            }
            /** @var \Magento\Catalog\Model\Product\Option $value */
            foreach ($customOptionValues as $customOptionValue) {
                if ($customOptionData['default_title'] == 'Choose Rent Type') {
                    continue;
                }
                $options[$key][static::GRID_TYPE_SELECT_NAME][] = $this->formatPriceByPath(
                    static::FIELD_PRICE_NAME,
                    $customOptionValue->getData()
                );
            }
            if ($customOption->getData(static::FIELD_STORE_TITLE_NAME) == 'Event Tickets') {
                $eventOptions = $options;
            }
        }
        $options = array_values($options);

        $productType = $this->getProductType();
        $set = $this->getProductAttributeSetId();
        $helper = $this->helperData;
        $rentType = $helper->getProductAttributeSetIdByLabel('Rental Booking');
        $eventType = $helper->getProductAttributeSetIdByLabel('Event Booking');
        $hotelType = $helper->getProductAttributeSetIdByLabel('Hotel Booking');
        if ($productType == "booking" && $set == $eventType) {
            return $this->modifyDataEventType($data, $options);
        } elseif ($productType === "booking" && $set === $rentType) {
            return array_replace_recursive(
                $data,
                [
                    $this->locator->getProduct()->getId() => [
                        static::DATA_SOURCE_DEFAULT => [
                            static::FIELD_ENABLE => 1,
                            static::GRID_OPTIONS_NAME => $options
                        ]
                    ]
                ]
            );
        } elseif ($productType === "hotelbooking" && $set === $hotelType) {
            $product = $this->locator->getProduct();
            $id = $product->getId();
            $attributeData = $this->associatedProducts->getConfigurableAttributesData();

            return array_replace_recursive($data, [
                $id => [
                    static::DATA_SOURCE_DEFAULT => [
                        static::FIELD_ENABLE => 1,
                        static::GRID_OPTIONS_NAME => $options,
                        static::FIELD_NEAR_BY_MAP => $product->getShowNearbyMap(),
                        static::FIELD_PRICE_CHARGED_PER_HOTEL => $product->getPriceChargedPerHotel(),
                        static::FIELD_ENABLE_ASK_QUES => $product->getAskAQuesEnable(),
                        'configurable_attributes_data' => $attributeData,
                        'affect_product_custom_options' => 1,
                        "current_product_id" => $id
                    ],
                    'affect_configurable_product_attributes' => '1',
                    'configurable-matrix' => $this->associatedProducts->getProductMatrix(),
                    'attributes' =>$this->associatedProducts->getProductAttributesIds(),
                    'attribute_codes' => $this->associatedProducts->getProductAttributesCodes(),
                ]
            ]);
        } else {
            return array_replace_recursive(
                $data,
                [
                    $this->locator->getProduct()->getId() => [
                        static::DATA_SOURCE_DEFAULT => [
                            static::FIELD_ENABLE => 1,
                            static::GRID_OPTIONS_NAME => $options
                        ]
                    ]
                ]
            );
        }
    }

    public function modifyDataEventType(array $data, $options)
    {
        if (empty($options)) {
            $options = [];
            $options[0] = [
                'sort_order' => 1,
                'option_id' => '',
                'title' => 'Event Tickets',
                'is_require' => 1,
                'price' => '',
                'price_type' => 'fixed',
                'sku' => '',
                'type' => 'multiple',
                'values' => [
                    [
                        'record_id' => 0
                    ]
                ]
            ];
        }
        $product = $this->locator->getProduct();
        $id = $product->getId();
        if ($chartImage = $product->getEventChartImage()) {
            $eventChartDirPath = $this->mediaDirectory->getAbsolutePath(
                'mpadvancedbookingsystem'
            );
            $eventChartImageDirPath = $this->mediaDirectory->getAbsolutePath(
                'mpadvancedbookingsystem/eventChartImage'
            );
            if (!$this->filesystemFile->fileExists($eventChartDirPath)) {
                $this->filesystemFile->mkdir($eventChartDirPath, 0777, true);
            }
            if (!$this->filesystemFile->fileExists($eventChartImageDirPath)) {
                $this->filesystemFile->mkdir($eventChartImageDirPath, 0777, true);
            }
            $eventChartImageBasePath = 'mpadvancedbookingsystem/eventChartImage/';
            $eventChartImageUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).$eventChartImageBasePath;

            $eventChartImagePath = $this->mediaDirectory->getAbsolutePath(
                $eventChartImageBasePath
            ).$chartImage;
            
            $data[$id][self::DATA_SOURCE_DEFAULT]['event_chart_image'][0]['type'] = 'image';
            $data[$id][self::DATA_SOURCE_DEFAULT]['event_chart_image'][0]['file'] = $chartImage;
            $data[$id][self::DATA_SOURCE_DEFAULT]['event_chart_image'][0]['name'] = $chartImage;
            $data[$id][self::DATA_SOURCE_DEFAULT]['event_chart_image'][0]['path'] = $eventChartImageBasePath;
            $data[$id][self::DATA_SOURCE_DEFAULT]['event_chart_image'][0]['url'] = $eventChartImageUrl.$chartImage;
            if ($this->filesystemFile->fileExists($eventChartImagePath)) {
                $data[$id][self::DATA_SOURCE_DEFAULT]['event_chart_image'][0]['size'] = 0;
                if (isset($this->mediaDirectory->stat($eventChartImagePath)['size'])) {
                    $data[$id][self::DATA_SOURCE_DEFAULT]['event_chart_image'][0]['size'] =
                    $this->mediaDirectory->stat($eventChartImagePath)['size'];
                }
            } else {
                $data[$id][self::DATA_SOURCE_DEFAULT]['event_chart_image'][0]['size'] = 0;
            }
        }
        // DATA_SOURCE_DEFAULT = 'product'
        return array_replace_recursive(
            $data,
            [
                $id => [
                    static::DATA_SOURCE_DEFAULT => [
                        static::FIELD_EVENT_DATE_FROM => $product->getEventDateFrom(),
                        static::FIELD_EVENT_DATE_TO => $product->getEventDateTo(),
                        static::FIELD_EVENT_CHART_AVAILABLE => $product->getEventChartAvailable(),
                        static::FIELD_PRICE_CHARGED_PER => $product->getPriceChargedPer(),
                        static::FIELD_IS_MULTIPLE_TICKET => $product->getIsMultipleTickets(),
                        static::FIELD_ENABLE => 1,
                        static::GRID_OPTIONS_NAME => $options
                    ]
                ]
            ]
        );
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        //add custom option block
        $this->createCustomOptionsPanel();

        $helper = $this->helperData;
        $productType = $this->getProductType();
        $set = $this->getProductAttributeSetId();
        $appointmentType = $helper->getProductAttributeSetIdByLabel('Appointment Booking');
        $rentalType = $helper->getProductAttributeSetIdByLabel('Rental Booking');
        $eventType = $helper->getProductAttributeSetIdByLabel('Event Booking');
        $hotelType = $helper->getProductAttributeSetIdByLabel('Hotel Booking');
        $tableType = $helper->getProductAttributeSetIdByLabel('Table Booking');
        if ($productType == "booking" && $set == $appointmentType) {
            $this->modifyMetaAppointmentType($this->meta);
        } elseif ($productType == "booking" && $set == $rentalType) {
            $this->modifyMetaRentalType($this->meta);
        } elseif ($productType == "booking" && $set == $eventType) {
            $this->modifyMetaEventType($this->meta);
        } elseif ($productType == "booking" && $set == $tableType) {
            $this->modifyMetaTableType($this->meta);
        } elseif ($productType == "booking") {
            $this->modifyMetaDefaultType($this->meta);
        } elseif ($productType == "hotelbooking" && $set == $hotelType) {
            $this->modifyMetaHotelType($this->meta);
            $this->meta = $this->addAttributeSetData($this->meta);
            $this->meta = $this->configAttributeHandler->modifyMeta($this->meta);
        } else {
            parent::modifyMeta($meta);
        }
        return $this->meta;
    }

    public function modifyMetaHotelType(array $meta)
    {
        $this->meta = $meta;
        $groupCode = $this->getGroupCodeByField($this->meta, ProductAttributeInterface::CODE_PRICE)
            ?: $this->getGroupCodeByField($this->meta, self::CODE_GROUP_PRICE);

        if ($groupCode && !empty($this->meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
            if (!empty($this->meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
                $this->meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $this->meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            ProductAttributeInterface::CODE_PRICE => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'component' => 'Magento_ConfigurableProduct/js/' .
                                                'components/price-configurable'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
            if (!empty(
                $this->meta[$groupCode]['children'][self::CODE_GROUP_PRICE]['children'][self::$advancedPricingButton]
            )) {
                $productTypeId = $this->locator->getProduct()->getTypeId();
                $visibilityConfig = ($productTypeId === "hotelbooking")
                    ? ['visible' => 0, 'disabled' => 1]
                    : [
                        'imports' => [
                            'visible' => 'ns = ${ $.ns }, index = '
                                . self::CONFIGURABLE_MATRIX . ':isEmpty',
                        ]
                    ];
                $config = $visibilityConfig;
                $config['componentType'] = 'container';
                $this->meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $this->meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            self::$advancedPricingButton => [
                                'arguments' => [
                                    'data' => [
                                        'config' => $config,
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
        }

        $this->meta["contact_tab"] = [
            "children" => [
                "contact_tab_container1" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Hotel Location"),
                                "required" => 0,
                                "sortOrder" => 5,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    HotelContact::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Hotel Location"),
                        "collapsible" => true,
                        "sortOrder" => 5,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
        $this->createHotelBookingOptionsPanel();
        $this->meta['hotel_questions'] = [
            'children' => [
                'question_listing' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => true,
                                'componentType' => 'insertListing',
                                'dataScope' => 'question_listing',
                                'externalProvider' => 'question_listing.question_listing_data_source',
                                'selectionsProvider' => 'question_listing.question_listing.product_columns.ids',
                                'ns' => 'question_listing',
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => false,
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'imports' => [
                                    'productId' => '${ $.provider }:data.product.current_product_id'
                                ],
                                'exports' => [
                                    'productId' => '${ $.externalProvider }:params.current_product_id'
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Ask a Question'),
                        'collapsible' => true,
                        'opened' => false,
                        'componentType' => Fieldset::NAME,
                        'sortOrder' =>
                            $this->getNextGroupSortOrder(
                                $this->meta,
                                'content',
                                19
                            ),
                    ],
                ],
            ],
        ];
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::GROUP_CONFIGURABLE => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                'label' => __('Configurations'),
                                'collapsible' => true,
                                'opened' => true,
                                'componentType' => Fieldset::NAME,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    'content',
                                    20
                                ),
                            ]
                        ]
                    ],
                    'children' => $this->getPanelChildren(),
                ],
                static::ASSOCIATED_PRODUCT_MODAL => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Modal::NAME,
                                'dataScope' => '',
                                'provider' => $this->dataSourceName,
                                'options' => [
                                    'title' => __('Select Associated Product'),
                                    'buttons' => [
                                        [
                                            'text' => __('Done'),
                                            'class' => 'action-primary',
                                            'actions' => [
                                                [
                                                    'targetName' => 'ns= ' . ''
                                                        . static::ASSOCIATED_PRODUCT_LISTING
                                                        . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
                                                    'actionName' => 'save'
                                                ],
                                                'closeModal'
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'children' => [
                        'information-block1' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Container::NAME,
                                        'component' => 'Magento_Ui/js/form/components/html',
                                        'additionalClasses' => 'message message-notice',
                                        'content' => __(
                                            'Choose a new product to delete and replace'
                                            . ' the current product configuration.'
                                        ),
                                        'imports' => [
                                            'visible' => '!ns = ${ $.ns }, index = '
                                                . static::CONFIGURABLE_MATRIX . ':isEmpty',
                                            '__disableTmpl' => ['visible' => false],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'information-block2' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Container::NAME,
                                        'component' => 'Magento_Ui/js/form/components/html',
                                        'additionalClasses' => 'message message-notice',
                                        'content' => __(
                                            'For better results, add attributes and attribute values to your products.'
                                        ),
                                        'imports' => [
                                            'visible' => 'ns = ${ $.ns }, index = '
                                                . static::CONFIGURABLE_MATRIX . ':isEmpty',
                                            '__disableTmpl' => ['visible' => false],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        static::ASSOCIATED_PRODUCT_LISTING => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'autoRender' => false,
                                        'componentType' => 'insertListing',
                                        'component' => 'Magento_ConfigurableProduct/js'
                                            .'/components/associated-product-insert-listing',
                                        'dataScope' => ''
                                            . static::ASSOCIATED_PRODUCT_LISTING,
                                        'externalProvider' => ''
                                            . static::ASSOCIATED_PRODUCT_LISTING . '.data_source',
                                        'selectionsProvider' => ''
                                            . static::ASSOCIATED_PRODUCT_LISTING . '.'
                                            . ''
                                            . static::ASSOCIATED_PRODUCT_LISTING . '.product_columns.ids',
                                        'ns' => '' . static::ASSOCIATED_PRODUCT_LISTING,
                                        'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                        'realTimeLink' => true,
                                        'behaviourType' => 'simple',
                                        'externalFilterMode' => false,
                                        'currentProductId' => $this->locator->getProduct()->getId(),
                                        'dataLinks' => [
                                            'imports' => false,
                                            'exports' => true
                                        ],
                                        'changeProductProvider' => 'change_product',
                                        'productsProvider' => ''
                                            . 'configurable_associated_product_listing.data_source',
                                        'productsColumns' => ''
                                            . 'configurable_associated_product_listing'
                                            . '.' . ''
                                            . 'configurable_associated_product_listing.product_columns',
                                        'productsMassAction' => ''
                                            . 'configurable_associated_product_listing'
                                            . '.' . ''
                                            . 'configurable_associated_product_listing.product_columns.ids',
                                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                                            . static::ASSOCIATED_PRODUCT_MODAL,
                                        'productsFilters' => ''
                                            . 'configurable_associated_product_listing'
                                            . '.' . ''
                                            . 'configurable_associated_product_listing.listing_top.listing_filters',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        );
        unset($this->meta['product-details']['children']['quantity_and_stock_status_qty']);
        unset($this->meta['product-details']['children']['container_amenities']);
    }

    public function modifyMetaAppointmentType(array $meta)
    {
        $this->meta = $meta;
        $fieldCode = 'qty';
        $pathField = $this->arrayManager->findPath($fieldCode, $this->meta, null, 'children');
        $this->meta = $this->arrayManager->merge(
            $pathField . '/arguments/data/config',
            $this->meta,
            ['disabled' => true]
        );
        $this->meta["contact_tab"] = [
            "children" => [
                "contact_tab_container" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Contact Information"),
                                "required" => 0,
                                "sortOrder" => 5,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    AppointmentContact::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Contact Information"),
                        "collapsible" => true,
                        "sortOrder" => 5,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
        $this->meta["booking_tab"] = [
            "children" => [
                "booking_tab_container" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Booking Information"),
                                "required" => 0,
                                "sortOrder" => 6,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    AppointmentBooking::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Booking Information"),
                        "collapsible" => true,
                        "sortOrder" => 6,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
    }

    public function modifyMetaTableType(array $meta)
    {
        $this->meta = $meta;
        $fieldCode = 'qty';
        $pathField = $this->arrayManager->findPath($fieldCode, $this->meta, null, 'children');
        $this->meta = $this->arrayManager->merge(
            $pathField . '/arguments/data/config',
            $this->meta,
            ['disabled' => true]
        );
        
        $this->meta["contact_tab"] = [
            "children" => [
                "contact_tab_container" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Contact Information"),
                                "required" => 0,
                                "sortOrder" => 5,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    TableContact::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Contact Information"),
                        "collapsible" => true,
                        "sortOrder" => 5,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
        $this->meta["booking_tab"] = [
            "children" => [
                "booking_tab_container" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Booking Information"),
                                "required" => 0,
                                "sortOrder" => 6,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    TableBooking::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Booking Information"),
                        "collapsible" => true,
                        "sortOrder" => 6,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
    }

    public function modifyMetaRentalType(array $meta)
    {
        $this->meta = $meta;
        $fieldCode = 'qty';
        $pathField = $this->arrayManager->findPath($fieldCode, $this->meta, null, 'children');
        $this->meta = $this->arrayManager->merge(
            $pathField . '/arguments/data/config',
            $this->meta,
            ['disabled' => true]
        );
        $this->meta["contact_tab"] = [
            "children" => [
                "contact_tab_container" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Contact Information"),
                                "required" => 0,
                                "sortOrder" => 5,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    RentalContact::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Contact Information"),
                        "collapsible" => true,
                        "sortOrder" => 5,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
        $this->meta["booking_tab"] = [
            "children" => [
                "booking_tab_container" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Renting Information"),
                                "required" => 0,
                                "sortOrder" => 6,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    RentalBooking::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Renting Information"),
                        "collapsible" => true,
                        "sortOrder" => 6,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
    }

    public function modifyMetaEventType(array $meta)
    {
        $this->meta = $meta;
        
        $fieldCode = 'qty';
        $pathField = $this->arrayManager->findPath($fieldCode, $this->meta, null, 'children');
        $this->meta = $this->arrayManager->merge(
            $pathField . '/arguments/data/config',
            $this->meta,
            ['disabled' => true]
        );
        $this->meta["contact_tab"] = [
            "children" => [
                "contact_tab_container" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Venue Details"),
                                "required" => 0,
                                "sortOrder" => 5,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    EventContact::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Venue Details"),
                        "collapsible" => true,
                        "sortOrder" => 5,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
        $this->createBookingOptionsPanel();
    }

    public function modifyMetaDefaultType(array $meta)
    {
        $this->meta = $meta;
        $this->meta["booking_tab"] = [
            "children" => [
                "booking_tab_container" => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "formElement" => "container",
                                "componentType" => "container",
                                'component' => 'Magento_Ui/js/form/components/html',
                                "label" => __("Booking Information"),
                                "required" => 0,
                                "sortOrder" => 1,
                                "content" => $this->layoutFactory->create()->createBlock(
                                    Booking::class
                                )->toHtml(),
                            ]
                        ]
                    ]
                ]
            ],
            "arguments" => [
                "data" => [
                    "config" => [
                        "componentType" => "fieldset",
                        "label" => __("Booking Information"),
                        "collapsible" => true,
                        "sortOrder" => 1,
                        'opened' => true,
                        'canShow' => true
                    ]
                ]
            ]
        ];
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getPanelChildren()
    {
        return [
            'configurable_products_button_set' => $this->getButtonSet(),
            'configurable-matrix' => $this->getGrid(),
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getGrid()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'label' => __('Current Variations'),
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_ConfigurableProduct/js/components/dynamic-rows-configurable',
                        'addButton' => false,
                        'isEmpty' => true,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data',
                        'dataProviderFromGrid' => '' . static::ASSOCIATED_PRODUCT_LISTING,
                        'dataProviderChangeFromGrid' => 'change_product',
                        'dataProviderFromWizard' => 'variations',
                        'map' => [
                            'id' => 'entity_id',
                            'product_link' => 'product_link',
                            'name' => 'name',
                            'sku' => 'sku',
                            'price' => 'price_number',
                            'price_string' => 'price',
                            'price_currency' => 'price_currency',
                            'qty' => 'qty',
                            // 'weight' => 'weight',
                            'thumbnail_image' => 'thumbnail_src',
                            'status' => 'status',
                            'attributes' => 'attributes',
                        ],
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                            'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
                            '__disableTmpl' => [
                                'insertDataFromGrid' => false,
                                'insertDataFromWizard' => false,
                                'changeDataFromGrid' => false
                            ],
                        ],
                        'sortOrder' => 20,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                            . static::ASSOCIATED_PRODUCT_MODAL,
                        'gridWithProducts' => 'ns=' . ''
                            . static::ASSOCIATED_PRODUCT_LISTING . ', index='
                            . static::ASSOCIATED_PRODUCT_LISTING
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
    }

    /**
     * Get configuration of column
     *
     * @param  string                    $name
     * @param  \Magento\Framework\Phrase $label
     * @param  array                     $editConfig
     * @param  array                     $textConfig
     * @return array
     */
    protected function getColumn(
        $name,
        \Magento\Framework\Phrase $label,
        $editConfig = [],
        $textConfig = []
    ) {
        $fieldEdit['arguments']['data']['config'] = [
            'dataType' => Number::NAME,
            'formElement' =>Input::NAME,
            'componentType' => Field::NAME,
            'dataScope' => $name,
            'fit' => true,
            'visibleIfCanEdit' => true,
            'imports' => [
                'visible' => '${$.provider}:${$.parentScope}.canEdit',
                '__disableTmpl' => ['visible' => false],
            ],
        ];
        $fieldText['arguments']['data']['config'] = [
            'componentType' => Field::NAME,
            'formElement' => Input::NAME,
            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-html',
            'dataType' => Text::NAME,
            'dataScope' => $name,
            'visibleIfCanEdit' => false,
            'labelVisible' => false,
            'imports' => [
                'visible' => '!${$.provider}:${$.parentScope}.canEdit',
                '__disableTmpl' => ['visible' => false],
            ],
        ];
        $fieldText['arguments']['data']['config'] = array_replace_recursive(
            $fieldText['arguments']['data']['config'],
            $textConfig
        );
        $fieldEdit['arguments']['data']['config'] = array_replace_recursive(
            $fieldEdit['arguments']['data']['config'],
            $editConfig
        );
        $container['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'label' => $label,
            'component' => 'Magento_Ui/js/form/components/group',
            'dataScope' => '',
            'showLabel' => false
        ];
        $container['children'] = [
            $name . '_edit' => $fieldEdit,
            $name . '_text' => $fieldText,
        ];

        return $container;
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return  array
     */
    protected function getRows()
    {
        $galleryUploadUrl = 'catalog/product_gallery/upload';
        $arr = [
            'record' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                            'componentType' => Container::NAME,
                            'isTemplate' => true,
                            'is_collection' => true,
                            'dataScope' => ''
                        ]
                    ]
                ],
                'children' => [
                    'thumbnail_image_container' => $this->getColumn(
                        'thumbnail_image',
                        __('Image'),
                        [
                            'component' => 'Magento_ConfigurableProduct/js/components/file-uploader',
                            'elementTmpl' => 'Magento_ConfigurableProduct/components/file-uploader',
                            'fit' => true,
                            'formElement' => 'fileUploader',
                            'componentType' => 'fileUploader',
                            'fileInputName' => 'image',
                            'isMultipleFiles' => false,
                            'links' => [
                                'thumbnailUrl' => '${$.provider}:${$.parentScope}.thumbnail_image',
                                'thumbnail' => '${$.provider}:${$.parentScope}.thumbnail',
                                'smallImage' => '${$.provider}:${$.parentScope}.small_image',
                                '__disableTmpl' => [
                                    'thumbnailUrl' => false,
                                    'thumbnail' => false,
                                    'smallImage' => false
                                ],
                            ],
                            'uploaderConfig' => [
                                'url' => $this->urlBuilder->getUrl(
                                    $galleryUploadUrl
                                ),
                            ],
                            'dataScope' => 'image',
                        ],
                        [
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'fit' => true,
                            'sortOrder' => 0
                        ]
                    ),
                    'name_container' => $this->getColumn(
                        'name',
                        __('Name'),
                        [],
                        ['dataScope' => 'product_link']
                    ),
                    'sku_container' => $this->getColumn(
                        'sku',
                        __('SKU')
                    ),
                    'price_container' => $this->getColumn(
                        'price',
                        __('Price'),
                        [
                            'imports' => [
                                'addbefore' => '${$.provider}:${$.parentScope}.price_currency',
                                '__disableTmpl' => ['addbefore' => false],
                            ],
                            'validation' => ['validate-zero-or-greater' => true]
                        ],
                        ['dataScope' => 'price_string']
                    ),
                    'quantity_container' => $this->getColumn(
                        'quantity',
                        __('Quantity'),
                        ['dataScope' => 'qty'],
                        ['dataScope' => 'qty']
                    ),
                    'status' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/cell-status',
                                    'componentType' => 'text',
                                    'label' => __('Status'),
                                    'dataScope' => 'status',
                                ]
                            ]
                        ]
                    ],
                    'attributes' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'componentType' => Field::NAME,
                                    'formElement' => Input::NAME,
                                    'dataType' => Text::NAME,
                                    'label' => __('Attributes'),
                                ],
                            ],
                        ],
                    ],
                    'actionsList' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/actions-list',
                                    'additionalClasses' => 'data-grid-actions-cell',
                                    'componentType' => 'text',
                                    'label' => __('Actions'),
                                    'fit' => true,
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
        return $arr;
    }

    /**
     * Returns Buttons Set configurations
     *
     * @return array
     */
    protected function getButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magento_ConfigurableProduct/js/components/container-configurable-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            'Configurable products allow customers to choose options '
                            . '(Ex: shirt color). You need to create a virtual product for each '
                            . 'configuration (Ex: a product for each color).'
                        ),
                        'content2' => __(
                            'Configurations cannot be created for a standard product with downloadable files. '
                            . 'To create configurations, first remove all downloadable files.'
                        ),
                        'template' => 'ui/form/components/complex',
                        'createConfigurableButton' => 'ns = ${ $.ns }, index = create_configurable_products_button',
                        '__disableTmpl' => ['createConfigurableButton' => false],
                    ],
                ],
            ],
            'children' => [
                'add_products_manually_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'displayAsLink' => true,
                                'actions' => [
                                    [
                                        'targetName' => 'ns=' . $this->formName . ', index='
                                            . static::ASSOCIATED_PRODUCT_MODAL,
                                        'actionName' => 'openModal',
                                    ],
                                    [
                                        'targetName' => 'ns=' . ''
                                            . static::ASSOCIATED_PRODUCT_LISTING
                                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
                                        'actionName' => 'showGridAssignProduct',
                                    ],
                                ],
                                'title' => __('Add Products Manually'),
                                'sortOrder' => 10,
                                'imports' => [
                                    'visible' => 'ns = ${ $.ns }, index = '
                                        . static::CONFIGURABLE_MATRIX . ':isShowAddProductButton',
                                    '__disableTmpl' => ['visible' => false],
                                ],
                            ],
                        ],
                    ],
                ],
                'create_configurable_products_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' =>
                                            $this->dataScopeName . '.configurableModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' =>
                                            $this->dataScopeName . '.configurableModal',
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('Create Configurations'),
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * createHotelBookingOptionsPanel creates panel for hotel booking product
     *
     * @return $this
     */
    private function createHotelBookingOptionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::GROUP_HOTELBOOKING_OPTIONS_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Booking Info'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                'collapsible' => true,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_BOOKING_OPTIONS_PREVIOUS_NAME,
                                    static::GROUP_BOOKING_OPTIONS_DEFAULT_SORT_ORDER
                                ),
                                'opened' => true,
                                'canShow' => true
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_NEAR_BY_MAP => $this->getNearByMapFieldConfig(10),
                        static::FIELD_PRICE_CHARGED_PER_HOTEL => $this->getPricePerHotelQtyFieldConfig(20),
                        static::FIELD_ENABLE_ASK_QUES => $this->getIsEnableAskQuestionFieldConfig(30),
                        'amenities_info' => $this->createAmenitiesPanel(40),
                        static::FIELD_HOTEL_CHECK_IN_OUT_TIME => $this->getHotelTimeFieldConfig(50)
                    ]
                ]
            ]
        );
        return $this;
    }

    /**
     * getHotelTimeFieldConfig Get Is Hotel Check in and out Time Field Config
     *
     * @param int $sortOrder
     * @return array
     */
    private function getHotelTimeFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'type' => 'container',
                    'name' => static::FIELD_HOTEL_CHECK_IN_OUT_TIME,
                    'config' => [
                        'type' => 'container',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/html',
                        "content" => $this->layoutFactory->create()->createBlock(
                            CheckTime::class
                        )->toHtml(),
                        'additionalClasses' => 'admin__control-hotel-check-time',
                    ]
                ]
            ],
        ];
    }

    /**
     * createAmenitiesPanel creates panel for amenities in hotel booking
     *
     * @param int $sortOrder
     * @return array
     */
    private function createAmenitiesPanel($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Amenities'),
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'sortOrder' => $sortOrder,
                        'component' => 'Magento_Ui/js/form/components/html',
                        "content" => $this->layoutFactory->create()->createBlock(
                            Amenities::class
                        )->toHtml(),
                    ]
                ]
            ]
        ];
    }

    /**
     * createBookingOptionsPanel Create "Ticket and Quantity" panel
     *
     * @return $this
     */
    private function createBookingOptionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::GROUP_BOOKING_OPTIONS_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Ticket and Quantity'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                'collapsible' => true,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_BOOKING_OPTIONS_PREVIOUS_NAME,
                                    static::GROUP_BOOKING_OPTIONS_DEFAULT_SORT_ORDER
                                ),
                                'opened' => true,
                                'canShow' => true
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_EVENT_DATE => $this->getEventDateFieldConfig(5),
                        static::FIELD_EVENT_CHART_AVAILABLE => $this->getEventChartFieldConfig(10),
                        static::FIELD_EVENT_CHART_IMAGE => $this->getEventChartImageFieldConfig(20),
                        static::FIELD_PRICE_CHARGED_PER => $this->getPricePerQtyFieldConfig(30),
                        static::FIELD_IS_MULTIPLE_TICKET => $this->getIsMultiTicketFieldConfig(40),
                        static::FIELD_ENABLE => $this->getEnableFieldConfig(50),
                        static::GRID_OPTIONS_NAME => $this->getBookingOptionsGridConfig(60)
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * getEventDateFieldConfig Get Is EventDate Field Config
     *
     * @param int $sortOrder
     * @return array
     */
    private function getEventDateFieldConfig($sortOrder)
    {
        $product = $this->locator->getProduct();
        $fromDate = 'today';
        if ($product->getEventDateFrom()) {
            if (strtotime(date('d-m-Y') <= strtotime($product->getEventDateFrom()))) {
                $fromDate = date('d-m-Y', strtotime($product->getEventDateFrom()));
            }
        }
        return [
            'arguments' => [
                'data' => [
                    'type' => 'container',
                    'name' => static::FIELD_EVENT_DATE,
                    'config' => [
                        'type' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => __('Event Date'),
                        'additionalClasses' => 'admin__control-grouped-date',
                    ]
                ]
            ],'children' => [
                static::FIELD_EVENT_DATE_FROM => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Event Date'),
                                'formElement' => 'date',
                                'componentType' => Input::NAME,
                                'dataScope' => static::FIELD_EVENT_DATE_FROM,
                                'dataType' => 'date',
                                'sortOrder' => 1,
                                'required' => true,
                                'validation' => [
                                    'required-entry' => true
                                ],
                                'component' => 'Magento_Ui/js/form/element/date',
                                'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                                'additionalClasses' => 'admin__field-date',
                                'uid' => 'wk-booking-event-from',
                                // 'storeTimeZone' => 'UTC',
                                'options' => [
                                    'dateFormat' => 'M/d/yy',
                                    'timeFormat' => 'HH:mm',
                                    'showsTime' => true,
                                    'minDate' => $fromDate,
                                    'storeLocale' => 'en_US'
                                ]
                            ]
                        ]
                    ]
                ],
                static::FIELD_EVENT_DATE_TO => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Event Date'),
                                'formElement' => 'date',
                                'componentType' => Input::NAME,
                                'dataScope' => static::FIELD_EVENT_DATE_TO,
                                'dataType' => 'date',
                                'sortOrder' => 2,
                                'required' => true,
                                'validation' => [
                                    'required-entry' => true
                                ],
                                'component' => 'Magento_Ui/js/form/element/date',
                                'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                                'additionalClasses' => 'admin__field-date',
                                //'storeTimeZone' => 'UTC',
                                'options' => [
                                    'dateFormat' => 'M/d/yy',
                                    'timeFormat' => 'HH:mm',
                                    'showsTime' => true,
                                    'minDate' => 'today',
                                    'storeLocale' => 'en_US'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * getNearByMapFieldConfig Get Is Event Chart Available Field Config
     *
     * @param  int $sortOrder
     * @return array
     */
    private function getNearByMapFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Show Nearby Map'),
                        'formElement' => 'checkbox',
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_NEAR_BY_MAP,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'component' => 'Magento_Ui/js/form/element/single-checkbox',
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'prefer' => 'toggle',
                        'valueMap' => ['true'=>'1', 'false'=>'0']
                    ]
                ]
            ]
        ];
    }

    /**
     * getPricePerHotelQtyFieldConfig Get Price Charged Per Qty Field Config
     *
     * @param  int $sortOrder
     * @return array
     */
    private function getPricePerHotelQtyFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Price Charged Per'),
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'formElement' => 'select',
                        'visible' => true,
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_PRICE_CHARGED_PER_HOTEL,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'additionalClasses' => 'wk-select-wide',
                        'options' => [
                            ['value' => 1, 'label' => __('Night')]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * getEventChartFieldConfig Get Is Event Chart Available Field Config
     *
     * @param int $sortOrder
     * @return array
     */
    private function getEventChartFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Event Map/Chart Available'),
                        'formElement' => 'checkbox',
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_EVENT_CHART_AVAILABLE,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'component' => 'Magento_Ui/js/form/element/single-checkbox',
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'prefer' => 'toggle',
                        'valueMap' => ['true'=>'1', 'false'=>'0']
                    ]
                ]
            ]
        ];
    }

    /**
     * getEventChartImageFieldConfig Get Event Chart Image Upload Field Config
     *
     * @param int $sortOrder
     * @return array
     */
    private function getEventChartImageFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'fileUploader',
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_EVENT_CHART_IMAGE,
                        'dataType' => 'fileUploader',
                        'allowedExtensions' => 'jpg jpeg gif png',
                        'notice' => __('Upload an Image in JPG, JPEG, GIF, PNG Format.'),
                        'sortOrder' => $sortOrder,
                        'additionalClasses' => 'wk-bk-chart-image',
                        'validation' => [
                            'required-entry' => true
                        ],
                        'imports' => [
                            'visible' => 'ns = ${ $.ns }, index = '.self::FIELD_EVENT_CHART_AVAILABLE.':checked'
                        ],
                        'uploaderConfig' => [
                            'url' => 'mpadvancebooking/bookings/eventChartUpload'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * getPricePerQtyFieldConfig Get Price Charged Per Qty Field Config
     *
     * @param int $sortOrder
     * @return array
     */
    private function getPricePerQtyFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Price Charged Per'),
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'formElement' => 'select',
                        'visible' => true,
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_PRICE_CHARGED_PER,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'additionalClasses' => 'wk-select-wide',
                        'options' => [
                            ['value' => 1, 'label' => __('Ticket')]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * getIsEnableAskQuestionFieldConfig Get Is Enable Ask Question Field Config
     *
     * @param  int $sortOrder
     * @return array
     */
    private function getIsEnableAskQuestionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Enable Ask a Question'),
                        'formElement' => 'checkbox',
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_ENABLE_ASK_QUES,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'component' => 'Magento_Ui/js/form/element/single-checkbox',
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'prefer' => 'toggle',
                        'valueMap' => ['true'=>'1', 'false'=>'0']
                    ]
                ]
            ]
        ];
    }

    /**
     * getIsMultiTicketFieldConfig Get Is Multi Ticket Allowed Field Config
     *
     * @param int $sortOrder
     * @return array
     */
    private function getIsMultiTicketFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Multiple Tickets'),
                        'formElement' => 'checkbox',
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_IS_MULTIPLE_TICKET,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'component' => 'Magento_Ui/js/form/element/single-checkbox',
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'prefer' => 'toggle',
                        'valueMap' => ['true'=>'1', 'false'=>'0']
                    ]
                ]
            ]
        ];
    }

    /**
     * getBookingOptionsGridConfig Get config for the whole grid
     *
     * @param int $sortOrder
     * @return array
     */
    private function getBookingOptionsGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Ticket'),
                        'componentType' => DynamicRows::NAME,
                        'name' => 'options',
                        'component' => 'Magento_Catalog/js/components/dynamic-rows-import-custom-options',
                        'template' => 'Webkul_MpAdvancedBookingSystem/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $sortOrder,
                        'dataProvider' => static::CUSTOM_OPTIONS_LISTING,
                        'imports' => ['insertData' => '${ $.provider }:${ $.dataProvider }']
                    ]
                ]
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('New Ticket'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true
                            ]
                        ]
                    ],
                    'children' => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'collapsible' => false,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ]
                                ]
                            ],
                            'children' => [
                                static::GRID_TYPE_SELECT_NAME => $this->getBookingSelectTypeGridConfig(30)
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * getBookingSelectTypeGridConfig Get config for grid for "select" types
     *
     * @param int $sortOrder
     * @return array
     */
    private function getBookingSelectTypeGridConfig($sortOrder)
    {
        $options = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'imports' => [
                            'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                            'optionTypeId' => '${ $.provider }:${ $.parentScope }.option_type_id',
                            'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default'
                        ],
                        'service' => [
                            'template' => 'Magento_Catalog/form/element/helper/custom-option-type-service'
                        ]
                    ]
                ]
            ]
        ];
        $product = $this->locator->getProduct();
        $visible = false;
        if ($product->getIsMultipleTickets()) {
            $visible = true;
        }
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Ticket Type'),
                        'addRowLabel' => __('New Ticket Type'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Webkul_MpAdvancedBookingSystem/js/dynamic-rows/dynamic-rows',
                        'template' => 'Webkul_MpAdvancedBookingSystem/dynamic-rows/templates/default',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'btnIsVisible' => $visible,
                        'renderDefaultRecord' => true,
                        'sortOrder' => $sortOrder
                    ]
                ]
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ]
                        ]
                    ],
                    'children' => [
                        static::FIELD_SKU_NAME => $this->getBookingSkuFieldConfig(10),
                        static::FIELD_TITLE_NAME => $this->getBookingTitleFieldConfig(
                            20,
                            $this->locator->getProduct()->getStoreId() ? $options : []
                        ),
                        static::FIELD_PRICE_NAME => $this->getBookingPriceFieldConfigForSelectType(30),
                        static::FIELD_PRICE_TYPE_NAME => $this->getBookingPriceTypeFieldConfig(
                            30,
                            ['fit' => true]
                        ),
                        static::FIELD_STOCK_NAME => $this->getBookingStockFieldConfig(40),
                        static::FIELD_IS_IN_STOCK_NAME => $this->getBookingIsInStockFieldConfig(
                            50,
                            ['fit' => true]
                        ),
                        static::FIELD_DESCRIPTION_NAME => $this->getBookingDescriptionFieldConfig(60),
                        static::FIELD_SORT_ORDER_NAME => $this->getBookingPositionFieldConfig(70),
                        static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(80)
                    ]
                ]
            ]
        ];
    }

    /**
     * getBookingSkuFieldConfig Get config for "SKU" field
     *
     * @param int $sortOrder
     * @return array
     */
    private function getBookingSkuFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('SKU'),
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SKU_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'visible' => true,
                        'validation' => [
                            'required-entry' => true
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * getBookingTitleFieldConfig Get config for "Title" fields
     *
     * @param int $sortOrder
     * @param array $options
     * @return array
     */
    private function getBookingTitleFieldConfig($sortOrder)
    {
        return [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Name'),
                            'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                            'visible' => true,
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => static::FIELD_TITLE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ]
                        ]
                    ]
                ]
            ];
    }

    /**
     * Get config for "Price" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getBookingPriceFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Price'),
                        'componentType' => Field::NAME,
                        'component' => 'Magento_Catalog/js/components/custom-options-component',
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_NAME,
                        'dataType' => Number::NAME,
                        'addbefore' => $this->getCurrencySymbol(),
                        'addbeforePool' => $this->productOptionsPrice->prefixesToOptionArray(),
                        'sortOrder' => $sortOrder,
                        'visible' => true,
                        'validation' => [
                            'required-entry' => true,
                            'validate-zero-or-greater' => true
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * getBookingPriceFieldConfigForSelectType Get config for "Price" field for select type.
     *
     * @param int $sortOrder
     * @return array
     */
    private function getBookingPriceFieldConfigForSelectType(int $sortOrder)
    {
        $priceFieldConfig = $this->getPriceFieldConfig($sortOrder);
        $priceFieldConfig['arguments']['data']['config']['template'] = 'Webkul_MpAdvancedBookingSystem/form/field';

        return $priceFieldConfig;
    }

    /**
     * getBookingStockFieldConfig Get config for stock field
     *
     * @param int $sortOrder
     * @return array
     */
    private function getBookingStockFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Quantity'),
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_STOCK_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'visible' => true,
                        'validation' => [
                            'required-entry' => true,
                            'validate-digits' => true
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * getBookingIsInStockFieldConfig Get config for "Is In Stock Type" field
     *
     * @param int $sortOrder
     * @param array $config
     * @return array
     */
    private function getBookingIsInStockFieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Stock'),
                            'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                            'visible' => true,
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'dataScope' => static::FIELD_IS_IN_STOCK_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => [
                                ['value' => 1, 'label' => __('In Stock')],
                                ['value' => 0, 'label' => __('Out of Stock')],
                            ],
                            'validation' => [
                                'required-entry' => true
                            ]
                        ]
                    ]
                ]
            ],
            $config
        );
    }

    /**
     * getBookingDescriptionFieldConfig Get config for description field
     *
     * @param int $sortOrder
     * @return array
     */
    private function getBookingDescriptionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'type' => 'form.textarea',
                    'config' => [
                        'label' => __('Description'),
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'componentType' => Field::NAME,
                        'formElement' => 'textarea',
                        'dataScope' => static::FIELD_DESCRIPTION_NAME,
                        'dataType' => 'textarea',
                        'sortOrder' => $sortOrder,
                        'visible' => true,
                        'cols' => 80,
                        'validation' => [
                            'required-entry' => true
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * getBookingPositionFieldConfig Get config for hidden field used for sorting
     *
     * @param int $sortOrder
     * @return array
     */
    private function getBookingPositionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ]
                ]
            ]
        ];
    }

    /**
     * getBookingPriceTypeFieldConfig Get config for "Price Type" field
     *
     * @param int $sortOrder
     * @param array $config
     * @return array
     */
    private function getBookingPriceTypeFieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Price Type'),
                            'template' => 'Webkul_MpAdvancedBookingSystem/form/field',
                            'component' => 'Magento_Catalog/js/components/custom-options-price-type',
                            'visible' => false,
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'dataScope' => static::FIELD_PRICE_TYPE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => $this->productOptionsPrice->toOptionArray(),
                            'imports' => [
                                'priceIndex' => self::FIELD_PRICE_NAME,
                            ]
                        ]
                    ]
                ]
            ],
            $config
        );
    }

    /**
     * Get product type
     *
     * @return null|string
     */
    private function getProductType()
    {
        return (string)$this->request->getParam(
            'type',
            $this->locator->getProduct()->getTypeId()
        );
    }

    /**
     * Get product Set Id
     *
     * @return int
     */
    private function getProductAttributeSetId()
    {
        return (string)$this->request->getParam(
            'set',
            $this->locator->getProduct()->getAttributeSetId()
        );
    }

    /**
     * Add Attribute Set Data to meta if not available or removed
     *
     * @param array $meta
     * @return null|array
     */
    protected function addAttributeSetData($meta)
    {
        if ($panelName = $this->getGeneralPanelName($meta)) {
            if (empty($meta[$panelName]['children']['attribute_set_id']['arguments']['data']['config']['options'])) {
                $metaWithAttributeSet = $this->attributeSet->modifyMeta($meta);
                if (!empty($metaWithAttributeSet[$panelName]['children']['attribute_set_id'])) {
                    $attributeSetArray = $metaWithAttributeSet[$panelName]['children']['attribute_set_id'];
                    $meta[$panelName]['children']['attribute_set_id'] = $attributeSetArray;
                }
            }
        }
        return $meta;
    }

    /**
     * Returns configurations for the messages container
     *
     * @return array
     */
    protected function getAttributeSetErrorContainer()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magento_Ui/js/form/components/html',
                        'componentType' => Container::NAME,
                        'content' => '',
                        'sortOrder' => 10,
                        'visible' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns new attribute set input configuration
     *
     * @return array
     */
    protected function getNewAttributeSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType' => Text::NAME,
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => 'configurableNewAttributeSetName',
                        'additionalClasses' => 'new-attribute-set-name',
                        'label' => __('New Attribute Set Name'),
                        'sortOrder' => 40,
                        'validation' => ['required-entry' => true],
                        'imports' => [
                            'visible' => 'ns = ${ $.ns }, index = affectedAttributeSetNew:checked',
                            'disabled' => '!ns = ${ $.ns }, index = affectedAttributeSetNew:checked',
                        ]
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns configuration for existing attribute set options
     *
     * @param array $meta
     * @return null|array
     */
    protected function getExistingAttributeSet($meta)
    {
        $ret = null;
        if ($panelName = $this->getGeneralPanelName($meta)) {
            if (empty($meta[$panelName]['children']['attribute_set_id']['arguments']['data']['config']['options'])) {
                $metaWithAttributeSet = $this->attributeSet->modifyMeta($meta);
                if (!empty($metaWithAttributeSet[$panelName]['children']['attribute_set_id'])) {
                    $attributeSetArray = $metaWithAttributeSet[$panelName]['children']['attribute_set_id'];
                    $meta[$panelName]['children']['attribute_set_id'] = $attributeSetArray;
                }
            }
            if (!empty($meta[$panelName]['children']['attribute_set_id']['arguments']['data']['config']['options'])) {
                $options = $meta[$panelName]['children']['attribute_set_id']['arguments']['data']['config']['options'];
                $ret = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Ui/js/form/element/ui-select',
                                'disableLabel' => true,
                                'filterOptions' => false,
                                'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                                'formElement' => 'select',
                                'componentType' => Field::NAME,
                                'options' => $options,
                                'label' => __('Choose existing Attribute Set'),
                                'dataScope' => 'configurableExistingAttributeSetId',
                                'sortOrder' => 60,
                                'multiple' => false,
                                'imports' => [
                                    'value' => 'ns = ${ $.ns }, index = attribute_set_id:value',
                                    'visible' => 'ns = ${ $.ns }, index = affectedAttributeSetExisting:checked',
                                    'disabled' => '!ns = ${ $.ns }, index = affectedAttributeSetExisting:checked',
                                ],
                            ],
                        ],
                    ],
                ];
            }
        }

        return $ret;
    }

    /**
     * Returns confirm button configuration
     *
     * @return array
     */
    protected function getConfirmButton()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => 100,
                    ],
                ],
            ],
            'children' => [
                'confirm_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => 'product_form.product_form.configurableVariations',
                                        'actionName' => 'addNewAttributeSetHandler',
                                    ],
                                ],
                                'title' => __('Confirm'),
                                'sortOrder' => 10
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
