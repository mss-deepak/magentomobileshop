<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\Cart;

class ClearAllCart extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->cart              = $cart;
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
        $cart           = $this->cart->getQuote();
        $result         = $this->resultJsonFactory->create();
        if (!count($cart->getAllItems())) {
            $result->setData(['status' => 'error', 'message' => __('No item in your cart')]);
            return $result;
        } else {
            try {
                $this->cart->truncate();
                $result->setData(['status' => 'success', 'message' => __('Cleared all cart Items')]);
                return $result;
            } catch (\Exception $e) {
                $result->setData(['status' => 'error', 'message' => __($e->getMessage())]);
                return $result;
            }
        }
    }
}
