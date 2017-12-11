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

class Getshippingmethods extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Shipping\Model\Config $shippingMethodConfig,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->checkoutCart         = $checkoutCart;
        $this->shippingMethodConfig = $shippingMethodConfig;
        $this->currency             = $currency;
        $this->storeManager         = $storeManager;
        $this->scopeConfig          = $scopeConfig;
        $this->directoryHelper      = $directoryHelper;
        $this->customHelper         = $customHelper;
        $this->resultJsonFactory  = $resultJsonFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId   = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId    = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency  = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $currentCurrency = $this->currency;
        $baseCurrency    = $this->storeManager->getStore()->getBaseCurrencyCode();
        $methods         = $this->shippingMethodConfig->getActiveCarriers();
        $result         = $this->resultJsonFactory->create();

        foreach ($methods as $shippigCode => $shippingModel) {
            if ($carrierMethods = $shippingModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $shippigCode . '_' . $methodCode;
                    if ($shippigCode == 'freeshipping') {
                        if ($this->scopeConfig->getValue('carriers/' . $shippigCode . '/free_shipping_subtotal') < $this->checkoutCart->getQuote()->getBaseSubtotalWithDiscount()) {
                            $shippingTitle = $this->scopeConfig->getValue('carriers/' . $shippigCode . '/title');
                            $shippingPrice = $this->scopeConfig->getValue('carriers/' . $shippigCode . '/price');

                            $shipMethods[] = array(
                                'code'  => $shippigCode . '_' . $shippigCode,
                                'value' => $shippingTitle,
                                'price' => number_format(
                                    $this->directoryHelper->currencyConvert(
                                        $shippingPrice,
                                        $baseCurrency,
                                        $currentCurrency
                                    ),
                                    2,
                                    '.',
                                    ''
                                ),
                            );
                        }
                    } else {
                        $shippingTitle = $this->scopeConfig->getValue('carriers/' . $shippigCode . '/title');
                        $shippingPrice = $this->scopeConfig->getValue('carriers/' . $shippigCode . '/price');
                        $shipMethods[] = array(
                            'code'  => $code,
                            'value' => $shippingTitle,
                            'price' => number_format(
                                $this->directoryHelper->currencyConvert(
                                    $shippingPrice,
                                    $baseCurrency,
                                    $currentCurrency
                                ),
                                2,
                                '.',
                                ''
                            ),
                        );
                    }
                }
            }
        }
        $result->setData($shipMethods);
            return $result;
    }
}
