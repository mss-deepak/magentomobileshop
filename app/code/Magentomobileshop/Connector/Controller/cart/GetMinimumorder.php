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

//use Magento\Checkout\Model\Session;

class GetMinimumorder extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Session
     */
    // protected $checkoutSession;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        // Session $checkoutSession,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Helper\Cart $checkoutHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->checkoutCart      = $checkoutCart;
        $this->checkoutHelper    = $checkoutHelper;
        $this->productModel      = $productModel;
        $this->jsonHelper        = $jsonHelper;
        $this->scopeConfig       = $scopeConfig;
        $this->customHelper      = $customHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $result         = $this->resultJsonFactory->create();
        $cart_data      = $this->jsonHelper->jsonDecode($this->getRequest()->getParam('cart_data'));
        if (!sizeof($cart_data)) {
            $result->setData(['status' => 'error', 'message' => __('Nothing to add in cart, cart is empty.')]);
            return $result;
        }
        $carts = $this->checkoutHelper->getCart();
        $carts->truncate();
        $cart = $this->checkoutCart;
        $cart->setQuote($carts->getQuote());
        foreach ($cart_data['items'] as $params) {
            try {
                $final_params  = [];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($params['product']);
                if ($product) {
                    if (isset($params['qty'])) {
                        $final_params['qty'] = $params['qty'];
                    }
                    $final_params['product'] = $params['product'];
                    $search                  = array('"{', '}"');
                    $replace                 = array('{', '}');
                    if (isset($params['super_attribute'])) {
                        $subject                         = ($params['super_attribute']);
                        $params['super_attribute']       = $this->jsonHelper->jsonDecode(str_replace($search, $replace, $subject));
                        $final_params['super_attribute'] = $params['super_attribute'];
                    }
                    if (isset($params['options'])) {
                        $subject                 = $params['options'];
                        $final                   = str_replace($search, $replace, $subject);
                        $params['options']       = $this->jsonHelper->jsonDecode($final);
                        $final_params['options'] = $params['options'];
                    }
                    if (isset($params['bundle_option'])) {
                        $final_params['bundle_option'] = $this->jsonHelper->jsonDecode($params['bundle_option']);
                    }
                    $request = new \Magento\Framework\DataObject($final_params);
                    $cart->addProduct($product, $request);
                }
            } catch (\Exception $e) {
                $result->setData(['status' => 'error', 'message' => $e->getMessage()]);
                return $result;
            }
        }
        try {
            $cart->save();
        } catch (\Exception $e) {
            $result->setData(['status' => 'error', 'message' => $e->getMessage()]);
            return $result;
        }
        if ($this->scopeConfig->getValue('sales/minimum_order/active')) {
            $check_grand_total = $this->checkoutHelper->getQuote()->getBaseSubtotalWithDiscount();

            $amount = $this->scopeConfig->getValue('sales/minimum_order/amount');
            if ($check_grand_total < $amount) {
                $message = $this->scopeConfig->getValue('sales/minimum_order/error_message');
                if (!$message) {
                    $message = 'Minimum Order Limit is ' . $amount;
                }
                 $result->setData(['status' => 'error', 'message' => $this->$message]);
                 return $result;
            }
        }
         $result->setData(['status' => 'success', 'message' => 'true']);
        return $result;
    }
}
