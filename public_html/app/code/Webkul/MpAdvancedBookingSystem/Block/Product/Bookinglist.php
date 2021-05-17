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

namespace Webkul\MpAdvancedBookingSystem\Block\Product;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory as InfoCollection;

/**
 * Webkul MpAdvancedBookingSystem Bookinglist Class
 */
class Bookinglist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /** @var \Magento\Catalog\Model\Product */
    protected $_productlists;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollection
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $salesListCollection
     * @param InfoCollection $infoCollection
     * @param \Webkul\MpAdvancedBookingSystem\Model\Source\BookingType\Options $bookingTypeOptions
     * @param array $data = []
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollection,
        \Magento\Catalog\Model\ProductFactory $product,
        \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $salesListCollection,
        InfoCollection $infoCollection,
        \Webkul\MpAdvancedBookingSystem\Model\Source\BookingType\Options $bookingTypeOptions,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_imageHelper = $context->getImageHelper();
        $this->_priceCurrency = $priceCurrency;
        $this->mpHelper = $mpHelper;
        $this->eavAttribute = $eavAttribute;
        $this->mpProductCollection = $mpProductCollection;
        $this->product = $product;
        $this->salesListCollection = $salesListCollection;
        $this->infoCollection = $infoCollection;
        $this->bookingTypeOptions = $bookingTypeOptions;
        parent::__construct($context, $data);
    }

    /**
     * _construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Booking Product List'));
    }

    /**
     * Get formatted by price and currency.
     *
     * @param   $price
     * @param   $currency
     *
     * @return array|float
     */
    public function getFormatedPrice($price, $currency)
    {
        return $this->_priceCurrency->format(
            $price,
            true,
            2,
            null,
            $currency
        );
    }

    /**
     * getAllProducts
     *
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        $storeId = $this->mpHelper->getCurrentStoreId();
        $websiteId = $this->mpHelper->getWebsiteId();
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->_productlists) {
            $paramData = $this->getRequest()->getParams();
            $filter = '';
            $filterStatus = '';
            $filterDateFrom = '';
            $filterDateTo = '';
            $from = null;
            $to = null;

            if (isset($paramData['s'])) {
                $filter = $paramData['s'] != '' ? $paramData['s'] : '';
            }
            if (isset($paramData['status'])) {
                $filterStatus = $paramData['status'] != '' ? $paramData['status'] : '';
            }
            if (isset($paramData['from_date'])) {
                $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
            }
            if (isset($paramData['to_date'])) {
                $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
            }
            if ($filterDateTo) {
                $todate = date_create($filterDateTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if (!$to) {
                $to = date('Y-m-d 23:59:59');
            }
            if ($filterDateFrom) {
                $fromdate = date_create($filterDateFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            $eavAttribute = $this->eavAttribute;
            $proAttId = $eavAttribute->getIdByCode('catalog_product', 'name');
            $proStatusAttId = $eavAttribute->getIdByCode('catalog_product', 'status');

            $catalogProductEntity = $this->mpProductCollection->create()->getTable('catalog_product_entity');

            $catalogProductEntityVarchar = $this->mpProductCollection->create()
                                                ->getTable('catalog_product_entity_varchar');

            $catalogProductEntityInt = $this->mpProductCollection->create()->getTable('catalog_product_entity_int');

            /* Get Seller Product Collection for current Store Id */

            $storeCollection = $this->mpProductCollection->create()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = '.$storeId.' AND 
                cpev.value like "%'.$filter.'%" AND 
                cpev.attribute_id = '.$proAttId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = '.$storeId.' AND 
                cpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $storeCollection->getSelect()->where(
                    'cpei.value = '.$filterStatus
                );
            }

            $storeCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            if ($from && $to) {
                $storeCollection->getSelect()->where(
                    "cpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $storeCollection->getSelect()->group('mageproduct_id');

            $storeProductIDs = $storeCollection->getAllIds();

            /* Get Seller Product Collection for 0 Store Id */

            $adminStoreCollection = $this->mpProductCollection->create();

            $adminStoreCollection->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = 0 AND 
                cpev.value like "%'.$filter.'%" AND 
                cpev.attribute_id = '.$proAttId
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = 0 AND 
                cpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $adminStoreCollection->getSelect()->where(
                    'cpei.value = '.$filterStatus
                );
            }

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );
            if ($from && $to) {
                $adminStoreCollection->getSelect()->where(
                    "cpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $adminStoreCollection->getSelect()->group('mageproduct_id');

            $adminProductIDs = $adminStoreCollection->getAllIds();

            $productIDs = array_merge($storeProductIDs, $adminProductIDs);

            $bookingProduct = $this->infoCollection->create();
            $allIds = $bookingProduct->getAllProductIds();
            $productIDs = array_unique(array_intersect($productIDs, $allIds));

            $collection = $this->mpProductCollection->create()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )
            ->addFieldToFilter(
                'mageproduct_id',
                ['in' => $productIDs]
            );
            $collection->setOrder('mageproduct_id');

            $this->_productlists = $collection;
        }

        return $this->_productlists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.bookingproduct.list.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
        }

        return $this;
    }

    /**
     * getProductData
     *
     * @param string|int $id
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductData($id = '')
    {
        return $this->product->create()->load($id);
    }

    /**
     * getPagerHtml
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * imageHelperObj
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }

    /**
     * getSalesdetail
     *
     * @param string|int $productId
     * @return array
     */
    public function getSalesdetail($productId = '')
    {
        $sum = 0;
        $arr = [];

        $collection = $this->mpProductCollection->create()
            ->addFieldToFilter(
                'mageproduct_id',
                $productId
            )->addFieldToSelect('seller_id')
            ->distinct(true);
        $sellerArr = $collection->getAllSellerIds();

        $data = [
            'quantitysoldconfirmed' => 0,
            'quantitysoldpending' => 0,
            'amountearned' => 0,
            'clearedat' => 0,
            'quantitysold' => 0,
        ];
        
        $quantity = $this->salesListCollection->create()
            ->addFieldToFilter(
                'mageproduct_id',
                $productId
            )
            ->addFieldToFilter(
                'seller_id',
                ['in' => $sellerArr]
            )->getSellerOrderCollection();

        foreach ($quantity as $rec) {
            $status = $rec->getCpprostatus();
            $data['quantitysold'] = $data['quantitysold'] + $rec->getMagequantity();
            if ($status == 1) {
                $data['quantitysoldconfirmed'] = $data['quantitysoldconfirmed'] + $rec->getMagequantity();
            } else {
                $data['quantitysoldpending'] = $data['quantitysoldpending'] + $rec->getMagequantity();
            }
        }

        $amountearned = $this->salesListCollection->create()
            ->addFieldToFilter(
                'cpprostatus',
                \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_COMPLETE
            )
            ->addFieldToFilter(
                'mageproduct_id',
                $productId
            )
            ->addFieldToFilter(
                'seller_id',
                ['in' => $sellerArr]
            )->getSellerOrderCollection();

        foreach ($amountearned as $rec) {
            $data['amountearned'] = $data['amountearned'] + $rec['actual_seller_amount'];
            $arr[] = $rec['created_at'];
        }
        $data['created_at'] = $arr;

        return $data;
    }

    /**
     * getBookingType
     *
     * @param int $attributeSetId
     * @return void|string
     */
    public function getBookingType($attributeSetId)
    {
        $optArr = $this->bookingTypeOptions->toOptionArray();
        $key = array_search($attributeSetId, array_column($optArr, 'value'));
        if (!empty($optArr[$key]['label']) && $optArr[$key]['value'] == $attributeSetId) {
            return $optArr[$key]['label'];
        }
    }
}
