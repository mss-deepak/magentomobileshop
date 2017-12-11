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

class PostCoupon extends \Magento\Framework\App\Action\Action
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
        if (!$couponCode) {
            $result->setData(['status' => 'error', 'message' => __('Coupon Code missing')]);
            return $result;
        }
        $cart            = $this->checkoutCart;
        $cartCount       = count($cart);
        $coupan_codes    = array();
        $rulesCollection = $this->saleRule->getCollection();
        foreach ($rulesCollection as $rule) {
            $coupan_codes[] = $rule->getCode();
        }
        if (!in_array($couponCode, $coupan_codes)) {
            $result->setData(['status' => 'error', 'message' => __('Coupon code  is not Valid')]);
            return $result;
        }
        if (!$cart->getItemsCount()) {
            $result->setData(['status' => 'error', 'message' => __("You can't use coupon code with an empty shopping cart")]);
            return $result;
        }
        try {
            $codeLength        = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= 255;

            $cart->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $cart->getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals()->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $cart->getQuote()->getCouponCode()) {
                    $messages = array(
                        'status'  => 'true',
                        'message' => __('Coupon code "%s" was applied. $couponCode'));
                } else {
                    $messages = array(
                        'status'  => 'error',
                        'message' => __('Coupon code "%s" is not valid., $couponCode'));
                }
            } else {
                $messages = array(
                    'status'  => 'error',
                    'message' => __('Coupon code was canceled.'));
            }
        } catch (\Exception $e) {
            $messages = array(
                'status'  => 'error',
                'message' => $e->getMessage(),
            );
        } catch (\Exception $e) {
            $messages = array(
                'status'  => 'error',
                'message' => $this->__('Cannot apply the coupon code.'),
            );
        }
        $return = $this->_getCartTotal();
        if ($return['coupon_code']) {
            $result->setData([$return]);
            return $result;
        } else {
            $messages = array(
                'status'  => 'error',
                'message' => __('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode)));
            $result->setData($messages);
            return $result;
        }
    }

    protected function _getCartTotal()
    {
        $cart             = $this->checkoutCart;
        $totalItemsInCart = $this->checkoutCartHelper->getItemsCount(); // total items in cart
        $totals           = $this->checkoutSession->getQuote()->getTotals(); // Total object
        $oldCouponCode    = $cart->getQuote()->getCouponCode();
        $oCoupon          = $this->saleCoupon->load($oldCouponCode, 'code');
        $oRule            = $this->saleRule->load($oCoupon->getRuleId());

        $subtotal   = number_format($totals["subtotal"]->getValue(), 2, '.', ''); // Subtotal value
        $grandtotal = number_format($totals["grand_total"]->getValue(), 2, '.', ''); // Grandtotal value
        if (isset($totals['discount'])) { // $totals['discount']->getValue()) {
            $discount = number_format($totals['discount']->getValue(), 2, '.', ''); // Discount value if applied
        } else {
            $discount = '0';
        }
        if (isset($totals['tax'])) { // $totals['tax']->getValue()) {
            $tax = number_format($totals['tax']->getValue(), 2, '.', ''); // Tax value if present
        } else {
            $tax = '';
        }
        return array(
            'subtotal'    => $subtotal,
            'grandtotal'  => $grandtotal,
            'discount'    => str_replace('-', '', $discount),
            'tax'         => $tax,
            'coupon_code' => $oldCouponCode,
            'coupon_rule' => $oRule->getData(),
        );
    }
}
