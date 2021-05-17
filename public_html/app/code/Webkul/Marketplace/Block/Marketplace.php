<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Block;

/*
 * Webkul Marketplace Landing Page Block
 */
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory as MpOrdersCollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as MpSaleslistCollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;

class Marketplace extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $stdlibDateTime;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $entityAttribute;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var SellerCollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @var MpOrdersCollectionFactory
     */
    protected $mpOrdersCollectionFactory;

    /**
     * @var MpSaleslistCollectionFactory
     */
    protected $mpSaleslistCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $entityAttribute
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param MpOrdersCollectionFactory $mpOrdersCollectionFactory
     * @param MpSaleslistCollectionFactory $mpSaleslistCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param SellerCollectionFactory $sellerCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $entityAttribute,
        \Webkul\Marketplace\Helper\Data $helper,
        MpOrdersCollectionFactory $mpOrdersCollectionFactory,
        MpSaleslistCollectionFactory $mpSaleslistCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        SellerCollectionFactory $sellerCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->imageHelper = $context->getImageHelper();
        $this->filterProvider = $filterProvider;
        $this->resource = $resource;
        $this->entityAttribute = $entityAttribute;
        $this->helper = $helper;
        $this->mpOrdersCollectionFactory = $mpOrdersCollectionFactory;
        $this->mpSaleslistCollectionFactory = $mpSaleslistCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->_messageManager = $messageManager;
        parent::__construct($context, $data);
    }

    public function imageHelperObj()
    {
        return $this->imageHelper;
    }

    /**
     * Prepare HTML content.
     *
     * @return string
     */
    public function getCmsFilterContent($value = '')
    {
        return $this->filterProvider->getPageFilter()->filter($value);
    }

    public function getStoreId()
    {
        if (count($this->helper->getAllStores()) == 1 && count($this->helper->getAllWebsites()) == 1) {
            $storeId = 0;
        } else {
            $storeId = $this->helper->getCurrentStoreId();
        }

        return $storeId;
    }

    public function getSellersCollection()
    {
        $paramData = $this->getRequest()->getParams();

        $marketplaceProduct = $this->resource->getTableName('marketplace_product');
        $productPrice = $this->resource->getTableName('catalog_product_index_price');
        $sellerTime = $this->resource->getTableName('wk_mp_booking_info');
        $form = $this->resource->getTableName('form');

        $collection = $this->sellerCollectionFactory->create();
        $collection->getSelect()->join(
            ['mp' => $marketplaceProduct],
            'main_table.seller_id = mp.seller_id',
            ['mageproduct_id']
        );
        $collection->getSelect()->join(
            ['cp' => $productPrice],
            'mp.mageproduct_id = cp.entity_id',
            ['price']
        );
        $collection->getSelect()->join(
            ['st' => $sellerTime],
            'mp.mageproduct_id = st.product_id',
            ['info']
        );
        $collection->getSelect()->join(
            ['fm'=> $form],
            'main_table.shop_title = fm.name',
            ['email','telephone']
        );

        $collection->addFieldToFilter('main_table.store_id', ['eq' => 1]);
        $collection->addFieldToSelect(['entity_id', 'is_seller', 'seller_id', 'experience', 'shop_url', 'banner_pic', 'shop_title', 'logo_pic', 'company_locality', 'country_pic', 'company_description',
            'store_id', 'specialties_general', 'specialties_work', 'specialties_social', 'about_me','time_zone','styles']);
        $collection->getSelect()
            ->columns('MIN(cp.price) AS price')
            ->group('main_table.seller_id');

        if (isset($paramData['date']) && $paramData['date']) {
            $week = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
            $selectDay = date("l", strtotime($paramData['date']));
            $datefilter = [];

            foreach ($week as $day => $key) {
                if ($day == $selectDay) {
                    $datefilter[] = ['like' => '%"' . intval($key) . '":[{%'];
                }
                if (count($datefilter)) {
                    $collection->addFieldToFilter('st.info', $datefilter);
                }
            }
        }
        if (isset($paramData['expgeneral']) && $paramData['expgeneral']) {
            $expgeneral = explode(',', $paramData['expgeneral']);
            $expgenfilter = [];
            foreach ($expgeneral as $value) {
                if ($value != 0) {
                    $expgenfilter[] = ['like' => '%"' . intval($value) . '"%'];

                    if (count($expgenfilter)) {
                        $collection->addFieldToFilter('main_table.specialties_general', $expgenfilter);
                    }
                }
            }
        }

        if (isset($paramData['expwork']) && $paramData['expwork']) {
            $expwork = explode(',', $paramData['expwork']);
            $expworkfilter = [];
            foreach ($expwork as $value) {
                if ($value != 0) {
                    $expworkfilter[] = ['like' => '%"' . intval($value) . '"%'];

                    if (count($expworkfilter)) {
                        $collection->addFieldToFilter('main_table.specialties_work', $expworkfilter);
                    }
                }
            }
        }

        if (isset($paramData['expsocial']) && $paramData['expsocial']) {
            $expsocial = explode(',', $paramData['expsocial']);
            $expsocialfilter = [];
            foreach ($expsocial as $value) {
                if ($value != 0) {
                    $expsocialfilter[] = ['like' => '%"' . intval($value) . '"%'];

                    if (count($expsocialfilter)) {
                        $collection->addFieldToFilter('main_table.specialties_social', $expsocialfilter);
                    }
                }
            }
        }

        if (isset($paramData['sort']) && $paramData['sort']) {
            if ($paramData['sort'] == 'high-low') {
                $collection->setOrder('price', 'desc');
            } elseif ($paramData['sort'] == 'low-high') {
                $collection->setOrder('price', 'asc');
            }
        }

        if (isset($paramData['zipcode']) && $paramData['zipcode']) {
            $zipcodes = $this->getStatus($paramData['zipcode']);
            $pos1 = strpos($zipcodes, 'error_code":400');
            $pos2 = strpos($zipcodes, 'error_code":404');
            $pos3 = strpos($zipcodes, 'error_code":401');
            $pos4 = strpos($zipcodes, 'error_code":429');

            if ($pos1 !== false) {
                $this->_messageManager->addError(
                    __('The request format was not correct.')
                );
            } elseif ($pos2 !== false) {
                $this->_messageManager->addError(
                    __('A zip code you provided was not found.')
                );
            } elseif ($pos3 !== false) {
                $this->_messageManager->addError(
                    __('The API key was not found, was not activated, or has been disabled.')
                );
            } elseif ($pos4 !== false) {
                $this->_messageManager->addError(
                    __('The usage limit for your application has been exceeded for the hour time period.')
                );
            } else {
                $zipcodes = str_replace('"', '', $zipcodes);
                $zipcodes = str_replace('{zip_codes:[', '', $zipcodes);
                $zipcodes = str_replace(']}', '', $zipcodes);
                $zip = explode(',', $zipcodes);
                $collection->getSelect()
                ->where(
                    'main_table.zip_code_stylist IN (' . implode(',', $zip) . ')'
                );
            }
        }

        if (isset($paramData['yf'])) {
            $collection->addFieldToFilter('experience', ['gteq'=>$paramData['yf']] ?: 0);
        }
        if (isset($paramData['yt'])) {
            $collection->addFieldToFilter('experience', ['lteq'=>$paramData['yt']] ?: 15);
        }
        return $collection;
    }

    public function getStatus($zip)
    {                                                                                     // mswrocw3GfRPlR8nq3auBpA4qlqetk0DfNow2TtWmEOWZQ88F9lvnH6R9DCUJYTW
        $clientKey = "mswrocw3GfRPlR8nq3auBpA4qlqetk0DfNow2TtWmEOWZQ88F9lvnH6R9DCUJYTW"; //TT23xTjufEftHBrx6diCSW7L5WE69flQiVkZOHlmtya9hb35S1WN8P2wXMuq3SJc
        $url = "https://www.zipcodeapi.com/rest/" . $clientKey . "/radius.json/" . $zip . "/4/km?minimal";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }
}
