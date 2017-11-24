<?php
namespace Magentomobileshop\Connector\Controller\Customer;

class Getuserinfo extends \Magento\Framework\App\Action\Action
{

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper
    ) {
        $this->customerSession   = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory   = $customerFactory;
        $this->storeManager      = $storeManager;
        $this->customHelper      = $customHelper;
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

        if ($this->customerSession->isLoggedIn()):
            $info = array();

            $customer          = $this->customerSession->getCustomer();
            $info['firstname'] = $customer->getFirstname();
            $info['lastname']  = $customer->getLastname();
            $customerAddressId = $customer->getDefaultBilling();

            if ($customerAddressId):
                $address = $this->customerFactory->create();
                $address->load($customerAddressId);
                if (sizeof($address)) {
                    $info['postcode']  = $address->getPostcode();
                    $info['city']      = $address->getCity();
                    $street            = $address->getStreet();
                    $info['street']    = $street[0];
                    $info['telephone'] = $address->getTelephone();
                    $info['fax']       = $address->getFax();
                    $info['country']   = $address->getCountry();
                    $info['region']    = $address->getRegion();
                }

                echo json_encode(array('status' => 'success', 'data' => $info));
                exit;
            else:

                echo json_encode(array('status' => 'success', 'data' => $info));
                exit;
            endif;

        else:
            $message = __('Login First.');
            echo json_encode(array('status' => 'error', 'message' => $message));
            exit;

        endif;
    }
}
