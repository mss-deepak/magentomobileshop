<?php
namespace Magentomobileshop\Connector\Controller\staticpages;

class GetPages extends \Magento\Framework\App\Action\Action
{
    const MSS_APP_PAGE_LIST = 'configuration/app_pages/cms_page_list';
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,

        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory,
        array $data = []
    ) {
        $this->scopeConfig           = $scopeConfig;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->customHelper          = $customHelper;
        parent::__construct($context);
    }
    public function execute()
    { 
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId  = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId   = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        try {
            $identifier = $this->scopeConfig->getValue(self::MSS_APP_PAGE_LIST);
            $data       = array();
            $pages      = explode(',', $identifier);
            foreach ($pages as $page) {
                if ($page) {
                    $page_model = $this->pageCollectionFactory->create()->addFieldToFilter('identifier', array('eq' => $page));
                    foreach ($page_model as $key => $value) {
                        $data[] = array(
                                'page_title' => $value->getTitle(),
                                'identifier'                 => $value->getIdentifier(),
                                'page_content'               => preg_replace('/<\/?a[^>]*>/','',$value->getContent())
                            );
                    }
                }
            }
            if (sizeof($data)) {
                echo json_encode(array('status' => 'success', 'count' => COUNT($data), 'data' => $data));
                exit;
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'No page configured, please configure page first'));
                exit;
            }
        } catch (exception $e) {
            echo json_encode(array('status' => 'error', 'message' => 'Problem in loading data.'));
            exit;
        }
    }
}
