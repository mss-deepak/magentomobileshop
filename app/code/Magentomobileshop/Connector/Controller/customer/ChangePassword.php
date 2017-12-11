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

class ChangePassword extends \Magento\Customer\Controller\AbstractAccount
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->customerSession = $customerSession;
        $this->customHelper    = $customHelper;
        $this->jsonHelper      = $jsonHelper;
        $this->customerFactory = $customerFactory;
        $this->customerModel   = $customerModel;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request           = $requestInterface;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $result         = $this->resultJsonFactory->create();
        $validate       = 0;
        $result         = '';
        if ($this->customerSession->isLoggedIn()) {
            $customer    = $this->customerSession->getCustomer();
            $customerid  = $customer->getEntityId();
            $oldpassword = $this->request->getParam('oldpassword');
            $newpassword = $this->request->getParam('newpassword');
            $username    = $customer->getEmail();
            try {
                $login_customer_result = $this->customerModel->setWebsiteId('1')->authenticate($username, $oldpassword);
                $validate              = 1;
            } catch (\Exception $ex) {
                $validate = 0;
            }
            if ($validate == 1) {
                try {
                    $customer = $this->customerModel->load($customerid);
                    $customer->setPassword($newpassword);
                    $customer->save();
                    $result->setData(['status' => 'error', 'message' => __('Your Password has been Changed Successfully')]);
                    return $result;
                } catch (\Exception $ex) {
                    $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                    return $result;
                }
            } else {
                $result->setData(['status' => 'error', 'message' => __('Incorrect Old Password.')]);
                return $result;
            }
        } else {
            $result->setData(['status' => 'error', 'message' => __('Kindly Signin first.')]);
            return $result;
        }
    }
}
