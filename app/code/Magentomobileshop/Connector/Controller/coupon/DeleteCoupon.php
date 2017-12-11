<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\Coupon;

class DeleteCoupon extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Magento\Customer\Model\Customer $customer,
        \Magento\SalesRule\Model\Rule $saleRule,
        \Magento\SalesRule\Model\Coupon $saleCoupon,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->checkoutCart       = $checkoutCart;
        $this->customer           = $customer;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->saleRule           = $saleRule;
        $this->checkoutSession    = $checkoutSession;
        $this->saleCoupon         = $saleCoupon;
        $this->customHelper       = $customHelper;
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->request            = $requestInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $couponCode     = $this->request->getParam('coupon_code');
        $result         = $this->resultJsonFactory->create();
        if (!$couponCode) {
            $result->setData(['status' => 'error', 'message' => __('Coupon Code missing')]);
            return $result;
        }
        $cart      = $this->checkoutCart;
        $applyCode = $cart->getQuote()->getCouponCode();
        if ($couponCode == $applyCode) {
            $carts                 = $this->checkoutCart->getQuote()->setCouponCode(' ')->collectTotals()->save();
            $product['subtotal']   = $carts->getSubtotal();
            $product['grandtotal'] = $carts->getGrandTotal();
            $product['totalitems'] = $carts->getItemsCount();
            $result->setData(['status' => 'success', 'message' => __($product)]);
            return $result;
        } else {
            $result->setData(['status' => 'error', 'message' => __('Coupon code missmatch')]);
            return $result;
        }
    }
}
