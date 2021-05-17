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

namespace Webkul\MpAdvancedBookingSystem\Block;

use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory as InfoCollection;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\CollectionFactory as QuestionCollection;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory as AnswerCollection;

/**
 * Webkul MpAdvancedBookingSystem Hotelbooking Block
 */
class Hotelbooking extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollection
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param InfoCollection $infoCollection
     * @param QuestionCollection $questionCollection
     * @param AnswerCollection $answerCollection
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollection,
        \Magento\Catalog\Model\ProductFactory $product,
        InfoCollection $infoCollection,
        QuestionCollection $questionCollection,
        AnswerCollection $answerCollection,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->imageHelper = $context->getImageHelper();
        $this->mpHelper = $mpHelper;
        $this->eavAttribute = $eavAttribute;
        $this->mpProductCollection = $mpProductCollection;
        $this->product = $product;
        $this->infoCollection = $infoCollection;
        $this->questionCollection = $questionCollection;
        $this->answerCollection = $answerCollection;
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
    }

    /**
     * _construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Questions List'));
    }

    /**
     * getAdminProductCollection
     *
     * @param int $customerId
     * @param string $filterName
     * @return array
     */
    protected function getAdminProductCollection($customerId, $filterName)
    {
        $adminProductIDs = [];
        try {
            $catalogProductEntityVarchar = $this->mpProductCollection->create()->getTable(
                'catalog_product_entity_varchar'
            );
            $catalogProductEntityInt = $this->mpProductCollection->create()->getTable(
                'catalog_product_entity_int'
            );
            $catalogProductEntity = $this->mpProductCollection->create()->getTable(
                'catalog_product_entity'
            );
            $proAttId = $this->eavAttribute->getIdByCode('catalog_product', 'name');
            $proStatusAttId = $this->eavAttribute->getIdByCode('catalog_product', 'status');

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
                cpev.value like "%'.$filterName.'%" AND 
                cpev.attribute_id = '.$proAttId
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = 0 AND 
                cpei.attribute_id = '.$proStatusAttId
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            $adminStoreCollection->getSelect()->group('mageproduct_id');

            $adminProductIDs = $adminStoreCollection->getAllIds();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Hotelbooking_Questions_getAdminProductCollection Exception : ".$e->getMessage()
            );
        }
        return $adminProductIDs;
    }

    /**
     * getSellerProductCollection
     *
     * @param int $customerId
     * @param string $filterName
     * @return array
     */
    protected function getSellerProductCollection($customerId, $filterName)
    {
        $storeProductIDs = [];
        try {
            $storeId = $this->mpHelper->getCurrentStoreId();
            $catalogProductEntityVarchar = $this->mpProductCollection->create()->getTable(
                'catalog_product_entity_varchar'
            );
            $catalogProductEntityInt = $this->mpProductCollection->create()->getTable(
                'catalog_product_entity_int'
            );
            $catalogProductEntity = $this->mpProductCollection->create()->getTable(
                'catalog_product_entity'
            );
            $proAttId = $this->eavAttribute->getIdByCode('catalog_product', 'name');
            $proStatusAttId = $this->eavAttribute->getIdByCode('catalog_product', 'status');

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
                cpev.value like "%'.$filterName.'%" AND 
                cpev.attribute_id = '.$proAttId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = '.$storeId.' AND 
                cpei.attribute_id = '.$proStatusAttId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            $storeCollection->getSelect()->group('mageproduct_id');

            $storeProductIDs = $storeCollection->getAllIds();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Hotelbooking_Questions_getSellerProductCollection Exception : ".$e->getMessage()
            );
        }
        return $storeProductIDs;
    }

    /**
     * getFilterParams
     *
     * @return array
     */
    protected function getFilterParams()
    {
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
        return [
            'filter' => $filter,
            'filterStatus' => $filterStatus,
            'from' => $from,
            'to' => $to
        ];
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
        return $this->imageHelper;
    }
}
