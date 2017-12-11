<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\Customer;

class EditcustomerAddress extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->customerSession   = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory   = $customerFactory;
        $this->storeManager      = $storeManager;
        $this->customHelper      = $customHelper;
        $this->customer          = $customer;
        $this->addressFactory    = $addressFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request           = $requestInterface;
        parent::__construct($context);
    }
/*
 * execute  Method
 * return type : json
 * parameters : addressId= addressid , addressData = {  "firstname": "check",  "lastname": "check", "street": "",     "city": "Pamchkulla",     "country_id": "IN",     "region": null,     "postcode": "345555",     "telephone": "3563566",     "email": null,     "is_default_shipping": 0,     "is_default_billing": 1 }
 */
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $this->editAddressApi();
    }

    protected function editAddressApi()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->customerSession->isLoggedIn()) {
            $addressId   = $this->request->getParam('addressId');
            $addressData = json_decode($this->request->getParam('addressData'), 1);
            $address     = $this->addressFactory->create();
            $address->load($addressId);
            $address->setFirstname($addressData['firstname']);
            $address->setLastname($addressData['lastname']);
            $address->setCountryId($addressData['country_id']);
            $address->setPostcode($addressData['postcode']);
            $address->setCity($addressData['city']);
            $address->setTelephone($addressData['telephone']);
            if (isset($addressData['region'])) {
                $address->setRegion($addressData['region']);
            } else {
                $address->setRegionId($addressData['region_id']);
            }
            $address->setStreet($addressData['street']);
            $address->setSaveInAddressBook('1');
            try {
                $address->setId($addressId);
                $address->save();
                $result->setData(['status' => 'success', 'message' => __('Address Updated successfully.')]);
                return $result;
            } catch (\Exception $e) {
                $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                return $result;
            }
        } else {
            $result->setData(['status' => 'error', 'message' => __('Kindly Sign in first.')]);
            return $result;
        }
    }
}
