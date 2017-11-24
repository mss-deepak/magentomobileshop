<?php
namespace Magentomobileshop\Connector\Controller\Token;
 
 
 
class Settoken extends \Magento\Framework\App\Action\Action
{
    const XML_SECURE_KEY_STATUS = 'magentomobileshop/key/status';
    const XML_SECURE_KEY = 'magentomobileshop/secure/key';
	
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Config\Model\ResourceModel\Config $resourceConfig,
                                \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
                                \Psr\Log\LoggerInterface $logger	
    							 )
								{
								    parent::__construct($context);
                                    $this->scopeConfig = $scopeConfig;
                                    $this->resourceConfig = $resourceConfig;
                                    $this->cacheTypeList = $cacheTypeList;
                                    $this->logger = $logger;
								}

        public function execute()
        {   
            try{
            $params = $this->getRequest ()->getParams ();
                  
            if(isset($params['secure_key']) && isset($params['status'])):

                $this->resourceConfig->saveConfig(self::XML_SECURE_KEY, $params['secure_key'], 'default', 0); 
                $this->resourceConfig->saveConfig(self::XML_SECURE_KEY_STATUS, $params['status'], 'default', 0); 
                $this->cacheTypeList->cleanType('config');

                echo json_encode(array('status'=>'success','message'=>'Data updated.'));
            else:

                echo json_encode(array('status'=>'error','message'=> $this->logger->addDebug('Required parameters are missing.')));

            endif;

            }
            catch(\Exception $e){

                echo json_encode(array('status'=>'error','message'=>  $this->logger->debug($e->getMessage)));

            }

        }

}