<?php
/**
 * Magentomobileshop Extension
 *
 * @category Magentomobileshop
 * @package Magentomobileshop_Connector
 * @author Magentomobileshop
 * @copyright Copyright (c) 2012-2018 Master Software Solutions (http://mastersoftwaretechnologies.com)
 */

namespace Magentomobileshop\Connector\Controller\Token;

class Settoken extends \Magento\Framework\App\Action\Action
{
    const XML_SECURE_KEY_STATUS = 'magentomobileshop/key/status';
    const XML_SECURE_KEY        = 'magentomobileshop/secure/key';
    protected $resultJsonFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        parent::__construct($context);
        $this->scopeConfig       = $scopeConfig;
        $this->resourceConfig    = $resourceConfig;
        $this->cacheTypeList     = $cacheTypeList;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request           = $requestInterface;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $params = $this->request->getParams();
            if (isset($params['secure_key']) && isset($params['status'])) {
                $this->resourceConfig->saveConfig(self::XML_SECURE_KEY, $params['secure_key'], 'default', 0);
                $this->resourceConfig->saveConfig(self::XML_SECURE_KEY_STATUS, $params['status'], 'default', 0);
                $this->cacheTypeList->cleanType('config');

                $result->setData(['status' => 'success', 'message' => 'Data updated.']);
                return $result;
            } else {
                $result->setData(['status' => 'error', 'message' => 'Required parameters are missing.']);
                return $result;
            }
        } catch (\Exception $e) {
            $result->setData(['status' => 'error', 'message' => $e->getMessage()]);
            return $result;
        }
    }
}
