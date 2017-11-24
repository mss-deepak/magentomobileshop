<?php
namespace Magentomobileshop\Connector\Controller\Cart;

class Add extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Framework\Locale\ResolverInterface $resolverInterface,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $session,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper

    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutCart      = $checkoutCart;
        $this->productModel      = $productModel;
        $this->jsonHelper        = $jsonHelper;
        $this->resolverInterface = $resolverInterface;
        $this->messageManager    = $messageManager;
        $this->session           = $session;
        $this->customHelper      = $customHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $final_params = array();
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        try {
        $params     = $this->getRequest()->getParams();
        $product_id = $this->getRequest()->getParam('product');
        $product    = $this->productModel->load($product_id);
        if (isset($params['qty'])) {
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->resolverInterface->getLocale()]
            );
            $params['qty']       = $filter->filter($params['qty']);
            $final_params['qty'] = $params['qty'];
        } else if ($product_id == '') {
            $this->messageManager->addError(__('Product Not Added
                                The SKU you entered %s was not found." ,$sku'));
        }

        if ($product) {

            $final_params['product'] = $params['product'];
            if (isset($params['super_attribute'])) {
                $final_params['super_attribute'] = $this->jsonHelper->jsonDecode($params['super_attribute']);
            }
            if (isset($params['options'])) {
                $search                  = array('"{', '}"');
                $replace                 = array('{', '}');
                $subject                 = $params['options'];
                $final                   = str_replace($search, $replace, $subject);
                $params['options']       = json_decode($final, 1);
                $final_params['options'] = $params['options'];
            }
            if (isset($params['bundle_option'])) {
                $final_params['bundle_option'] = $this->jsonHelper->jsonDecode($params['bundle_option']);
            }

            $this->checkoutCart->addProduct($product, $final_params);
            $this->checkoutCart->save();
        }

        $quote = $this->session->getQuote();
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $cartItemArr = $item->getId();
        }

        $items_qty = floor($quote->getItemsQty());
        $result    = '{"result":"success"';
        $result .= ', "items_qty": "' . $items_qty . '"';
        $result .= ', "cart_item_id": "' . $cartItemArr . '"}';
        echo $result;

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = '{"result":"error"';
            $result .= ', "message": "' . str_replace("\"", "||", $e->getMessage()) . '"}';
            echo $result;

        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('error.'));
        }

    }
}
