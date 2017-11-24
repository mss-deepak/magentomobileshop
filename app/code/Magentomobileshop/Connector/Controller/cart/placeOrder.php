<?php
namespace Magentomobileshop\Connector\Controller\cart;

class PlaceOrder extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        // \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Checkout\Model\Session $checkoutSession,
        // \Magento\Sales\Model\Quote  $saleQuote,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Quote\Model\Quote $quotes,
        \Magento\Quote\Model\QuoteIdMaskFactory $QuoteIdMaskFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->storeManager       = $storeManager;
        $this->product            = $product;
        $this->formkey            = $formkey;
        $this->quote              = $quote;
        $this->quoteManagement    = $quoteManagement;
        $this->customerFactory    = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService       = $orderService;
        $this->scopeConfig        = $scopeConfig;
        $this->customerSession    = $customerSession;
        $this->customHelper       = $customHelper;
        $this->checkoutCart       = $checkoutCart;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->customerAddress    = $customerAddress;
        $this->checkoutSession    = $checkoutSession;
        $this->logger             = $logger;
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->customer           = $customer;
        $this->quotes             = $quotes;
        $this->QuoteIdMaskFactory = $QuoteIdMaskFactory;
        $this->quoteRepository    = $quoteRepository;
        $this->_eventManager      = $eventManager;
        /*   $this->saleQuote = $saleQuote;*/
        parent::__construct($context);
    }

/*
 * execute  Method
 * return type : json
 * login case parameter:-
 * parameters : shippingmethod ,paymentmethod, registration_id , data:"{     \"0\": \"{         \\\"firstname\\\": \\\"mages\\\",         \\\"lastname\\\": \\\"mages\\\",         \\\"email\\\": \\\"magedds@gmail.com\\\",         \\\"street_line_1\\\": \\\"Peer\\\",         \\\"city\\\": \\\"das\\\",         \\\"region_id\\\": \\\"Puasadasdnjab\\\",         \\\"region\\\": \\\"Puasadasdnjab\\\",         \\\"postcode\\\": \\\"14320130\\\",         \\\"country_id\\\": \\\"IN\\\",         \\\"telephone\\\": \\\"9041899933\\\",         \\\"customer_password\\\": null,         \\\"confirm_password\\\": null,         \\\"is_default_shipping\\\": 0,         \\\"is_default_billing\\\": 1     }\",     \"1\": \"{         \\\"firstname\\\": \\\"mages\\\",         \\\"lastname\\\": \\\"mages\\\",         \\\"email\\\": \\\"magedds@gmail.com\\\",         \\\"street_line_1\\\": \\\"Peer\\\",         \\\"city\\\": \\\"das\\\",         \\\"region_id\\\": \\\"Puasadasdnjab\\\",         \\\"region\\\": \\\"Puasadasdnjab\\\",         \\\"postcode\\\": \\\"14320130\\\",         \\\"country_id\\\": \\\"IN\\\",         \\\"telephone\\\": \\\"9041899933\\\",         \\\"customer_password\\\": null,         \\\"confirm_password\\\": null,         \\\"is_default_shipping\\\": 0,         \\\"is_default_billing\\\": 1     }\" }"
 * Guest case Parameter:-   userbillingid:1
shippingmethod:flatrate_flatrate
paymentmethod:checkmo
registration_id:29
usershippingid:1
 * APi Response :- Josn
 */

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $result         = $this->resultJsonFactory->create();
        if ($this->customerSession->isLoggedIn()) {
            $session    = $this->customerSession;
            $customerId = $session->getId();
            $totalItems = $this->checkoutCartHelper->getSummaryCount();
            if ($totalItems > 0) {
                $usershippingid  = (int) $this->getRequest()->getParam('usershippingid');
                $userbillingid   = (int) $this->getRequest()->getParam('userbillingid');
                $shipping_method = $this->getRequest()->getParam('shippingmethod');
                $paymentmethod   = $this->getRequest()->getParam('paymentmethod');
                $registration_id = $this->getRequest()->getParam('registration_id') ?: null;
                $deviceType      = $this->getRequest()->getParam('device_type') ?: null;
                $card_details    = $this->getRequest()->getParam('cards_details');
                $save_cc         = $this->getRequest()->getParam('save_cc');
                /*    if($paymentmethod == 'authorizenet')
                $this->validateCarddtails(json_decode($card_details,1));*/
                if (!\Zend_Validate::is($usershippingid, 'NotEmpty')) {
                    return $result->setData(array('Status' => 'error', 'message' => (__('AddressId should not be empty'))));
                    exit;
                }
                if (!\Zend_Validate::is($userbillingid, 'NotEmpty')) {
                    return $result->setData(array('Status' => 'error', 'message' => (__('AddressId should not be empty'))));
                    exit;
                }
                if (!\Zend_Validate::is($shipping_method, 'NotEmpty')) {
                    return $result->setData(array('Status' => 'error', 'message' => (__('Shippingmethod should not be empty'))));
                    exit;
                }
                if (!\Zend_Validate::is($paymentmethod, 'NotEmpty')) {
                    return $result->setData(array('Status' => 'error', 'message' => (__('paymentmethod should not be empty'))));
                    exit;
                }
                if ($usershippingid == '' && $userbillingid == '') {
                    return $result->setData(array('Status' => 'error', 'message' => (__('address is missing!!!!'))));
                    exit;
                }
                $customers = $this->customerSession->getCustomer()->getId();
                try {
                    $usershippingidData = $this->customerAddress->load($usershippingid)->getData();
                    $userbillingidData  = $this->customerAddress->load($userbillingid)->getData();
                    $quote              = $this->checkoutSession->getQuote();
                    $quote->setMms_order_type('app')->save();

                    $billingAddress  = $quote->getBillingAddress()->addData($userbillingidData);
                    $shippingAddress = $quote->getShippingAddress()->addData($usershippingidData);
                    $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                        ->setShippingMethod($shipping_method);
                    if ($paymentmethod != 'authorizenet') {
                        $shippingAddress->setPaymentMethod($paymentmethod);
                        $quote->getPayment()->importData(array('method' => $paymentmethod));
                    }
                    $quote->collectTotals()->save();
                    $order = $this->quoteManagement->submit($quote);

                    // Load event before order Place
                    $this->_eventManager->dispatch('connector_place_order', ['order' => $order, 'device_type' => $deviceType, 'device_registraton' => $registration_id]);
                    $order->setEmailSent(0);
                    $itemcount  = $order->getTotalItemCount();
                    $grandTotal = $order->getData('grand_total');
                    $order->setMms_order_type('app')->save();
                    $cart = $this->checkoutCart;
                    if ($cart->getQuote()->getItemsCount()) {
                        $current_cart = $this->checkoutCart;
                        $current_cart->truncate();
                        $current_cart->save();
                    }
                    $allItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
                    foreach ($allItems as $item) {
                        $itemId = $item->getItemId(); //item id of particular item
                        //    $quoteItem=$this->getItemModel()->load($itemId);//load particular item which you want to delete by his item id
                        $this->cart->removeItem($itemId)->save();
                    }
                    return $result->setData(['message' => ('Order placed successfully.'),
                        'orderid'                          => $order->getRealOrderId(),
                        'items_count'                      => $itemcount,
                        'grand_total'                      => $grandTotal,
                        'result'                           => 'success']);
                } catch (\Exception $e) {
                    return $result->setData(['status' => 'error', 'message' => $e->getMessage()]);
                    exit;
                }
            } else {
                return $result->setData(['message' => 'cart is empty', 'result' => 'success']);
            }
        } else {
            ini_set('memory_limit', '128M');
            $getParam  = $this->getRequest()->getParams();
            $getParams = $this->getRequest()->getParam('data');

            $json_data     = json_decode($getParams, 1);
            $json_billing1 = $json_data;

            $json_billing    = json_decode($json_data[0], 1);
            $json_shipping   = json_decode($json_data[1], 1);
            $paymentmethod   = $getParam['paymentmethod'];
            $shipping_method = $getParam['shippingmethod'];
            $card_details    = $this->getRequest()->getParam('cards_details');
            $save_cc         = $this->getRequest()->getParam('save_cc');
            $deviceType      = $this->getRequest()->getParam('device_type') ?: null;
            $registration_id = $this->getRequest()->getParam('registration_id') ?: null;

            try {
                $checkout_session = $this->checkoutSession->getQuoteId();
                $quote            = $this->quotes->load($checkout_session);
                //$this->checkoutSession->getQuote()->setMms_order_type('app')->save();
                $quote->setStoreId($this->storeManager->getStore()->getId());
                $billingAddress = array(
                    'firstname'            => $json_billing['firstname'],
                    'lastname'             => $json_billing['lastname'],
                    'email'                => $json_billing['email'],
                    'street'               => array(
                        $json_billing['street_line_1'],
                        // @$json_billing['street_line_2'],
                    ),
                    'city'                 => $json_billing['city'],
                    /*'region' => $getParams['region'],*/
                    'postcode'             => $json_billing['postcode'],
                    'country_id'           => $json_billing['country_id'],
                    'telephone'            => $json_billing['telephone'],
                    'customer_password'    => '',
                    'confirm_password'     => '',
                    'save_in_address_book' => '0',
                    //  'use_for_shipping' => '1',
                    'is_default_shipping'  => $json_billing['is_default_shipping'],
                    'is_default_billing'   => $json_billing['is_default_billing'],
                );
                $shippingAddress = array(
                    'firstname'            => $json_shipping['firstname'],
                    'lastname'             => $json_shipping['lastname'],
                    'email'                => $json_shipping['email'],
                    'street'               => array(
                        $json_shipping['street_line_1'],
                        // @$json_shipping['street_line_2'],
                    ),
                    'city'                 => $json_shipping['city'],
                    /*'region' => $getParams['region'],*/
                    'postcode'             => $json_shipping['postcode'],
                    'country_id'           => $json_shipping['country_id'],
                    'telephone'            => $json_shipping['telephone'],
                    'customer_password'    => '',
                    'confirm_password'     => '',
                    'save_in_address_book' => '0',
                    //'use_for_shipping' => '1',
                    'is_default_shipping'  => $json_shipping['is_default_shipping'],
                    'is_default_billing'   => $json_shipping['is_default_billing'],
                );
                if (isset($json_shipping['region'])) {
                    $shippingAddress['region'] = $json_shipping['region'];
                } else {
                    $shippingAddress['region_id'] = $json_shipping['region_id'];
                }

                if (isset($json_billing['region'])) {
                    $billingAddress['region'] = $json_billing['region'];
                } else {
                    $billingAddress['region_id'] = $json_billing['region_id'];
                }

                $quote->getBillingAddress()
                    ->addData($billingAddress);

                $quote->getShippingAddress()
                    ->addData($shippingAddress)
                    ->setShippingMethod($shipping_method);

                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals();

                if ($paymentmethod != 'authorizenet') {
                    $quote->setPaymentMethod($paymentmethod);
                    $quote->getPayment()->importData(array('method' => $paymentmethod));
                }

                $customer_id = $this->reigesterGuestUser(array('firstname' => $json_billing['firstname'], 'lastname' => $json_billing['lastname'], 'email' => $json_billing['email']));

                $quote->setCustomer($this->customerRepository->getById($customer_id));
                $quote->save();

                $order = $this->quoteManagement->submit($quote);
                // Load event before order Place
                $this->_eventManager->dispatch('connector_place_order', ['order' => $order, 'device_type' => $deviceType, 'device_registraton' => $registration_id]);
                // $order->setMms_order_type('app')->save();
                if ($paymentmethod == 'payucheckout_shared') {
                } else {
                    $order->setEmailSent(0);
                }
                $itemcount  = $order->getTotalItemCount();
                $grandTotal = $order->getData('grand_total');

                $increment_id = $order->getRealOrderId();
                $quote        = $customer        = $service        = null;
                $cart         = $this->checkoutCart;
                if ($cart->getQuote()->getItemsCount()) {
                    $current_cart = $this->checkoutCart;
                    $current_cart->truncate();
                    $current_cart->save();
                }
                //$this->checkoutSession->clear();
                echo json_encode(array('status' => 'success',
                    'orderid'                       => $increment_id,
                    'items_count'                   => $itemcount,
                    'grand_total'                   => $grandTotal,
                ));
                exit;
            } catch (Exception $e) {
                echo json_encode(array('status' => 'error', 'message' => __($e->getMessage())));
                exit;
            }
        }
    }

    private function radPassoword()
    {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1, 6))), 1, 6);
    }
    public function reigesterGuestUser($userdata)
    {
        $customer = $this->customer;
        $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
        $load_pre = $customer->loadByEmail($userdata['email']);
        if ($load_pre->getId()) {
            //    print_r($load_pre->getId()); die('exit');
            return $load_pre->getId();
            exit;
        }
        //  $customer = $customer->setId(null);
        $customer->setData('email', $userdata['email']);
        $customer->setData('firstname', $userdata['firstname']);
        $customer->setData('lastname', $userdata['lastname']);
        $customer->setData('password', $this->radPassoword());
        $customer->setConfirmation(null);
        $customer->save();
        //  print_r($customer->getId()); die('a');
        $customer->sendNewAccountEmail('registered', '', $this->storeManager->getStore()->getId());
        return $customer->getId();
        try {
            $customer->setConfirmation(null);
            $customer->save();
            $customer->sendNewAccountEmail('registered', '', $this->storeManager->getStore()->getId());
            return $customer->getId();
            exit;
        } catch (Exception $ex) {
            echo json_encode(array('status' => 'error', 'message' => __($ex->getMessage())));
            exit;
        }
    }
}
