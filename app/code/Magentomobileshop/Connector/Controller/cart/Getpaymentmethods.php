<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\cart;

class Getpaymentmethods extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Payment\Model\Config $paymentMethodConfig,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->paymentMethodConfig = $paymentMethodConfig;
        $this->scopeConfig         = $scopeConfig;
        $this->customHelper        = $customHelper;
        $this->resultJsonFactory   = $resultJsonFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $payments       = $this->paymentMethodConfig->getActiveMethods();
        $result         = $this->resultJsonFactory->create();
        $methods        = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            if ($this->scopeConfig->getValue('magentomobileshop_payment' . '/' . $paymentCode)) {
                $paymentTitle = $this->scopeConfig->getValue('payment/' . $paymentCode . '/title');
                if ($paymentCode == 'authorizenet') {
                    $methods[] = array(
                        'value' => $paymentTitle,
                        'code'  => $paymentCode,
                        'cards' => array('Visa' => 'VI', 'Mastercard' => 'MC', 'American Express' => 'AE', 'Discover' => 'DI'),
                    );
                } else {
                    $methods[] = array(
                        'value' => $paymentTitle,
                        'code'  => $paymentCode,
                    );
                }
            }
        }
        $result->setData($methods);
        return $result;
    }
}
