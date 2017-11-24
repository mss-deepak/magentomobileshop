<?php
namespace Magentomobileshop\Connector\Controller\customer;

class getMyOrders extends \Magento\Framework\App\Action\Action
{

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession         = $customerSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->imageHelper             = $imageHelper;
        $this->customHelper            = $customHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        if ($this->customerSession->isLoggedIn()) {
            $cust_id      = $this->customerSession->getId();
            $res          = array();
            $totorders    = $this->__getOrders($cust_id);
            $res["total"] = count($totorders);
            # start order  loop
            foreach ($totorders as $order) {

                $shippingAddress = $order->getShippingAddress();
                if (is_object($shippingAddress)) {
                    $shippadd = array();
                    $flag     = 0;
                    if (count($totorders) > 0) {
                        $flag = 1;
                    }

                    $shippadd = array(
                        "firstname" => $shippingAddress->getFirstname(),
                        "lastname"  => $shippingAddress->getLastname(),
                        "company"   => $shippingAddress->getCompany(),
                        "street"    => $shippingAddress->getStreetFull(),
                        "region"    => $shippingAddress->getRegion(),
                        "city"      => $shippingAddress->getCity(),
                        "pincode"   => $shippingAddress->getPostcode(),
                        "countryid" => $shippingAddress->getCountry_id(),
                        "contactno" => $shippingAddress->getTelephone(),
                        "shipmyid"  => $flag,
                    );
                }
                $billingAddress = $order->getBillingAddress();
                if (is_object($billingAddress)) {
                    $billadd = array();
                    $billadd = array(
                        "firstname" => $billingAddress->getFirstname(),
                        "lastname"  => $billingAddress->getLastname(),
                        "company"   => $billingAddress->getCompany(),
                        "street"    => $billingAddress->getStreetFull(),
                        "region"    => $billingAddress->getRegion(),
                        "city"      => $billingAddress->getCity(),
                        "pincode"   => $billingAddress->getPostcode(),
                        "countryid" => $billingAddress->getCountry_id(),
                        "contactno" => $billingAddress->getTelephone(),
                    );
                }
                $payment = array();
                $payment = $order->getPayment();
                try {
                    $payment_result = array(
                        "payment_method_title" => $payment->getMethodInstance()->getTitle(),
                        "payment_method_code"  => $payment->getMethodInstance()->getCode(),
                    );
                    if ($payment->getMethodInstance()->getCode() == "banktransfer") {

                        $payment_result["payment_method_description"] = $payment->getMethodInstance()->getInstructions();
                    }
                } catch (Exception $ex2) {

                }

                $items                       = $order->getAllVisibleItems();
                $itemcount                   = count($items);
                $name                        = array();
                $unitPrice                   = array();
                $sku                         = array();
                $ids                         = array();
                $qty                         = array();
                $images                      = array();
                $test_p                      = array();
                $itemsExcludingConfigurables = array();
                $productlist                 = array();
                foreach ($items as $itemId => $item) {
                    $name = $item->getName();
                    //echo $item->getName();
                    if ($item->getOriginalPrice() > 0) {
                        $unitPrice = number_format($item->getOriginalPrice(), 2, '.', '');
                    } else {
                        $unitPrice = number_format($item->getPrice(), 2, '.', '');
                    }

                    $sku = $item->getSku();
                    $ids = $item->getProductId();
                    //$qty[]=$item->getQtyToInvoice();
                    $qty           = (int) $item->getQtyOrdered();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $products      = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                    $images        = $this->imageHelper
                        ->init($products, 'product_page_image_large')
                        ->setImageFile($products->getFile())
                        ->resize('250', '250')
                        ->getUrl();

                    $productlist[] = array(
                        "name"             => $name,
                        "sku"              => $sku,
                        "id"               => $ids,
                        "quantity"         => (int) $qty,
                        "unitprice"        => $unitPrice,
                        "image"            => $images,
                        "total_item_count" => $itemcount,
                        "price_org"        => $test_p,
                        "price_based_curr" => 1,
                    );

                } # item foreach close
                $order_date = $order->getCreatedAt() . '';
                $orderData  = array(
                    "id"                    => $order->getId(),
                    "order_id"              => $order->getRealOrderId(),
                    "status"                => str_replace('-', ' ', $order->getStatus()),
                    "order_date"            => $order_date,
                    "grand_total"           => number_format($order->getGrandTotal(), 2, '.', ''),
                    "shipping_address"      => $shippadd,
                    "billing_address"       => $billadd,
                    "shipping_message"      => $order->getShippingDescription(),
                    "shipping_amount"       => number_format($order->getShippingAmount(), 2, '.', ''),
                    "payment_method"        => $payment_result,
                    "tax_amount"            => number_format($order->getTaxAmount(), 2, '.', ''),
                    "products"              => $productlist,
                    "order_currency"        => $order->getOrderCurrencyCode(),
                    "order_currency_symbol" => 'USD',
                    "currency"              => 'USD',
                    "couponUsed"            => 0,
                );
                $couponCode = $order->getCouponCode();
                if ($couponCode != "") {
                    $orderData["couponUsed"]      = 1;
                    $orderData["couponCode"]      = $couponCode;
                    $orderData["discount_amount"] = floatval(number_format($order->getDiscountAmount(), 2, '.', '')) * -1;
                }
                $res["data"][] = $orderData;
            } # end foreach
            echo json_encode($res);

        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Please Login to see the Orders'));
        }
        exit();
    }

    protected function __getOrders($customerId)
    {

        $this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
            $customerId
        )->setOrder(
            'created_at',
            'desc'
        );
        return $this->orders;
    }

}
