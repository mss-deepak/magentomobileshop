<?php
namespace Magentomobileshop\Connector\Controller\customer;

class loginStatus extends \Magento\Framework\App\Action\Action
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
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        if ($this->customerSession->isLoggedIn()):
            echo json_encode(array('status' => true));
            exit;
        else:
            echo json_encode(array('status' => false));
            exit;
        endif;

    }
}
