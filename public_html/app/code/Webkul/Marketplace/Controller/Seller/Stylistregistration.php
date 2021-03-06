<?php
declare(strict_types=1);

namespace Webkul\Marketplace\Controller\Seller;


use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\SellerFactory as MpSellerFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Webkul\Marketplace\Helper\Email as MpEmailHelper;

class Stylistregistration extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $_date;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Magento\Framework\Message\ManagerInterface
	 */
	protected $_messageManager;

	/**
	 * @var MpHelper
	 */
	protected $mpHelper;

	/**
	 * @var MpSellerFactory
	 */
	protected $mpSellerFactory;

	/**
	 * @var UrlRewriteFactory
	 */
	protected $urlRewriteFactory;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $customerSession;

	/**
	 * @var \Magento\Framework\UrlInterface
	 */
	protected $urlInterface;

	/**
	 * @var MpEmailHelper
	 */
	protected $mpEmailHelper;

	/**
	 * @var \Magento\Backend\Model\Url
	 */
	protected $urlBackendModel;

	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $customerFactory;

	protected $resultFactory;

	/**
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
	 * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
	 * @param \Magento\Framework\Message\ManagerInterface $messageManager
	 * @param MpHelper                                    $mpHelper
	 * @param MpSellerFactory                             $mpSellerFactory
	 * @param UrlRewriteFactory                           $urlRewriteFactory
	 * @param \Magento\Customer\Model\Session             $customerSession
	 * @param \Magento\Framework\UrlInterface             $urlInterface
	 * @param MpEmailHelper                               $mpEmailHelper
	 * @param \Magento\Backend\Model\Url                  $urlBackendModel
	 * @param \Magento\Customer\Model\CustomerFactory     $customerFactory
	 */

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        MpHelper $mpHelper,
        MpSellerFactory $mpSellerFactory,
        UrlRewriteFactory $urlRewriteFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlInterface,
        MpEmailHelper $mpEmailHelper,
        \Magento\Backend\Model\Url $urlBackendModel,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Customer\Model\CustomerFactory $customerFactory
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_date = $date;
        $this->mpHelper = $mpHelper;
        $this->mpSellerFactory = $mpSellerFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
        $this->mpEmailHelper = $mpEmailHelper;
        $this->urlBackendModel = $urlBackendModel;
		$this->resultFactory = $resultFactory;
		$this->customerFactory = $customerFactory;
		return parent::__construct($context);
	}

	public function execute()
	{

		$post = (array) $this->getRequest()->getPost();

		if (!empty($post)) {
			// Retrieve your form data
			$firstName		= $post['firstname'];
			$lastName		= $post['lastname'];
			$email			= $post['email'];
			// $profileurl		= $post['profileurl'];
			$address1		= $post['address1'];
			$address2		= $post['address2'];
			$city			= $post['city'];
			$state			= $post['state_stylist'];
			$zip			= $post['zip'];
			$phone			= $post['phone'];
			$title_stylist 	= $post['title_stylist'];
			$experience 	= isset($post['experience'])?$post['experience']:'0';
			$password		= $post['password'];


			$state = explode('_', $post['state_stylist']);
			$stateValue = $state[0];
			$stateLabel = $state[1];


			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			$websiteId = $this->_storeManager->getStore()->getWebsiteId();
			$customerFactor = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
			$customer = $customerFactor->setWebsiteId($websiteId)->loadByEmail($email);
					try {
						if (!$customer->getId()) {
							$profileurl = str_replace(' ', '', strtolower($firstName.$lastName));
				            if (!empty($profileurl)) {

				                $profileurlcount = $this->mpSellerFactory->create()->getCollection();
				                $profileurlcount->addFieldToFilter(
				                    'shop_url',
				                    $profileurl
				                );

				                if (!$profileurlcount->getSize()) {
									// create customer
									$customer = $this->customerFactory->create();
									$customer->setWebsiteId($websiteId);
									$customer->setEmail($email);
									$customer->setFirstname($firstName);
									$customer->setLastname($lastName);
									$customer->setPassword($password);
									// save customer
									$customer->save();
									$customer->setConfirmation(null);
									$customer->save();

									// create stylist
									$shop_title = $firstName.' '.$lastName;
									$street = $address1.' '.$address2;
									$company_locality = $city.', '.$stateLabel;
				                    $partnerApprovalStatus = $this->mpHelper->getIsPartnerApproval();
				                    $status = $partnerApprovalStatus ? 0 : 1;
				                    $customerid = $customer->getId();
				                    $model = $this->mpSellerFactory->create();
				                    $model->setData('is_seller', $status);
				                    $model->setData('shop_url', $profileurl);
				                    $model->setData('seller_id', $customerid);
				                    $model->setData('store_id', 0);
									$model->setData('shop_title',$shop_title);
									$model->setData('state_stylist',$stateValue);

									if (array_key_exists('zip', $post)) {
										$zip = preg_replace("/<script.*?\/script>/s", "", $zip) ? : $zip;
				                        $model->setData('zip_code_stylist',$zip);
				                    }

									// if (array_key_exists('state', $post)) {
									// 	$state = preg_replace("/<script.*?\/script>/s", "", $state) ? : $state;
				                    //     $model->setData('state_stylist',$state);
				                    // }

									if ($street) {
										$street = preg_replace("/<script.*?\/script>/s", "", $street) ? : $street;
				                        $model->setData('street_stylist',$street);
				                    }

									if (array_key_exists('city', $post)) {
										$city = preg_replace("/<script.*?\/script>/s", "", $city) ? : $city;
				                        $model->setData('city_stylist',$city);
				                    }

									if (array_key_exists('phone', $post)) {
										$phone = preg_replace("/<script.*?\/script>/s", "", $phone) ? : $phone;
				                        $model->setData('contact_number',$phone);
				                    }

									if ($company_locality) {
										$company_locality = preg_replace("/<script.*?\/script>/s", "", $company_locality) ? : $company_locality;
				                        $model->setData('company_locality',$company_locality);
				                    }

									// $model->setData('zip_code_stylist',$zip);
									// $model->setData('street_stylist',$street);
									// $model->setData('city_stylist',$city);
									// $model->setData('contact_number',$phone);
									// $model->setData('company_locality',$company_locality);

									$model->setData('title_stylist',$title_stylist);
									$model->setData('experience',$experience);


									if (array_key_exists('certification', $post)) {
				                        $certificationArray = array_filter($post['certification']);
				                        $certification = serialize($certificationArray);
				                        $model->setData('certification',$certification);
				                    }

									if (array_key_exists('specialties_general', $post)) {
				                        $specialties_generalArray = array_filter($post['specialties_general']);
				                        $specialties_general = serialize($specialties_generalArray);
				                        $model->setData('specialties_general',$specialties_general);
				                    }

									if (array_key_exists('specialties_work', $post)) {
										$specialties_workArray = array_filter($post['specialties_work']);
				                        $specialties_work = serialize($specialties_workArray);
				                        $model->setData('specialties_work',$specialties_work);
				                    }

									if (array_key_exists('specialties_social', $post)) {
										$specialties_socialArray = array_filter($post['specialties_social']);
				                        $specialties_social = serialize($specialties_socialArray);
				                        $model->setData('specialties_social',$specialties_social);
				                    }

				                    $model->setCreatedAt($this->_date->gmtDate());
				                    $model->setUpdatedAt($this->_date->gmtDate());

				                    if ($status == 0) {
				                        $model->setAdminNotification(1);
				                    }
				                    $model->save();

				                    $loginUrl = $this->urlInterface->getUrl("marketplace/account/dashboard");
				                    $this->customerSession->setBeforeAuthUrl($loginUrl);
				                    $this->customerSession->setAfterAuthUrl($loginUrl);

				                    $helper = $this->mpHelper;
				                    if ($helper->getAutomaticUrlRewrite()) {
				                        $this->createSellerPublicUrls($profileurl);
				                    }

				                    if ($partnerApprovalStatus) {
				                        $adminStoremail = $helper->getAdminEmailId();
				                        $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
				                        $adminUsername = $helper->getAdminName();
				                        $senderInfo = [
				                            'name' => $customer->getFirstName().' '.$customer->getLastName(),
				                            'email' => $customer->getEmail(),
				                        ];
				                        $receiverInfo = [
				                            'name' => $adminUsername,
				                            'email' => $adminEmail,
				                        ];
				                        $emailTemplateVariables['myvar1'] = $customer->getFirstName().' '.
				                        $customer->getLastName();
				                        $emailTemplateVariables['myvar2'] = $this->urlBackendModel->getUrl(
				                            'customer/index/edit',
				                            ['id' => $customer->getId()]
				                        );
				                        $emailTemplateVariables['myvar3'] = $helper->getAdminName();

				                        $this->mpEmailHelper->sendNewSellerRequest(
				                            $emailTemplateVariables,
				                            $senderInfo,
				                            $receiverInfo
				                        );
				                    }
									// Create customer session
									$this->customerSession->setCustomerAsLoggedIn($customer);

									$resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
									$resultRedirect->setUrl('/stylist/account/editprofile/');

									$this->_messageManager->addSuccessMessage('Thank you for registering!');

				                } else {

									$profileurlFirstSection = str_replace(' ', '', strtolower($firstName.$lastName));
									$profileurlSectSection = rand(10, 99);
									$profileurl = $profileurlFirstSection.$profileurlSectSection;

									// create customer
									$customer = $this->customerFactory->create();
									$customer->setWebsiteId($websiteId);
									$customer->setEmail($email);
									$customer->setFirstname($firstName);
									$customer->setLastname($lastName);
									$customer->setPassword($password);
									// save customer
									$customer->save();
									$customer->setConfirmation(null);
									$customer->save();

									// create stylist
									$shop_title = $firstName.' '.$lastName;
									$street = $address1.' '.$address2;
									$company_locality = $city.', '.$stateLabel;
				                    $partnerApprovalStatus = $this->mpHelper->getIsPartnerApproval();
				                    $status = $partnerApprovalStatus ? 0 : 1;
				                    $customerid = $customer->getId();
				                    $model = $this->mpSellerFactory->create();
				                    $model->setData('is_seller', $status);
				                    $model->setData('shop_url', $profileurl);
				                    $model->setData('seller_id', $customerid);
				                    $model->setData('store_id', 0);
									$model->setData('shop_title',$shop_title);
									$model->setData('state_stylist',$stateValue);

									if (array_key_exists('zip', $post)) {
										$zip = preg_replace("/<script.*?\/script>/s", "", $zip) ? : $zip;
				                        $model->setData('zip_code_stylist',$zip);
				                    }

									// if (array_key_exists('state', $post)) {
									// 	$state = preg_replace("/<script.*?\/script>/s", "", $state) ? : $state;
				                    //     $model->setData('state_stylist',$state);
				                    // }

									if ($street) {
										$street = preg_replace("/<script.*?\/script>/s", "", $street) ? : $street;
				                        $model->setData('street_stylist',$street);
				                    }

									if (array_key_exists('city', $post)) {
										$city = preg_replace("/<script.*?\/script>/s", "", $city) ? : $city;
				                        $model->setData('city_stylist',$city);
				                    }

									if (array_key_exists('phone', $post)) {
										$phone = preg_replace("/<script.*?\/script>/s", "", $phone) ? : $phone;
				                        $model->setData('contact_number',$phone);
				                    }

									if ($company_locality) {
										$company_locality = preg_replace("/<script.*?\/script>/s", "", $company_locality) ? : $company_locality;
				                        $model->setData('company_locality',$company_locality);
				                    }

									// $model->setData('zip_code_stylist',$zip);
									// $model->setData('street_stylist',$street);
									// $model->setData('city_stylist',$city);
									// $model->setData('contact_number',$phone);
									// $model->setData('company_locality',$company_locality);

									$model->setData('title_stylist',$title_stylist);
									$model->setData('experience',$experience);


									if (array_key_exists('certification', $post)) {
				                        $certificationArray = array_filter($post['certification']);
				                        $certification = serialize($certificationArray);
				                        $model->setData('certification',$certification);
				                    }

									if (array_key_exists('specialties_general', $post)) {
				                        $specialties_generalArray = array_filter($post['specialties_general']);
				                        $specialties_general = serialize($specialties_generalArray);
				                        $model->setData('specialties_general',$specialties_general);
				                    }

									if (array_key_exists('specialties_work', $post)) {
										$specialties_workArray = array_filter($post['specialties_work']);
				                        $specialties_work = serialize($specialties_workArray);
				                        $model->setData('specialties_work',$specialties_work);
				                    }

									if (array_key_exists('specialties_social', $post)) {
										$specialties_socialArray = array_filter($post['specialties_social']);
				                        $specialties_social = serialize($specialties_socialArray);
				                        $model->setData('specialties_social',$specialties_social);
				                    }

				                    $model->setCreatedAt($this->_date->gmtDate());
				                    $model->setUpdatedAt($this->_date->gmtDate());

				                    if ($status == 0) {
				                        $model->setAdminNotification(1);
				                    }
				                    $model->save();

				                    $loginUrl = $this->urlInterface->getUrl("marketplace/account/dashboard");
				                    $this->customerSession->setBeforeAuthUrl($loginUrl);
				                    $this->customerSession->setAfterAuthUrl($loginUrl);

				                    $helper = $this->mpHelper;
				                    if ($helper->getAutomaticUrlRewrite()) {
				                        $this->createSellerPublicUrls($profileurl);
				                    }

				                    if ($partnerApprovalStatus) {
				                        $adminStoremail = $helper->getAdminEmailId();
				                        $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
				                        $adminUsername = $helper->getAdminName();
				                        $senderInfo = [
				                            'name' => $customer->getFirstName().' '.$customer->getLastName(),
				                            'email' => $customer->getEmail(),
				                        ];
				                        $receiverInfo = [
				                            'name' => $adminUsername,
				                            'email' => $adminEmail,
				                        ];
				                        $emailTemplateVariables['myvar1'] = $customer->getFirstName().' '.
				                        $customer->getLastName();
				                        $emailTemplateVariables['myvar2'] = $this->urlBackendModel->getUrl(
				                            'customer/index/edit',
				                            ['id' => $customer->getId()]
				                        );
				                        $emailTemplateVariables['myvar3'] = $helper->getAdminName();

				                        $this->mpEmailHelper->sendNewSellerRequest(
				                            $emailTemplateVariables,
				                            $senderInfo,
				                            $receiverInfo
				                        );
				                    }
									// Create customer session
									$this->customerSession->setCustomerAsLoggedIn($customer);

									$resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
									$resultRedirect->setUrl('/stylist/account/editprofile/');

									$this->_messageManager->addSuccessMessage('Thank you for registering!');

									// $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
									// $resultRedirect->setUrl('/stylist/seller/stylistregistration/');
									//
				                    // $this->_messageManager->addError(
				                    //     __('This Shop URL already Exists.')
				                    // );
				                }
							}

						} else {

							$resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
							$resultRedirect->setUrl('/stylist/seller/stylistregistration/');

							$this->_messageManager->addError(
								__('This email '.$email.' already exists.')
							);
						}

			        } catch (\Exception $e) {
			            $this->mpHelper->logDataInLogger(
			                "Observer_CustomerRegisterSuccessObserver execute : ".$e->getMessage()
			            );
			            $this->_messageManager->addError($e->getMessage());
			        }
			// $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
			// $resultRedirect->setUrl('/stylist/seller/stylistregistration/');
			return $resultRedirect;
		}

		return $this->_pageFactory->create();

	}

	private function createSellerPublicUrls($profileurl = '')
    {
        if ($profileurl) {
            $getCurrentStoreId = $this->mpHelper->getCurrentStoreId();

            /*
            * Set Seller Profile Url
            */
            $sourceProfileUrl = 'stylist/seller/profile/'.$profileurl;
            $requestProfileUrl = 'stylist/profile/'.$profileurl;
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $profileRequestUrl = '';
            $urlCollectionData = $this->urlRewriteFactory->create()
                              ->getCollection()
                              ->addFieldToFilter('target_path', $sourceProfileUrl)
                              ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $profileRequestUrl = $value->getRequestPath();
            }
            if ($profileRequestUrl != $requestProfileUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceProfileUrl)
                ->setRequestPath($requestProfileUrl)
                ->save();
            }

            /*
            * Set Seller Collection Url
            */
            $sourceCollectionUrl = 'stylist/seller/collection/'.$profileurl;
            $requestCollectionUrl = 'stylist/profile/'.$profileurl.'/collection';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $collectionRequestUrl = '';
            $urlCollectionData = $this->urlRewriteFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('target_path', $sourceCollectionUrl)
                                ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $collectionRequestUrl = $value->getRequestPath();
            }
            if ($collectionRequestUrl != $requestCollectionUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceCollectionUrl)
                ->setRequestPath($requestCollectionUrl)
                ->save();
            }

            /*
            * Set Seller Feedback Url
            */
            $sourceFeedbackUrl = 'stylist/seller/feedback/'.$profileurl;

            $requestFeedbackUrl = 'stylist/profile/'.$profileurl.'/feedback';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $feedbackRequestUrl = '';
            $urlFeedbackData = $this->urlRewriteFactory->create()
                              ->getCollection()
                              ->addFieldToFilter('target_path', $sourceFeedbackUrl)
                              ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlFeedbackData as $value) {
                $urlId = $value->getId();
                $feedbackRequestUrl = $value->getRequestPath();
            }
            if ($feedbackRequestUrl != $requestFeedbackUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceFeedbackUrl)
                ->setRequestPath($requestFeedbackUrl)
                ->save();
            }

            /*
            * Set Seller Location Url
            */
            $sourceLocationUrl = 'stylist/seller/location/'.$profileurl;
            $requestLocationUrl = 'stylist/profile/'.$profileurl.'/location';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $locationRequestUrl = '';
            $urlLocationData = $this->urlRewriteFactory->create()
                              ->getCollection()
                              ->addFieldToFilter('target_path', $sourceLocationUrl)
                              ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlLocationData as $value) {
                $urlId = $value->getId();
                $locationRequestUrl = $value->getRequestPath();
            }
            if ($locationRequestUrl != $requestLocationUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceLocationUrl)
                ->setRequestPath($requestLocationUrl)
                ->save();
            }

            /**
             * Set Seller Policy Url
             */
            $sourcePolicyUrl = 'stylist/seller/policy/'.$profileurl;
            $requestPolicyUrl = 'stylist/profile/'.$profileurl.'/policy';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $policyRequestUrl = '';
            $urlPolicyData = $this->urlRewriteFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('target_path', $sourcePolicyUrl)
                            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlPolicyData as $value) {
                $urlId = $value->getId();
                $policyRequestUrl = $value->getRequestPath();
            }
            if ($policyRequestUrl != $requestPolicyUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourcePolicyUrl)
                ->setRequestPath($requestPolicyUrl)
                ->save();
            }
        }
    }


}
