<?php
namespace Magentomobileshop\Connector\Controller\customer;

class DeleteAddress extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->addressFactory    = $addressFactory;
        $this->storeManager      = $storeManager;
        $this->customHelper      = $customHelper;
        $this->customerSession   = $customerSession;
        $this->customer          = $customer;
        $this->addressRepository = $addressRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $customer       = $this->customerSession;
        $addressId      = $this->getRequest()->getParam('addressId');
        if (!$addressId) {
            echo json_encode(array('status' => 'error', 'message' => 'Address Id is missing.'));
            exit;
        }
        if ($customer->isLoggedIn()) {
            //if ($this->addressRepository->getCustomerId() === $this->customerSession()->getCustomerId()) {

            try {
                $this->addressRepository->deleteById($addressId);
                echo json_encode(array('status' => 'success', 'message' => 'Request complete.'));
            } catch (\Exception $e) {
                echo json_encode(array(
                    'status'  => 'error',
                    'message' => __($e->getMessage()),
                ));
            }
            exit;
            // }
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Login first.'));
            exit;
        }
    }
}
