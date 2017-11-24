<?php
namespace Magentomobileshop\Connector\Controller\customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

/**
 * ForgotPasswordPost controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Forgotpwd extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Escaper */
    protected $escaper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->session                   = $customerSession;
        $this->customHelper              = $customHelper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper                   = $escaper;
        $this->jsonHelper                = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
      //  $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $result = array();
        $email  = $this->getRequest()->getParam('email');
        if ($email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->session->setForgottenEmail($email);
                $result['message'] = 'Please correct the email address.';
                $result['status']  = "error";
                echo $this->jsonHelper->jsonEncode($result);
                exit();
            }
            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $result['message'] = $exception->getMessage();
                $result['status']  = "error";
                echo $this->jsonHelper->jsonEncode($result);
                exit();
            } catch (\Exception $exception) {
                $result['message'] = 'We\'re unable to send the password reset email.';
                $result['status']  = "error";
                echo $this->jsonHelper->jsonEncode($result);
                exit();
            }
            $result['message'] = $this->getSuccessMessage($email);
            $result['status']  = "success";
            echo $this->jsonHelper->jsonEncode($result);
            exit();
        } else {
            $result['message'] = 'Please enter your email.';
            $result['status']  = "success";
            echo $this->jsonHelper->jsonEncode($result);
            exit();
        }
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }
}
