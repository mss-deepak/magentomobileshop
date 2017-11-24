<?php
namespace Magentomobileshop\Connector\Controller\Feedback;

class SendFeedback extends \Magento\Framework\App\Action\Action
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

        $email = $this->getRequest()->getParams('email');
        $message = $this->getRequest()->getParams('message');
        if($email)
        {
            $result['message']= 'Thanks for your valuable feedback.';
            $result['status']='success';
            echo json_encode($result);
            exit;
        }
        echo json_encode (
                    array (
                    'status' => 'error',
                    'message' => 'please enter the email !!'
                    ) 
        );
    }
}