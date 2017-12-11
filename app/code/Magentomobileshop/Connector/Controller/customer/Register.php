<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\customer;

class Register extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->customerSession   = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory   = $customerFactory;
        $this->storeManager      = $storeManager;
        $this->customHelper      = $customHelper;
        $this->logger            = $logger;
        $this->request           = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request           = $requestInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $result         = $this->resultJsonFactory->create();
        if (!$this->customerSession->isLoggedIn()) {
            $params = $this->request->getParams();

            if ((null == $params['password']) || (null == $params['email'])) {
                return $result->setData(['status' => false, 'message' => 'empty password or email.']);
            }
            try {
                $customer = $this->customerFactory->create();
                $customer->setPassword($params['password']);
                $customer->setConfirmation($this->request->getPost('password-confirmation', $params['password']));
                $customer->setFirstname($params['firstname']);
                $customer->setLastname($params['lastname']);
                $customer->setDefaultMobileNumber($params['default_mobile_number']);
                $customer->setEmail($params['email']);
                $customer->setPassword($params['password']);
                $customer->save();
                $customer->sendNewAccountEmail('registered', '', $this->storeManager->getStore()->getId());

                return $result->setData(['status' => true, 'message' => 'Your account is activated successfully']);
            } catch (\Exception $e) {
                return $result->setData(['status' => false, 'message' => $e->getMessage()]);
            }
        } else {
            return $result->setData(['status' => false, 'message' => 'Already Logged in.Please verify once']);
        }
    }
}
