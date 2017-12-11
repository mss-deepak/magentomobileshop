<?php
namespace Magentomobileshop\Connector\Controller\Cart;

class cancelOrder extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Customer\Model\Session $customerSession

    ) {
        $this->customerSession = $customerSession;
        $this->order           = $orderFactory;
        $this->customHelper    = $customHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $orderId        = (int) $this->getRequest()->getParam('orderId');

        if ($orderId) {
            if ($this->customerSession->isLoggedIn()) {
                try {
                    $order = $this->order->create()->loadByIncrementId($orderId);
                    if (!$order->getId()) {
                        echo json_encode(array('status' => 'error', 'message' => 'Invalid Order Id.'));
                        exit;
                    }

                    $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)
                        ->setStatus('Canceled')
                        ->addStatusHistoryComment('Order marked as cancelled by User.', false)
                        ->setIsCustomerNotified(true);

                    $order->save();

                    if ($order->canCancel()) {
                        $order->cancel()->save();
                    }
                    echo json_encode(array('status' => 'success', 'message' => 'Order marked as cancelled by User'));
                    exit;

                } catch (Exceptio $e) {
                    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
                    exit;
                }

                echo json_encode(array('status' => 'success', 'message' => 'Order marked as cancelled by User.'));
                exit;
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Login first tio cancel Order.'));
                exit;
            }

        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Please send Order Id to cancel.'));
            exit;
        }
        exit();
    }
}
