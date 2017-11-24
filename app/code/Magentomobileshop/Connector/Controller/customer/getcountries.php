<?php
namespace Magentomobileshop\Connector\Controller\customer;

class getcountries extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_directoryCountry;

    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Psr\Log\LoggerInterface $logger,
        \Magentomobileshop\Connector\Helper\Data $customHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->countryFactory    = $countryFactory;
        $this->storeManager      = $storeManager;
        $this->_resource         = $resource;
        $this->_directoryCountry = $directoryCountry;
        $this->customHelper      = $customHelper;
        $this->logger            = $logger;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $this->customHelper->loadParent($this->getRequest()->getHeader('token'));
        $this->storeId    = $this->customHelper->storeConfig($this->getRequest()->getHeader('storeid'));
        $this->viewId     = $this->customHelper->viewConfig($this->getRequest()->getHeader('viewid'));
        $this->currency   = $this->customHelper->currencyConfig($this->getRequest()->getHeader('currency'));
        $countries_return = $this->GetcountriesApi();
        $result           = $this->resultJsonFactory->create();
        return $result->setData($countries_return);
    }

    public function GetcountriesApi()
    {
        $countries            = $this->countryFactory->create()->getResourceCollection()->loadByStore();
        $countries            = $this->_directoryCountry->toOptionArray(true);
        $connection           = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $tbl_directory_region = $connection->getTableName('directory_country_region');
        foreach ($countries as $countryKey => $country) {
            if ($country['value'] != '') {
                $countries[$countryKey]['name'] = $countries[$countryKey]['label'];
                unset($countries[$countryKey]['label']);
                $stateArray = $this->countryFactory->create()->setId(
                    $countries[$countryKey]['value']
                )->getLoadedRegionCollection()->toOptionArray(); //Get all regions for the given ISO country code
                if (count($stateArray) > 0) {
                    unset($stateArray[0]);
                    foreach ($stateArray as $subkey => $subvalue) {
                        $stateArray[$subkey]['region_id']    = $stateArray[$subkey]['value'];
                        $stateArray[$subkey]['default_name'] = $stateArray[$subkey]['title'];
                        $stateArray[$subkey]['name']         = $stateArray[$subkey]['label'];
                        $stateArray[$subkey]['code']         = $connection->fetchOne('SELECT code FROM `' . $tbl_directory_region . '` WHERE region_id =' . $subvalue['value']);
                        unset($stateArray[$subkey]['value']);
                        unset($stateArray[$subkey]['title']);
                        unset($stateArray[$subkey]['label']);
                    }

                    $countries[$countryKey]['state'] = array_values($stateArray);
                } else {
                    $countries[$countryKey]['state'] = [];
                }
            }
        } //array_shift($countries);
        return $countries;

    }
}
