<?php

namespace RockLab\Form\Block;

use Magento\Framework\View\Element\Template;
use RockLab\Form\Model\ResourceModel\Form\CollectionFactory as SellerCollectionFactory;

class Sellers extends \Magento\Framework\View\Element\Template
{
    protected $resource;

    protected $sellerCollectionFactory;

    protected $helper;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        SellerCollectionFactory $sellerCollectionFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        array $data = []
    ) {
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->resource = $resource;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getSellers()
    {
        $marketplaceUserdata = $this->resource->getTableName('marketplace_userdata');

        $collection = $this->sellerCollectionFactory->create();
        $collection->getSelect()->join(
            ['mu'=> $marketplaceUserdata],
            'main_table.name = mu.shop_title',
            ['shop_title','logo_pic','shop_url']
        );

        return $collection;
    }
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

}
