<?php
namespace Magentomobileshop\Bannersliderapp\Api\Data;

interface GridInterface
{
    const BANNER_ID    = 'banner_id';
    const NAME         = 'name';
    const ORDER_BANNER = 'order_banner';
    const STATUS       = 'status';
    const URL_TYPE     = 'url_type';
    const CHECK_TYPE   = 'check_type';
    const PRODUCT_ID   = 'product_id';
    const CATAGORY_ID  = 'category_id';
    const IMAGE        = 'image';
    const IMAGE_ALT    = 'image_alt';

    public function getBannerId();
    public function setBannerId($bannerId);

    public function getName();
    public function setName($name);

    public function getOrderBanner();
    public function setOrderBanner($orderBanner);

    public function getStatus();
    public function setStatus($status);

    public function getUrlType();
    public function setUrlType($urlType);

    public function getCheckType();
    public function setCheckType($checkType);

    public function getProductId();
    public function setProductId($productId);

    public function getCategoryId();
    public function setCategoryId($categoryId);

    public function getImagePath();
    public function setImagePath($imagePath);

    public function getImageAlt();
    public function setImageAlt($imageAlt);
}
