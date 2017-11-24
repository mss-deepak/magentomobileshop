<?php
namespace Magentomobileshop\Connector\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $storeManager;
    const XML_SECURE_TOKEN_EXP = 'secure/token/exp';
    public function __construct(
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Tax\Helper\Data $helperData,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currentCurrency,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterface $_order,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation
    ) {
        $this->checkoutCart              = $checkoutCart;
        $this->helperData                = $helperData;
        $this->productModel              = $productModel;
        $this->imageHelper               = $imageHelper;
        $this->storeManager              = $storeManager;
        $this->checkoutHelper            = $checkoutHelper;
        $this->checkoutSession           = $checkoutSession;
        $this->couponFactory             = $couponFactory;
        $this->currentCurrency           = $currentCurrency;
        $this->date                      = $date;
        $this->_order                    = $_order;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->scopeConfig               = $scopeConfig;
        $this->cartHelper                = $cartHelper;
        $this->customerAddress           = $customerAddress;
        $this->customerSession           = $customerSession;
        $this->wishlistRepository        = $wishlistRepository;
        $this->productRepository         = $productRepository;
        $this->taxCalculation            = $taxCalculation;
    }

    public function _getCartInformation($addressId ,$countryId ,$setRegionId, $shipping_method , $zipcode)
    {
        $shipping_amount = $this->_getShippingTotal($addressId ,$countryId ,$setRegionId, $shipping_method, $zipcode);
        $cart = $this->checkoutCart;
        if ($cart->getQuote()->getItemsCount()) {
            //$cart->init ();
            $cart->save();
        }
        $cart->getQuote()->collectTotals()->save();
        $cartInfo               = array();
        $cartInfo['is_virtual'] = $cart->getIsVirtualQuote();
        $cartInfo['cart_items'] = $this->_getCartItems();
        $cartInfo['cart_items_count']     =  $this->cartHelper->getSummaryCount();
        $cartInfo['grand_total']          = number_format($cart->getQuote()->getGrandTotal(), 2, '.', '');
        $cartInfo['sub_total']            = number_format($cart->getQuote()->getSubtotal(), 2, '.', '');
        $cartInfo['allow_guest_checkout'] = $this->checkoutHelper->isAllowedGuestCheckout($this->checkoutSession->getQuote());
        $cartInfo ['shipping_amount'] = $shipping_amount;

        return $cartInfo;
    }

    public function getCurrencysymbolByCode($code)
    {
        return $this->currentCurrency->getCurrencySymbol() ?: $code;
    }

    public function getPriceInclAndExclTax(int $productId)
    {
        $product = $this->productRepository->getById($productId);
     
        if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
            // First get base price (=price excluding tax)
            $productRateId = $taxAttribute->getValue();
            $rate = $this->taxCalculation->getCalculatedRate($productRateId);
     
            if ((int) $this->scopeConfig->getValue(
                'tax/calculation/price_includes_tax', 
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 1
            ) {
                // Product price in catalog is including tax.
                $priceExcludingTax = $product->getPrice() / (1 + ($rate / 100));
            } else {
                // Product price in catalog is excluding tax.
                $priceExcludingTax = $product->getPrice();
            }
     
            $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));
            return array(
                'incl' => $priceIncludingTax,
                'excl' => $priceExcludingTax
            );
        }
     
        throw new LocalizedException(__('Tax Attribute not found'));
    }

    protected function _getCartItems()
    {
        $cartItemsArr            = array();
        $cart                    = $this->checkoutSession;
        $quote                   = $cart->getQuote();
        $displayCartPriceInclTax = $this->helperData->displayCartPriceInclTax();
        $displayCartPriceExclTax = $this->helperData->displayCartPriceExclTax();
        $displayCartBothPrices   = $this->helperData->displayCartBothPrices();

        $items           = $quote->getAllVisibleItems();
        $baseCurrency    = $this->storeManager->getStore()->getBaseCurrencyCode();
        $currentCurrencys = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        $product_model = $this->productModel;

        foreach ($items as $item) {
                $this->getPriceInclAndExclTax($item->getProduct()->getId());
            $product                          = $product_model->load($item->getProduct()->getId());
            $cartItemArr                      = array();
            $cartItemArr['cart_item_id']      = $item->getId();
            $cartItemArr['currency']          = $this->currentCurrency->getCurrencySymbol();
            $cartItemArr['entity_type']       = $item->getProductType();
            $cartItemArr['item_id']           = $item->getProduct()->getId();
            $cartItemArr['item_title']        = strip_tags($item->getProduct()->getName());
            $cartItemArr['qty']               = $item->getQty();
            $cartItemArr['thumbnail_pic_url'] = $this->imageHelper
                                                    ->init($product_model, 'product_page_image_small')
                                                    ->setImageFile($product_model->getFile())
                                                    ->resize('100', '100')
                                                    ->getUrl();
            $cartItemArr['custom_option']     = $this->_getCustomOptions($item);
            $cartItemArr['item_price']        = number_format($item->getPrice(), 2, '.', '');
            array_push($cartItemsArr, $cartItemArr);
        }

        return $cartItemsArr;
    }

    protected function _getCustomOptions($item)
    {
        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

        $result = array();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    protected function _getMessage()
    {
        $cart = $this->checkoutCart;
        if (!Mage::getSingleton('checkout/type_onepage')->getQuote()->hasItems()) {
            $this->errors[] = $this->__('Cart is empty!');
            return $this->errors;
        }
        if (!$cart->getQuote()->validateMinimumAmount()) {
            $warning        = Mage::getStoreConfig('sales/minimum_order/description');
            $this->errors[] = $warning;
        }

        if (($messages = $cart->getQuote()->getErrors())) {
            foreach ($messages as $message) {
                if ($message) {
                    $message        = str_replace("\"", "||", $message);
                    $this->errors[] = $this->__($message->getText());
                }
            }
        }

        return $this->errors;
    }

    public function _getShippingTotal($addressId ,$countryId ,$setRegionId, $shipping_method , $zipcode){
        
        $quote= $this->checkoutCart->getQuote();
        if(isset($addressId)) {
        $customer   = $this->customerAddress->load($addressId);
        $countryId = $customer['country_id'];
        $setRegionId = $customer['region_id'];
        $regionName = $customer['region'];
        $shippingCheck = $quote->getShippingAddress()->getData();

          if($shippingCheck['shipping_method'] != $shipping_method) {
                    if (isset($setRegionId)){
                        $quote->getShippingAddress()
                          ->setCountryId($countryId)
                          ->setRegionId($setRegionId)
                          ->setPostcode($zipcode)
                          ->setCollectShippingRates(true);
                    } else {
                    $quote->getShippingAddress()
                          ->setCountryId($countryId)
                          ->setRegion($regionName)
                          ->setPostcode($zipcode)
                          ->setCollectShippingRates(true);              
                    }
                    $quote->save();
                    $quote->getShippingAddress()->setShippingMethod($shipping_method)->save();
                }
        
                $quote->collectTotals ()->save ();
                $amount=$quote->getShippingAddress()->getData();
                $shipping_amount = $amount['shipping_incl_tax'];
                return  $shipping_amount;
            } else {  
                $shippingCheck = $quote->getShippingAddress()->getData();

                if($shippingCheck['shipping_method'] != $shipping_method) {
                    if (isset($setRegionId)){
                        $quote->getShippingAddress()
                          ->setCountryId($countryId)
                          ->setRegionId($setRegionId)
                          ->setPostcode($zipcode)
                          ->setCollectShippingRates(true);
                    } else {  
                    $quote->getShippingAddress()
                          ->setCountryId($countryId)
                          ->setPostcode($zipcode)
                          ->setCollectShippingRates(true);  
                    }
                    $quote->save();
                    $quote->getShippingAddress()->setShippingMethod($shipping_method)->save();
                }
                $quote->collectTotals ()->save ();
                $amount=$quote->getShippingAddress();
                $shipping_amount = $amount['shipping_incl_tax'];
                return $shipping_amount;
            }
 
    }

    public function _getCartTotal()
    {
        $cart             = $this->checkoutCart;
        $totalItemsInCart = $cart->getQuote()->getItemsCount(); // total items in cart
        $totals           = $this->checkoutSession->getQuote()->getTotals(); // Total object
        $oldCouponCode    = $cart->getQuote()->getCouponCode();

        $oCoupon = $this->couponFactory->create();
        $oCoupon->load($oldCouponCode, 'code');
        $oRule = $oCoupon->getRuleId();

        $subtotal   = round($totals["subtotal"]->getValue()); // Subtotal value
        $grandtotal = round($totals["grand_total"]->getValue()); // Grandtotal value
        if (isset($totals['discount'])) { // $totals['discount']->getValue()) {
            $discount = round($totals['discount']->getValue()); // Discount value if applied
        } else {
            $discount = '0';
        }
        if (isset($totals['tax'])) { // $totals['tax']->getValue()) {
            $tax = round($totals['tax']->getValue()); // Tax value if present
        } else {
            $tax = '';
        }
        return array(
            'subtotal'    => $subtotal,
            'grandtotal'  => $grandtotal,
            'discount'    => str_replace('-', '', $discount),
            'tax'         => $tax,
            'coupon_code' => $oldCouponCode,
            'coupon_rule' => $oRule,
            //'coupon_rule' => $oRule->getData()
        );
    }

    public function compareExp()
    {
        $saved_session   = strtotime($this->scopeConfig->getValue('secure/token/exp'));
        $current_session = strtotime($this->date->gmtDate());
        return round(($current_session - $saved_session) / 3600);
    }
    public function loadParent($helper)
    {

        if ($this->compareExp() > 4800) {
            echo json_encode(array('status' => 'error', 'code' => '001'));
            exit;
        }

        if ($this->scopeConfig->getValue('magentomobileshop/secure/token') != $helper) {
            echo json_encode(array('status' => 'error', 'code' => '002'));
            exit;
        }
        if (!$this->scopeConfig->getValue('magentomobileshop/key/status')) {
            echo json_encode(array('status' => 'error', 'code' => '003'));
            exit;
        }
        if ($this->compareExp() > 4800 ||
            $this->scopeConfig->getValue('magentomobileshop/secure/token') != isset($helper)
            || !$this->scopeConfig->getValue('magentomobileshop/key/status') || !$helper) {
            echo json_encode(array('status' => 'error', 'code' => '004'));
            exit;
        }
    }
    public function storeConfig($storeid)
    {
        if ($this->storeManager->getStore()->getStoreId() == $storeid) {
            return $this->storeManager->getStore()->getStoreId();
            exit;
        } else {
            return $storeid;
            exit;
        }
    }
    public function viewConfig($viewid)
    {
        return $viewid;
    }
    public function currencyConfig($currency)
    {
        return $currency;
    }

    public function getOrderDetails($_orderId)
    {
        if ($_orderId) {
            $_order     = $this->_order->loadByIncrementId($_orderId);
            $data_order = $_order->getData();
            if ($data_order) {
                $_items      = $_order->getAllItems();
                $_orderItems = array();
                foreach ($_items as $_item) {
                    $product      = $this->_productRepositoryFactory->create()->getById($_item->getProductId());
                    $productImage = $this->imageHelper->init($product, 'product_base_image')
                        ->constrainOnly(true)
                        ->keepAspectRatio(true)
                        ->keepTransparency(true)
                        ->keepFrame(false)
                        ->resize('75')->getUrl();
                    $_orderItems[] = [
                        'sku'             => $_item->getSku(),
                        'item_id'         => $_item->getId(),
                        'product_image'   => $productImage,
                        'price'           => $_item->getPrice(),
                        'discount_amount' => $_item->getDiscountAmount(),
                        'qty_ordered'     => $_item->getQtyOrdered(),
                        //'symbol'          => $this->currentCurrency->getCurrencySymbol(),
                    ];
                }
                $data_order['symbol']   =$this->currentCurrency->getCurrencySymbol();
                $result                  = array();
                $result['order_details'] = $data_order;
                $result['order_items']   = $_orderItems;
                $result['status']        = 'success';
            } else {
                $result['status']  = 'error';
                $result['message'] = 'Order Not Found.';
            }
        } else {
            $result['status']  = 'error';
            $result['message'] = 'Please Provide Order Id.';
        }

        return $result;
    }

    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }

    public function getSpecialPriceProduct($productId)
        {  
            $product = $this->productModel->load($productId);
          //  $baseCurrency = $this->getBaseCurrencyCode();
          //  $currentCurrency = $this->getCurrentCurrencyCode();


            $specialprice =$this->getSpecialPriceByProductId($productId);
            $final_price_with_tax = $product->getData('final_price');
                                
             if($specialprice >= $final_price_with_tax):
                return $final_price_with_tax;
             else:
                return $specialprice;
             endif;
        }
    public function getSpecialPriceByProductId($productId)
    {
            $product = $this->productModel->load($productId);
            $specialprice = $product->getData('special_price');
            $specialPriceFromDate = $product->getData('special_from_date');
            $specialPriceToDate = $product->getData('special_to_date');
            
            $today = time();
         
            if ($specialprice):
                if($today >= strtotime( $specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime( $specialPriceFromDate) && is_null($specialPriceToDate)):
                        return $specialprice;
                else: return '0.00'; endif;
            else:
                return '0.00';
            endif;
    }

    public function check_wishlist($productId){

            $customer =  $this->customerSession;

            if($customer->isLoggedIn()):

                $wishlist = $this->wishlistRepository->create()->loadByCustomerId($customer->getId(), true);
                $wishListItemCollection = $wishlist->getItemCollection();
                $wishlist_product_id = array();
                foreach ($wishListItemCollection as $item){   

                     $wishlist_product_id[]=   $item->getProductId();
                 }
                if(in_array($productId,  $wishlist_product_id))
                    return true;
                else
                    return false; 
                
            else:
                return false;
            endif;
        }
}
