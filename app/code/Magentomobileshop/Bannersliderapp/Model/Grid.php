<?php
namespace Magentomobileshop\Bannersliderapp\Model;

use Magentomobileshop\Bannersliderapp\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    const CACHE_TAG         = 'magentomobile_bannersliderapp';
    protected $_cacheTag    = 'magentomobile_bannersliderapp';
    protected $_eventPrefix = 'magentomobile_bannersliderapp';
    protected function _construct()
    {
        $this->_init('Magentomobileshop\Bannersliderapp\Model\ResourceModel\Grid');
    }
    public function getBannerId()
    {
        return $this->getData(self::BANNER_ID);
    }
    public function setBannerId($bannerId)
    {
        return $this->setData(self::BANNER_ID, $bannerId);
    }
    public function getName()
    {
        return $this->getData(self::NAME);
    }
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }
    public function getOrderBanner()
    {
        return $this->getData(self::ORDER_BANNER);
    }
    public function setOrderBanner($orderBanner)
    {
        return $this->setData(self::ORDER_BANNER, $orderBanner);
    }
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
    public function getUrlType()
    {
        return $this->getData(self::URL_TYPE);
    }
    public function setUrlType($urlType)
    {
        return $this->setData(self::URL_TYPE, $urlType);
    }
    public function getCheckType()
    {
        return $this->getData(self::CHECK_TYPE);
    }
    public function setCheckType($checkType)
    {
        return $this->setData(self::CHECK_TYPE, $checkType);
    }
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
    public function getCategoryId()
    {
        return $this->getData(self::CATAGORY_ID);
    }
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATAGORY_ID, $categoryId);
    }
    public function getImagePath()
    {
        return $this->getData(self::IMAGE);
    }
    public function setImagePath($imagePath)
    {
        return $this->setData(self::IMAGE, $image);
    }
    public function getImageAlt()
    {
        return $this->getData(self::IMAGE_ALT);
    }
    public function setImageAlt($imageAlt)
    {
        return $this->setData(self::IMAGE_ALT, $imageAlt);
    }
}
