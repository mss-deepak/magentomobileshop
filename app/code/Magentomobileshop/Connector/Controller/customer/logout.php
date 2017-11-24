<?php
namespace Magentomobileshop\Connector\Controller\customer;

class logout extends \Magento\Framework\App\Action\Action
{

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper
    ) {
        $this->customerSession = $customerSession;
        $this->customHelper    = $customHelper;
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
            if (!empty($lastCustomerId)):
                //    $message = __('Customer logout successfully');
                echo json_encode(array(true, '0x0000', null));
            endif;
        } catch (\Exception $e) {
            echo json_encode(array(false, '0x1000', __($e->getMessage())));
        }

    }
}
