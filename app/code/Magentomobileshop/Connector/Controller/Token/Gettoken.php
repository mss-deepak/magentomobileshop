<?php
namespace Magentomobileshop\Connector\Controller\Token;
 
 
 
class Gettoken extends \Magento\Framework\App\Action\Action
{
    const XML_SECURE_KEY_STATUS = 'magentomobileshop/key/status';
    const XML_SECURE_KEY = 'magentomobileshop/secure/key';
    const XML_SECURE_TOKEN = 'magentomobileshop/secure/token';
    const XML_SECURE_TOKEN_EXP = 'secure/token/exp';
	
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Config\Model\ResourceModel\Config $resourceConfig,
                                \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
                                \Psr\Log\LoggerInterface $logger,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Framework\Message\ManagerInterface $messageManager,
                                \Magentomobileshop\Connector\Helper\Data  $customHelper,
                                \Magento\Framework\Stdlib\DateTime\DateTime $date,
                                \Magento\Customer\Model\Session  $customerSession
    							 )
								{
								    parent::__construct($context);
                                    $this->scopeConfig = $scopeConfig;
                                    $this->resourceConfig = $resourceConfig;
                                    $this->cacheTypeList = $cacheTypeList;
                                    $this->logger = $logger;
                                    $this->request = $request;
                                    $this->messageManager = $messageManager;
                                    $this->customHelper = $customHelper;
                                    $this->date = $date;
                                    $this->customerSession = $customerSession;
								}

        public function execute(){

                $this->_gettokenApi() ;
        }

        public function _gettokenApi()
        {   

            try{
                if($this->scopeConfig->getValue(self::XML_SECURE_KEY_STATUS)):
                    
                   
                    $params = $this->getRequest()->getHeader('token');
                    if(isset($params)):

                        if($params == $this->scopeConfig->getValue(self::XML_SECURE_KEY)):
               

                            if($this->scopeConfig->getValue(self::XML_SECURE_TOKEN_EXP) && 
                                $this->customHelper->compareExp() < 4800):

                                    echo json_encode(array('status'=>'success','token'=> $this->scopeConfig->getValue(self::XML_SECURE_TOKEN)));
                                    exit;
                            endif;

                            $token = $this->radToken();
                            $current_session =$this->date->gmtDate('Y-m-d H:i:s');

                            $this->resourceConfig->saveConfig(self::XML_SECURE_TOKEN, $token, 'default', 0); 
                            $this->resourceConfig->saveConfig(self::XML_SECURE_TOKEN_EXP, $current_session, 'default', 0);
                            
                            //clearing cache
                           $this->cacheTypeList->cleanType('config');
                            
                            $this->getSession();

                            if($this->getRequest()->getHeader('username') && $this->getRequest()->getHeader('password')):
                                echo json_encode(array('status'=>'success','token'=>$token,'user'=>$this->usersession($this->getRequest()->getHeader('username'),$this->getRequest()->getHeader('username'))));
                                exit;
                            else:
                                echo json_encode(array('status'=>'success','token'=>$token));
                                exit;
                            endif;
                        else:
                            echo json_encode(array('status'=>'error','message'=> 'Invalid secure key.'));

                        endif;
                    else:

                        echo json_encode(array('status'=>'error','message'=> 'Secure key is required.'));

                    endif;

                else:
                        echo json_encode(array('status'=>'error','message'=> 'App is disabled by magentomobileshop admin.'));
                endif;

            }catch(\Exception $e){

                echo json_encode(array('status'=>'error','message'=> $this->logger->debug($e->getMessage())));
            }

        }


    private function radToken(){

            return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,25))),1,25);
        }

    public function getSession(){

          $adminSessionLifetime = (int)$this->scopeConfig->getValue('admin/security/session_cookie_lifetime'); 
          if($adminSessionLifetime < 86400)
            $this->resourceConfig->saveConfig('admin/security/session_cookie_lifetime','86400');

          return true;
    }

    private function usersession($username,$password){
     
        if ($this->customerSession->isLoggedIn()){
            return true;
            try 
            {
                if (!$this->customerSession->login ( $username, $password ))
                    return false;
                else
                    return true;
            } 
            catch ( Mage_Core_Exception $e ) 
            {
                return false;
            }
        }
    }

}