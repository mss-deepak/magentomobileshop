<?php
namespace Magentomobileshop\Connector\Controller\Cart;

class updateCart extends \Magento\Framework\App\Action\Action
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
        $id             = $this->getRequest()->getParam('cart_data');

        $cart = $this->checkoutCart;

        if ($id):
            try {
                $cart->removeItem($id)->save();
                echo json_encode(array("status" => "success"));
            } catch (Mage_Core_Exception $e) {
                echo json_encode(array("status" => "error", "message" => __($e->getMessage())));

            } catch (Exception $e) {
                echo json_encode(array("status" => "error", "message" => __($e->getMessage())));

            } else :
            $message = __("Param cart_item_id is empty.");
            echo json_encode(array("status" => "error", "message" => $message));
            exit;
        endif;
    }
}
