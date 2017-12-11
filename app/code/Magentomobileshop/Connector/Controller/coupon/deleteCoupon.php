<?php
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
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutCart       = $checkoutCart;
        $this->customer           = $customer;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->saleRule           = $saleRule;
        $this->checkoutSession    = $checkoutSession;
        $this->saleCoupon         = $saleCoupon;
        $this->customHelper       = $customHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $couponCode     = $this->getRequest()->getParam('coupon_code');
        if (!$couponCode) {
            echo json_encode(array('status' => 'error', 'message' => __('Coupon Code missing')));
            exit;
        }
        $cart      = $this->checkoutCart;
        $applyCode = $cart->getQuote()->getCouponCode();
        if ($couponCode == $applyCode) {
            $carts                 = $this->checkoutCart->getQuote()->setCouponCode(' ')->collectTotals()->save();
            $product['subtotal']   = $carts->getSubtotal();
            $product['grandtotal'] = $carts->getGrandTotal();
            $product['totalitems'] = $carts->getItemsCount();
            //     $product['symbol']     = Mage::helper('connector')->getCurrencysymbolByCode($this->currency);
            echo json_encode(array('status' => "success", 'message' => $product));
            exit;
        } else {
            echo json_encode(array('status' => "error", 'message' => __('Coupon code missmatch')));
            exit;
        }
    }
}
