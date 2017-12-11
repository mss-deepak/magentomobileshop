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

class Add extends \Magento\Framework\App\Action\Action
{
    protected $_messageManager;
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
        \Magento\Checkout\Model\Session $session,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutCart      = $checkoutCart;
        $this->productModel      = $productModel;
        $this->jsonHelper        = $jsonHelper;
        $this->resolverInterface = $resolverInterface;
        $this->_messageManager   = $context->getMessageManager();
        $this->session           = $session;
        $this->customHelper      = $customHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request           = $requestInterface;
        parent::__construct($context);
    }
    public function execute()
    {
        $final_params = array();
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        try {
            $params     = $this->request->getParams();
            $product_id = $this->request->getParam('product');
            $product    = $this->productModel->load($product_id);
            $result     = $this->resultJsonFactory->create();
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->resolverInterface->getLocale()]
                );
                $params['qty']       = $filter->filter($params['qty']);
                $final_params['qty'] = $params['qty'];
            } elseif ($product_id == '') {
                $this->_messageManager->addError(__('Product Not Added
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
            $results    = '{"result":"success"';
            $results .= ', "items_qty": "' . $items_qty . '"';
            $results .= ', "cart_item_id": "' . $cartItemArr . '"}';
            $result->setData([$results]);
            return $result;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $results = '{"result":"error"';
            $results .= ', "message": "' . str_replace("\"", "||", $e->getMessage()) . '"}';
            $results->setData([$results]);
            return $result;
        } catch (\Exception $e) {
            $this->_messageManager->addException($e, __('error.'));
        }
    }
}
