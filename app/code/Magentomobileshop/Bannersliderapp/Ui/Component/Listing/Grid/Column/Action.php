<?php
namespace Magentomobileshop\Bannersliderapp\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Action extends Column
{
    const ROW_EDIT_URL = 'grid/grid/addrow';
    const NAME         = 'logo';
    const ALT_FIELD    = 'name';
    protected $_urlBuilder;
    private $_editUrl;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = [],
        $editUrl = self::ROW_EDIT_URL
    ) {
        $this->_urlBuilder  = $urlBuilder;
        $this->_editUrl     = $editUrl;
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('image_path');
            foreach ($dataSource['data']['items'] as &$item) {
                $imagesContainer            = '';
                $mediaRelativePath          = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $logoPath                   = $mediaRelativePath . $item['image_path'];
                $imagesContainer            = "<img src='" . $mediaRelativePath . 'images/' . $item['image_path'] . "' width='75' height='75'/>";
                $item[$fieldName . '_src']  = $imagesContainer;
                $item[$fieldName . '_alt']  = $this->getAlt($item);
                $item[$fieldName . '_link'] = $this->_urlBuilder->getUrl(
                    'brand/manage/edit',
                    ['banner_id' => $item['banner_id'], 'store' => $this->context->getRequestParam('store'), 'label' => __('Edit')]
                );
                $item[$fieldName . '_orig_src'] = $imagesContainer;
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
