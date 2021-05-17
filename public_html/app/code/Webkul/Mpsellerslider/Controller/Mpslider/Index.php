<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsellerslider
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpsellerslider\Controller\Mpslider;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Mpsellerslider Mpslider controller
 */
class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @var \Webkul\Mpsellerslider\Helper\Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Mpsellerslider\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Mpsellerslider\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param  RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Manage seller slider page on customer account.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $isPartner = $this->mpHelper->isSeller();
            if ($isPartner == 1) {
                if ($this->helper->isModuleEnable()) {
                    /**
                     * @var \Magento\Framework\View\Result\Page $resultPage
                     */
                    $resultPage = $this->resultPageFactory->create();
                    if ($this->mpHelper->getIsSeparatePanel()) {
                        $resultPage->addHandle('mpsellerslider_layout2_mpslider_index');
                    }
                    $resultPage->getConfig()->getTitle()->set(__('Add Images For Slider'));
                    return $resultPage;
                } else {
                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/account/dashboard',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Mpslider_Index execute : ".$e->getMessage());
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/dashboard',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
