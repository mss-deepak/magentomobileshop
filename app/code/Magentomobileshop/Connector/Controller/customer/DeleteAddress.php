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

class DeleteAddress extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->addressFactory    = $addressFactory;
        $this->storeManager      = $storeManager;
        $this->customHelper      = $customHelper;
        $this->customerSession   = $customerSession;
        $this->customer          = $customer;
        $this->addressRepository = $addressRepository;
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
        $customer       = $this->customerSession;
        $addressId      = $this->request->getParam('addressId');
        $result         = $this->resultJsonFactory->create();
        if (!$addressId) {
            $result->setData(['status' => 'error', 'message' => __('Address Id is missing.')]);
            return $result;
        }
        if ($customer->isLoggedIn()) {
            try {
                $this->addressRepository->deleteById($addressId);
                $result->setData(['status' => 'success', 'message' => __('Request complete.')]);
                return $result;
            } catch (\Exception $e) {
                $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                return $result;
            }
        } else {
            $result->setData(['status' => 'error', 'message' => __('Login first.')]);
            return $result;
        }
    }
}
