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
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Webkul\Marketplace\Model\Seller;
use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory as MpOrdersCollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as MpSaleslistCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class Marketplace extends \Magento\Framework\View\Element\Template
{
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
	 * @var \Magento\Framework\Message\ManagerInterface
	 */
	protected $_messageManager;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $entityAttribute
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param MpOrdersCollectionFactory $mpOrdersCollectionFactory
     * @param MpSaleslistCollectionFactory $mpSaleslistCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
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
        $this->productRepository = $productRepository;
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

    public function getBestSaleSellers()
    {
        $marketplaceUserdata = $this->resource->getTableName('marketplace_userdata');
        $catalogProductEntityInt = $this->resource->getTableName('catalog_product_entity_int');
        $marketplaceProduct = $this->resource->getTableName('marketplace_product');
        $catalogProductWebsite = $this->resource->getTableName('catalog_product_website');
        $proAttId = $this->entityAttribute->getIdByCode('catalog_product', 'visibility');

        $helper = $this->helper;
        $sellersOrder = $this->mpOrdersCollectionFactory->create()->addFieldToSelect('seller_id');
        $storeId = $this->getStoreId();
        $sellersOrder->joinSellerTable();
        $sellersOrder->addActiveSellerFilter();
        $sellersOrder->addFieldToFilter('invoice_id', ['neq' => 0]);
        $sellersOrder->getSelect()->group('main_table.seller_id');
        $sellersOrder = $helper->joinCustomer($sellersOrder);
        $sellersOrder->resetColumns();
        $sellersOrder->getSelect()->columns('seller_id');

        $websiteId = $helper->getWebsiteId();
        $joinTable = $this->resource->getTableName('customer_grid_flat');
        $paramData = $this->getRequest()->getParams();
        $sellerArr = [];
        $sellerIdsArr = [];
        foreach ($sellersOrder as $value) {
            $sellerId = $value['seller_id'];
            if ($sellerHelperProCount = $helper->getSellerProCount($sellerId)) {
                $sellerArr[$sellerId] = [];
                array_push($sellerIdsArr, $sellerId);


                if (count($sellerArr[$sellerId]) < 3) {
                    $sellerProCount = count($sellerArr[$sellerId]);
                    $sellerProductColl = $this->productCollectionFactory->create()
                    ->addFieldToFilter(
                        'status',
                        ['eq' => 1]
                    )->addFieldToFilter(
                        'visibility',
                        ['eq' => 4]
                    )
                    ->addFieldToFilter(
                        'entity_id',
                        ['nin' => $sellerArr[$sellerId]]
                    );
                    $sellerProductColl->getSelect()
                    ->join(
                        ['cpw' => $catalogProductWebsite],
                        'cpw.product_id = e.entity_id'
                    )->where(
                        'cpw.website_id = '.$helper->getWebsiteId()
                    );
                    $sellerProductColl->getSelect()
                    ->join(
                        ['mpro' => $marketplaceProduct],
                        'mpro.mageproduct_id = e.entity_id',
                        [
                            'seller_id' => 'seller_id',
                            'mageproduct_id' => 'mageproduct_id'
                        ]
                    )->where(
                        'mpro.seller_id = '.$sellerId
                    );
                    $sellerProductColl->getSelect()->limit(3);
                    foreach ($sellerProductColl as $value) {
                        if ($sellerProCount < 3) {
                            array_push(
                                $sellerArr[$value['seller_id']],
                                $value['entity_id']
                            );
                            ++$sellerProCount;
                        }
                    }
                }
            }
        }
        if (count($sellerArr) != 4) {
            $i = count($sellerArr);
            $countProArr = [];
            $sellerProductColl = $this->productCollectionFactory->create()
            ->addFieldToFilter(
                'status',
                ['eq' => 1]
            )->addFieldToFilter(
                'visibility',
                ['eq' => 4]
            );
            $sellerProductColl->getSelect()
            ->join(
                ['cpw' => $catalogProductWebsite],
                'cpw.product_id = e.entity_id'
            )->where(
                'cpw.website_id = '.$helper->getWebsiteId()
            );
            $sellerProductColl->getSelect()
            ->join(
                ['mpro' => $marketplaceProduct],
                'mpro.mageproduct_id = e.entity_id',
                [
                    'seller_id' => 'seller_id',
                    'mageproduct_id' => 'mageproduct_id'
                ]
            );
            if (count($sellerArr)) {
                $sellerProductColl->getSelect()->join(
                    ['mmu' => $marketplaceUserdata],
                    'mmu.seller_id = mpro.seller_id',
                    ['is_seller' => 'is_seller']
                )->where(
                    'mmu.is_seller = 1
                    AND mmu.seller_id NOT IN ('.implode(',', array_keys($sellerArr)).')'
                );
            } else {
                $sellerProductColl->getSelect()->join(
                    ['mmu' => $marketplaceUserdata],
                    'mmu.seller_id = mpro.seller_id',
                    ['is_seller' => 'is_seller']
                )->where(
                    'mmu.is_seller = 1'
                );

                if (isset($paramData['zipcode']) && $paramData['zipcode']) {
                    // var_dump($paramData['zipcode']);exit;
                    $pos1 = strpos($this->getStatus($paramData['zipcode']), 'error_code":400');
                    $pos2 = strpos($this->getStatus($paramData['zipcode']), 'error_code":404');
                    $pos3 = strpos($this->getStatus($paramData['zipcode']), 'error_code":401');
                    $pos4 = strpos($this->getStatus($paramData['zipcode']), 'error_code":429');

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
                            $zip_codes = $this->getStatus($paramData['zipcode']);
                            $zip_codes = str_replace('"', '', $zip_codes);
                            $zip_codes = str_replace('{zip_codes:[', '', $zip_codes);
                            $zip_codes = str_replace(']}', '', $zip_codes);
                            $zip = explode(',', $zip_codes);
                            $sellerProductColl->getSelect()
                            ->where(
                                'mmu.zip_code_stylist IN ('.implode(',',$zip).')'
                            );
                        }
                }

                if (isset($paramData['expgeneral']) && $paramData['expgeneral']) {

                    $expgeneral = explode(',', $paramData['expgeneral']);
                    in_array(1, $expgeneral)? $a=1:$a=0;
                    in_array(2, $expgeneral)? $b=2:$b=0;
                    in_array(3, $expgeneral)? $c=3:$c=0;
                    in_array(4, $expgeneral)? $d=4:$d=0;
                    in_array(5, $expgeneral)? $e=5:$e=0;

                    in_array(6, $expgeneral)? $f=6:$f=0;
                    in_array(7, $expgeneral)? $g=7:$g=0;
                    in_array(8, $expgeneral)? $h=8:$h=0;
                    in_array(9, $expgeneral)? $i=9:$i=0;
                    in_array(10, $expgeneral)? $j=10:$j=0;

                    in_array(11, $expgeneral)? $k=11:$k=0;
                    in_array(12, $expgeneral)? $l=12:$l=0;
                    in_array(13, $expgeneral)? $m=13:$m=0;
                    in_array(14, $expgeneral)? $n=14:$n=0;
                    in_array(15, $expgeneral)? $o=15:$o=0;

                    in_array(16, $expgeneral)? $p=16:$p=0;
                    in_array(17, $expgeneral)? $q=17:$q=0;
                    in_array(18, $expgeneral)? $r=18:$r=0;
                    in_array(19, $expgeneral)? $s=19:$s=0;
                    in_array(20, $expgeneral)? $t=20:$t=0;

                    $sellerProductColl->getSelect()
                    ->where(
                             'mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$a.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$b.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$c.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$d.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$e.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$f.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$g.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$h.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$i.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$j.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$k.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$l.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$m.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$n.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$o.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$p.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$q.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$r.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$s.'".*\''
                        . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$t.'".*\''
                    );
                }


                if (isset($paramData['expwork']) && $paramData['expwork']) {
                        $expwork = explode(',', $paramData['expwork']);

                        in_array(1, $expwork)? $a=1:$a=0;
                        in_array(2, $expwork)? $b=2:$b=0;
                        in_array(3, $expwork)? $c=3:$c=0;
                        in_array(4, $expwork)? $d=4:$d=0;
                        in_array(5, $expwork)? $e=5:$e=0;

                        in_array(6, $expwork)? $f=6:$f=0;
                        in_array(7, $expwork)? $g=7:$g=0;
                        in_array(8, $expwork)? $h=8:$h=0;
                        in_array(9, $expwork)? $i=9:$i=0;
                        in_array(10, $expwork)? $j=10:$j=0;

                        $sellerProductColl->getSelect()
                        ->where(
                                 'mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$a.'".*\''
                            . 'OR mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$b.'".*\''
                            . 'OR mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$c.'".*\''
                            . 'OR mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$d.'".*\''
                            . 'OR mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$e.'".*\''
                            . 'OR mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$f.'".*\''
                            . 'OR mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$g.'".*\''
                            . 'OR mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$h.'".*\''
                            . 'OR mmu.	specialties_work REGEXP \'.*;s:[0-9]+:"'.$i.'".*\''
                            . 'OR mmu.specialties_general REGEXP \'.*;s:[0-9]+:"'.$j.'".*\''
                        );
                }



            }



            if ($helper->getCustomerSharePerWebsite()) {
                $sellerProductColl->getSelect()->join(
                    $joinTable.' as cgf',
                    'mpro.seller_id = cgf.entity_id AND cgf.website_id= '.$websiteId
                );
            } else {
                $sellerProductColl->getSelect()->join(
                    $joinTable.' as cgf',
                    'mpro.seller_id = cgf.entity_id'
                );
            }

            $sellerProductColl->getSelect()
                             ->columns('COUNT(*) as countOrder')
                             ->group('seller_id');
            foreach ($sellerProductColl as $value) {
                if (!isset($countProArr[$value['seller_id']])) {
                    $countProArr[$value['seller_id']] = [];
                }
                $countProArr[$value['seller_id']] = $value['countOrder'];
            }

            arsort($countProArr);

            foreach ($countProArr as $procountSellerId => $procount) {
                if ($i <= 4) {
                    if ($sellerHelperProCount = $helper->getSellerProCount($procountSellerId)) {

                        array_push($sellerIdsArr, $procountSellerId);

                        if (!isset($sellerArr[$procountSellerId])) {
                            $sellerArr[$procountSellerId] = [];
                        }
                        $sellerProductColl = $this->productCollectionFactory->create()
                        ->addFieldToFilter(
                            'status',
                            ['eq' => 1]
                        )->addFieldToFilter(
                            'visibility',
                            ['eq' => 4]
                        );

                        $sellerProductColl->getSelect()
                        ->join(
                            ['cpw' => $catalogProductWebsite],
                            'cpw.product_id = e.entity_id'
                        )->where(
                            'cpw.website_id = '.$helper->getWebsiteId()
                        );
                        $sellerProductColl->getSelect()
                        ->join(
                            ['mpro' => $marketplaceProduct],
                            'mpro.mageproduct_id = e.entity_id',
                            [
                                'seller_id' => 'seller_id',
                                'mageproduct_id' => 'mageproduct_id'
                            ]
                        )->where(
                            'mpro.seller_id = '.$procountSellerId
                        );





                        $sellerProductColl->getSelect()->limit(3);
                        foreach ($sellerProductColl as $value) {
                            array_push($sellerArr[$procountSellerId], $value['mageproduct_id']);
                        }


                    }
                }
                ++$i;
            }
        }
        $sellerProfileArr =  [];
        foreach ($sellerIdsArr as $sellerId) {
            $sellerData = $helper->getSellerCollectionObj($sellerId);
            foreach ($sellerData as $sellerDataResult) {
                $sellerId = $sellerDataResult->getSellerId();
                $sellerProfileArr[$sellerId] = [];
                $profileurl = $sellerDataResult->getShopUrl();
                $shoptitle = $sellerDataResult->getShopTitle();
                $description = $sellerDataResult->getCompanyDescription();
                $company_locality = $sellerDataResult->getCompanyLocality();
                $experience = $sellerDataResult->getExperience();
                $title_stylist = $sellerDataResult->getTitleStylist();
                $specialties_general = $sellerDataResult->getSpecialtiesGeneral();
                $specialties_work = $sellerDataResult->getSpecialtiesWork();
                $specialties_social = $sellerDataResult->getSpecialtiesSocial();
                $logo = $sellerDataResult->getLogoPic()??"noimage.png";
                array_push(
                    $sellerProfileArr[$sellerId],
                    [
                        'profileurl'=>$profileurl,
                        'shoptitle'=>$shoptitle,
                        'logo'=>$logo,
                        'description'=>$description,
                        'experience'=>$experience,
                        'company_locality'=>$company_locality,
                        'title_stylist'=>$title_stylist,
                        'specialties_general'=>$specialties_general,
                        'specialties_work'=>$specialties_work,
                        'specialties_social'=>$specialties_social
                    ]
                );
            }
        }

        // return [$sellerArr, $sellerProfileArr, $sellerCountArr];
        return [$sellerArr, $sellerProfileArr];
    }

    public function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }
}
