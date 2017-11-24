<?php
namespace Magentomobileshop\Connector\Controller\customer;

use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;

class login extends \Magento\Framework\App\Action\Action
{

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CustomerUrl $customerHelperData,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magentomobileshop\Connector\Helper\Data $customHelper
        //    \Magento\Framework\View\Result\PageFactory  $resultPageFactory,
        //    \Magento\Customer\Model\CustomerFactory $customerFactory,
        //  \Magento\Store\Model\StoreManagerInterface $storeManager,
    ) {
        $this->customerSession           = $customerSession;
        $this->customerUrl               = $customerHelperData;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->messageManager            = $messageManager;
        $this->customHelper              = $customHelper;
        //  $this->storeManager = $storeManager;
        // $this->resultPageFactory = $resultPageFactory;
        // $this->customerFactory = $customerFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $this->loginApi();

    }

    protected function loginApi()
    {

        $username     = $this->getRequest()->getParam('username');
        $password     = $this->getRequest()->getParam('password');
        $validate     = array();
        $customerinfo = array();
        try {
/*validations start*/
            if (($username == null) || ($password == null)) {
                $validate['status']  = false;
                $message             = __('Wrong username and password');
                $validate['message'] = $message;
                echo json_encode($validate);
                return;
            }
/*validations End*/
            else {
/*Customer login portion start*/
                $customer = $this->customerAccountManagement->authenticate($username, $password);
                $this->customerSession->setCustomerDataAsLoggedIn($customer);
                $this->customerSession->regenerateId();

                if ($this->customerSession->isLoggedIn()) {
                    $customer_data = $this->customerSession->getCustomer()->getData();

                    $customerinfo = array(
                        "id"    => $customer_data['entity_id'],
                        "name"  => $customer_data['firstname'] . $customer_data['lastname'],
                        "email" => $customer_data['email'],
                    );
                    echo json_encode(array('status' => 'success', 'message' => $customerinfo));
                } else {
                    return false;
                }
/*Customer login portion end*/
            }
        } catch (EmailNotConfirmedException $e) {
            $value   = $this->customerUrl->getEmailConfirmationUrl($username);
            $message = __(
                'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                $value
            );
            echo json_encode(array('status' => false, 'message' => $message));
            //   $this->customerSession->setUsername($username);
        } catch (UserLockedException $e) {
            $message = __(
                'The account is locked. Please wait and try again or contact %1.',
                $this->getScopeConfig()->getValue('contact/email/recipient_email')
            );
            echo json_encode(array('status' => false, 'message' => $message));
            //  $this->customerSession->setUsername($username);
        } catch (AuthenticationException $e) {
            $message = __('Invalid login or password.');
            echo json_encode(array('status' => false, 'message' => $message));
            //  $this->customerSession->setUsername($username);
        } catch (\Exception $e) {
            $message = __('An unspecified error occurred. Please contact us for assistance.');
            echo json_encode(array('status' => false, 'message' => $message));
        }
    }

}
