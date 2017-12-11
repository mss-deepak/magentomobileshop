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

class Getcheckoutcart extends \Magento\Framework\App\Action\Action
{
    const XML_SETTING_ACTIVE = 'wishlist/general/active';
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Customer\Model\Customer $customer,
        //   \Magento\Sale\Model\Quote   $quote,
        \Magento\Catalog\Model\Product $catalog,
        \Magento\Directory\Model\Currency $currentCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->checkoutCart       = $checkoutCart;
        $this->customHelper       = $customHelper;
        $this->customer           = $customer;
        $this->catalog            = $catalog;
        $this->currentCurrency    = $currentCurrency;
        $this->storeManager       = $storeManager;
        $this->imageHelper        = $imageHelper;
        $this->scopeConfig        = $scopeConfig;
        $this->wishlistRepository = $wishlistRepository;
        $this->customerSession    = $customerSession;
        $this->wishlistHelper     = $wishlistHelper;
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
        $customerId     = $this->request->getParam('customerid');

        if ($customerId) {
            $customer = $this->customer->load($customerId);
            try {
                $cart = $this->checkoutCart->getQuote();
                if (!count($cart->getAllItems())) {
                      $result->setData(['status' => 'success', 'message' => __('No item in your cart')]);
                    return $result;
                }
                $product_model = $this->catalog;
                $baseCurrency    = $this->storeManager->getStore()->getBaseCurrencyCode();
                $currentCurrency = $this->currency;
                foreach ($cart->getAllVisibleItems() as $item) {
                    $objectManager               = \Magento\Framework\App\ObjectManager::getInstance();
                    $singleProduct               = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                    $productName                 = array();
                    $productName['cart_item_id'] = $item->getId();
                    $productName['id']           = $item->getProductId();
                    $productName['sku']          = $item->getSku();
                    $productName['qty']          = $item->getQty();
                    $productName['Name']         = $item->getProduct()->getName();
                    $productName['Price']        = $item->getPrice() * $item->getQty();
                    $productName['image'] = $this->imageHelper
                        ->init($singleProduct, 'product_page_image_large')
                        ->setImageFile($singleProduct->getFile())
                        ->resize('100', '100')
                        ->getUrl();
                    $productName['wishlist'] = $this->customHelper->check_wishlist($item->getProductId());
                    if ($singleProduct->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                        $productName['configurable'] = $this->_getConfigurableOptions($item);
                    }
                    if ($singleProduct->getOptions()) {
                        ($this->_getCustomOption($item)) ? $productName['custom_option'] = $this->_getCustomOption($item) : '';
                    }
                    $product['product'][] = $productName;
                }
                $product['subtotal']   = number_format($cart->getSubtotal(), 2, '.', '');
                $product['grandtotal'] = number_format($cart->getGrandTotal(), 2, '.', '');
                $product['totalitems'] = $cart->getItemsCount();
                $product['symbol']     = $this->currentCurrency->getCurrencySymbol();

                  $result->setData(['status' => 'success', 'message' => $product]);
                return $result;
            } catch (\Exception $e) {
                 $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                return $result;
            }
        } else {
            try {
                $baseCurrency    = $this->storeManager->getStore()->getBaseCurrencyCode();
                $currentCurrency = $this->currency;
                $product_model   = $this->catalog;
                $cart  = $this->checkoutCart;
                $quote = $cart->getQuote();
                /*get last inserted cart ID*/
                foreach ($cart->getAllVisibleItems() as $item) {
                    $objectManager               = \Magento\Framework\App\ObjectManager::getInstance();
                    $singleProduct               = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                    $productName                 = array();
                    $productName['cart_item_id'] = $item->getId();
                    $productName['id']           = $item->getProductId();
                    $productName['sku']          = $item->getSku();
                    $productName['qty']          = $item->getQty();
                    $productName['Name']         = $item->getProduct()->getName();
                    $productName['Price']        = $item->getPrice() * $item->getQty();
                    $productName['image'] = $this->imageHelper
                        ->init($singleProduct, 'product_page_image_large')
                        ->setImageFile($singleProduct->getFile())
                        ->resize('100', '100')
                        ->getUrl();
                    $productName['wishlist'] = $this->customHelper->check_wishlist($item->getProductId());
                    if ($singleProduct->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                        $productName['configurable'] = $this->_getConfigurableOptions($item);
                    }
                    if ($singleProduct->getOptions()) {
                        ($this->_getCustomOption($item)) ? $productName['custom_option'] = $this->_getCustomOption($item) : '';
                    }
                    $product['product'][] = $productName;
                }
                $product['subtotal']   = $cart->getSubtotal();
                $product['grandtotal'] = $cart->getGrandTotal();
                $product['totalitems'] = $cart->getItemsCount();
                $product['symbol']     = $this->currency->getCurrencySymbol();

                 $result->setData(['status' => 'success', 'message' => $product]);
                return $result;
            } catch (\Exception $e) {
                  $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                return $result;
            }
        }
    }

    protected function _getConfigurableOptions($item)
    {

        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        $result = array();
        if (count($options['info_buyRequest']['super_attribute'])) {
            $configurable = array();
            $i            = 0;
            foreach ($options['info_buyRequest']['super_attribute'] as $key => $value) {
                $configurable['attribute_id']   = $key;
                $configurable['option_id']      = $value;
                $configurable['attribute_name'] = $options['attributes_info'][$i]['label'];
                $configurable['option_name']    = $options['attributes_info'][$i]['value'];
                $i++;
                $result[] = $configurable;
            }
        }

        return $result;
    }

    protected function _getCustomOption($item)
    {
        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        $result  = array();
        if (count($options['options'])) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $key => $option) {
                    if ($option['option_type'] == 'date') {
                        $timestamp                                = strtotime($option['option_value']);
                        $date                                     = json_encode(array('day' => date("d", $timestamp), 'month' => date("m", $timestamp), 'year' => date("Y", $timestamp)));
                        $options['options'][$key]['option_value'] = $date;
                    } elseif ($option['option_type'] == 'date_time') {
                        $timestamp = strtotime($option['option_value']);

                        $date = json_encode(array('day' => date("d", $timestamp), 'month' => date("m", $timestamp), 'year' => date("Y", $timestamp), 'hour' => date("h", $timestamp), 'minute' => date("i", $timestamp), 'day_part' => date('A', $timestamp)));
                        $options['options'][$key]['option_value'] = $date;
                    }
                }
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = $result = array_merge($result, $options['additional_options']);
            }
        }
        return $result;
    }
}
