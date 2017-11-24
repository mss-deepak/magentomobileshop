<?php
namespace Magentomobileshop\Connector\Controller\customer;
class changePassword extends \Magento\Customer\Controller\AbstractAccount
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer  $customerModel
    ) {
        $this->customerSession           = $customerSession;
        $this->customHelper              = $customHelper;
        $this->jsonHelper                = $jsonHelper;
        $this->customerFactory           = $customerFactory;
        $this->customerModel             = $customerModel;
        parent::__construct($context);
    }
    public function execute()
    {
      //  $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $validate = 0;
        $result = '';
        if ($this->customerSession->isLoggedIn())
        {

            $customer = $this->customerSession->getCustomer();
            $customerid  = $customer->getEntityId();
            $oldpassword = $this->getRequest ()->getParam ('oldpassword');
            $newpassword = $this->getRequest ()->getParam ('newpassword');
            $username    = $customer->getEmail();
            try {
                 $login_customer_result = $this->customerModel->setWebsiteId('1')->authenticate($username, $oldpassword);
                 $validate = 1;
            }
            catch(Exception $ex) {
                 $validate = 0;
            }
            if($validate == 1) {
                 try {
                      $customer = $this->customerModel->load($customerid);
                      $customer->setPassword($newpassword);
                      $customer->save();
                      echo  json_encode(array('status'=>'success','message'=>'Your Password has been Changed Successfully'));
                      exit;
                 }
                 catch(Exception $ex) {
                    echo  json_encode(array('status'=>'error','message'=>'Error : '));
                    exit;
                 }
            }
            else {
                 echo  json_encode(array('status'=>'error','message'=>'Incorrect Old Password.'));
                 exit;
            }
        } else {
            echo  json_encode(array('status'=>'error','message'=>'Kindly Signin first.'));
            exit;
        }
    }
}
