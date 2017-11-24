<?php
namespace Magentomobileshop\Bannersliderapp\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;

class Thumbnail extends Column
{
    const ROW_EDIT_URL = 'grid/grid/addrow';
    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';
    protected $_urlBuilder;
    protected $_storeManager;
    private $_editUrl;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $components = [],
        array $data = [],
        $editUrl = self::ROW_EDIT_URL
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_editUrl    = $editUrl;
        $this->_storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
         if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $mediaRelativePath=$this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $logoPath=$mediaRelativePath.'images/'.$item['thumbnail'];
                $item[$fieldName . '_src'] = $logoPath;
                $item[$fieldName . '_alt'] = $this->getAlt($item);
                // $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                //     'brand/manage/edit',
                //     ['brand_id' => $item['banner_id'], 'store' => $this->context->getRequestParam('store')]
                // );
                $item[$fieldName . '_orig_src'] = $logoPath;

            }
        }
        return $dataSource;
    }
    protected function getAlt($row)
    {
        $altField = self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
