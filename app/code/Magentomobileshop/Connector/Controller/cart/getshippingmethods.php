<?php
namespace Magentomobileshop\Connector\Controller\cart;

class getshippingmethods extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Shipping\Model\Config $shippingMethodConfig,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magentomobileshop\Connector\Helper\Data $customHelper

    ) {
        $this->checkoutCart         = $checkoutCart;
        $this->shippingMethodConfig = $shippingMethodConfig;
        $this->currency             = $currency;
        $this->storeManager         = $storeManager;
        $this->scopeConfig          = $scopeConfig;
        $this->directoryHelper      = $directoryHelper;
        $this->customHelper         = $customHelper;
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
                                'price' => number_format($this->directoryHelper->currencyConvert($shippingPrice, $baseCurrency,
                                    $currentCurrency), 2, '.', ''),
                            );
                        }
                    } else {
                        $shippingTitle = $this->scopeConfig->getValue('carriers/' . $shippigCode . '/title');
                        $shippingPrice = $this->scopeConfig->getValue('carriers/' . $shippigCode . '/price');
                        $shipMethods[] = array(
                            'code'  => $code,
                            'value' => $shippingTitle,
                           'price' => number_format($this->directoryHelper->currencyConvert($shippingPrice, $baseCurrency, $currentCurrency
                           ), 2, '.', ''),
                            // 'price' => $this->storeManager->getStore()->getBaseCurrency()->convert($shippingPrice
                            //     ,$currentCurrency),
                        );
                    }
                }

            }

        }
        echo json_encode($shipMethods);
    }
}
