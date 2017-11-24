<?php
namespace Magentomobileshop\Connector\Controller\customer;

class setuserinfo extends \Magento\Framework\App\Action\Action
{

    public function __construct(\Magento\Framework\App\Action\Context $context,
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

                if (!$address):
                    $address = $this->addressFactory->create();
                    $address->setCustomerId($customer->getId());
                    $address->setIsDefaultBilling(true);
                endif;

                /*   $address->setFirstname($customer_info['firstname']);
                $address->setLastname($customer_info['lastname']);
                $address->setTelephone($customer_info['telephone']?:'null');
                $address->setCity($customer_info['city']?:'null');
                $address->setStreet($customer_info['street']?:'null');
                $address->setState($customer_info['region']?:'null');
                $address->setCountry($customer_info['country']?:'null');
                $address->setPostcode($customer_info['postcode']?:'null');*/

                try {

                    //$address->save();
                    $customer->save();
                    echo json_encode(array('status' => 'success', 'message' => 'Data Updated successfully'));
                } catch (exception $e) {
                    echo json_encode(array('status' => 'error', 'message' => 'Data Not Updated'));
                }
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Data Not Updated'));
            }
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Login First.'));
        }
    }

}
