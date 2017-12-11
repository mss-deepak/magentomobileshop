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

use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;

class Login extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerUrl $customerHelperData,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->customerSession           = $customerSession;
        $this->customerUrl               = $customerHelperData;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customHelper              = $customHelper;
        $this->resultJsonFactory         = $resultJsonFactory;
        $this->request                   = $requestInterface;
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

        $username     = $this->request->getParam('username');
        $password     = $this->request->getParam('password');
        $validate     = array();
        $customerinfo = array();
        $result       = $this->resultJsonFactory->create();
        try {
            /*validations start*/
            if (($username == null) || ($password == null)) {
                $result->setData(['status' => false, 'message' => __('Wrong username and password')]);
                return $result;
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
                    $result->setData(['status' => 'success', 'message' => __($customerinfo)]);
                    return $result;
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
            $result->setData(['status' => false, 'message' => $message]);
            return $result;
            //   $this->customerSession->setUsername($username);
        } catch (\Exception $e) {
            $message = __(
                'The account is locked. Please wait and try again or contact %1.',
                $this->getScopeConfig()->getValue('contact/email/recipient_email')
            );
            $result->setData(['status' => false, 'message' => $message]);
            return $result;
            //  $this->customerSession->setUsername($username);
        } catch (AuthenticationException $e) {
            $message = __('Invalid login or password.');
            $result->setData(['status' => false, 'message' => $message]);
            return $result;
            //  $this->customerSession->setUsername($username);
        } catch (\Exception $e) {
            $message = __('An unspecified error occurred. Please contact us for assistance.');
            $result->setData(['status' => false, 'message' => $message]);
            return $result;
        }
    }
}
