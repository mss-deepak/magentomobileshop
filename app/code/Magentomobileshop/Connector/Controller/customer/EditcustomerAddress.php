<?php
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
        \Magento\Customer\Model\AddressFactory $addressFactory
    ) {
        $this->customerSession   = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory   = $customerFactory;
        $this->storeManager      = $storeManager;
        $this->customHelper      = $customHelper;
        $this->customer          = $customer;
        $this->addressFactory    = $addressFactory;
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

        if ($this->customerSession->isLoggedIn()) {
            $addressId   = $this->getRequest()->getParam('addressId');
            $addressData = json_decode($this->getRequest()->getParam('addressData'), 1);
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
            // @$address->setIsDefaultBilling($addressData['is_default_billing']);
            // @$address->setIsDefaultShipping($addressData['is_default_shipping']);
            $address->setSaveInAddressBook('1');
            try {
                $address->setId($addressId);
                $address->save();
                //  $customer->save();
                echo json_encode(array('status' => 'success', 'message' => __('Address Updated successfully.')));
                exit;
            } catch (\Mage_Core_Exception $e) {
                echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
                exit;
            }
        } else {
            echo json_encode(array('status' => 'error', 'message' => __('Kindly Sign in first.')));
            exit;
        }
    }
}
