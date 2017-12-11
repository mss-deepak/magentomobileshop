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

class Logout extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->customerSession   = $customerSession;
        $this->customHelper      = $customHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->logoutApi();
    }

    protected function logoutApi()
    {
        try {
            $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
            $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
            $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
            $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
            $lastCustomerId = $this->customerSession->getId();
            $this->customerSession->logout($lastCustomerId);
            $result = $this->resultJsonFactory->create();
            if (!empty($lastCustomerId)) {
                $result->setData([true, '0x0000', null]);
                return $result;
            }
        } catch (\Exception $e) {
            $result->setData([false, '0x1000', __($e->getMessage())]);
            return $result;
        }
    }
}
