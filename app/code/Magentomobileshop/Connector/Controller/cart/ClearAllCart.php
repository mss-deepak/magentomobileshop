<?php
namespace Magentomobileshop\Connector\Controller\Cart;

class ClearAllCart extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Checkout\Model\Cart $cart

    ) {
        $this->cart         = $cart;
        $this->customHelper = $customHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $cart           = $this->cart->getQuote();
        if (!count($cart->getAllItems())) {
            echo json_encode(array('status' => 'error', 'message' => "No item in your cart"));
        } else {
            try {
                $this->cart->truncate();
                echo json_encode(array('status' => 'success', 'message' => "Cleared all cart Items"));
            } catch (exception $e) {
                echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
            }
        }

        exit();
    }
}
