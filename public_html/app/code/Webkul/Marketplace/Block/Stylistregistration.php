<?php

namespace Webkul\Marketplace\Block;

class Stylistregistration extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

	public function __construct(
		\Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
		\Magento\Framework\View\Element\Template\Context $context
		)
	{
		$this->_regionCollectionFactory = $regionCollectionFactory;
		parent::__construct($context);
	}


	public function getFormAction()
	{
	       // companymodule is given in routes.xml
	       // controller_name is folder name inside controller folder
	       // action is php file name inside above controller_name folder

	   return '/stylist/seller/stylistregistration';
	}

	public function getRegionCollection()
    {
        $collection = $this->_regionCollectionFactory->create();
        return $collection;
    }

    public function getRegionOptionArray()
    {
        return $options = $this->getRegionCollection()
                // ->addCountryFilter('CA')
                ->addCountryFilter('US')
                ->toOptionArray();
    }
}
