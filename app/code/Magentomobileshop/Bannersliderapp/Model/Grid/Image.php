<?php
namespace Magentomobileshop\Bannersliderapp\Model\Grid;

use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;

class Image
{
    /**
     * media sub folder
     * @var string
     */
    protected $subDir = 'magentomobileshop/bannersliderapp/image';

    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $fileSystem;
    }
    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $this->subDir . '/image/';
    }
}
