<?php
namespace Magentomobileshop\Bannersliderapp\Controller\Banner;

class Banner extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customHelper = $customHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $connection    = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('                                 \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
            $result        = $connection->fetchAll("SELECT * FROM magentomobile_bannersliderapp");
            $array         = array();
            $k             = 0;
            foreach ($result as $key => $value) {
                $array[$k]['banner_id']         = $value['banner_id'];
                $array[$k]['name']              = $value['name'];
                $array[$k]['order_banner']      = $value['order_banner'];
                $array[$k]['status']            = $value['status'];
                $array[$k]['link_type']         = $value['url_type'];
                $array[$k]['check_type']        = $value['check_type'];
                $array[$k]['category_name']     = null;
                $array[$k]['product_id']        = $value['product_id'];
                $array[$k]['category_id']       = $value['category_id'];
                $array[$k]['image_url']         = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'images/' . $value['thumbnail'];
                $array[$k]['image_description'] = $value['image_alt'];
                $k++;
            }
            echo json_encode(array('status' => 'success', 'data' => $array));
        } catch (\Exception $e) {
            echo $this->messageManager->addError($e->getMessage());
            exit;
        }
    }
}
