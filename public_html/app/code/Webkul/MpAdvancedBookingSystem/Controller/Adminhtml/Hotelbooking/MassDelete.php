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
namespace Webkul\MpAdvancedBookingSystem\Controller\Adminhtml\Hotelbooking;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter
     *
     * @var MassActionFilter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param MassActionFilter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        MassActionFilter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $records = $collection->getSize();
        $collection->walk('delete');

        $this->messageManager->addSuccess(__(
            'A total of %1 record(s) have been deleted.',
            $records
        ));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('mpadvancebooking/hotelbooking/questions');
    }
}
