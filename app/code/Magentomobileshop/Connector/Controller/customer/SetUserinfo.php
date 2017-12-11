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

class SetUserinfo extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Customer\Model\AddressFactory $addressFactory
    ) {
        $this->customerSession   = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory   = $customerFactory;
        $this->storeManager      = $storeManager;
        $this->customHelper      = $customHelper;
        $this->addressFactory    = $addressFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $this->getuserinfoApi();
    }

    protected function getuserinfoApi()
    {

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerFactory->create();
            $customer->load($this->customerSession->getId());
            $data = $this->getRequest()->getParam('data');

            $customer_info = json_decode($data, true);

            if (isset($customer_info)) {
                $customer->setFirstname($customer_info['firstname']);
                $customer->setLastname($customer_info['lastname']);

                $address = $customer->getPrimaryBillingAddress();

                if (!$address) {
                    $address = $this->addressFactory->create();
                    $address->setCustomerId($customer->getId());
                    $address->setIsDefaultBilling(true);
                }
                try {
                    $customer->save();
                    $result->setData(['status' => 'error', 'message' => __('Data Updated successfully')]);
                    return $result;
                } catch (\Exception $e) {
                    $result->setData(['status' => 'error', 'message' => __('Data Not Updated')]);
                    return $result;
                }
            } else {
                $result->setData(['status' => 'error', 'message' => __('Data Not Updated')]);
                return $result;
            }
        } else {
            $result->setData(['status' => 'error', 'message' => __('Login First.')]);
            return $result;
        }
    }
}
