<?php
namespace Magentomobileshop\Connector\Controller\Cart;

class clearcart extends \Magento\Framework\App\Action\Action
{
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
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Checkout\Model\Cart $checkoutCart

    ) {
        $this->checkoutCart   = $checkoutCart;
        $this->messageManager = $messageManager;
        $this->customHelper   = $customHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $cart           = $this->checkoutCart;
        if ($cart->getQuote()->getItemsCount()) {
            $cart->truncate()->save();
        }
        //$cart->clear();

        $result = '{"result":"success"';
        $result .= ', "message": "' . __('cart is empty!') . '"}';
        echo $result;
    }
}
