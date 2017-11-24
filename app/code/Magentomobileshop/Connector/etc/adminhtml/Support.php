<?php
/*namespace Magentomobileshop\Connector\Controller\Support;

class Support extends \Magento\Framework\App\Action\Action
{
   
	public function supportAction()
	{		
		$this->loadLayout();
		$this->_title($this->__("MagentoMobileShop Support"));
		$this->renderLayout();
	}

		public function landingAction() {

		$version = Mage::getConfig()->getModuleConfig('Mss_Connector')->version;
		$session = Mage::getSingleton('core/session')->getAppDatas();

		$array = array();
		$array['version'] = $version;
		$push = array_merge($session,$array);
		$data = json_encode($push); 
  	    $final_data = base64_encode($data);
		  ?>
  		<div class="loading">  <?php
	  		$image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."/frontend/base/default/images/magentomobileshop/magento_logo.png";

	      ?><span class="loading-text">We are activating your new mobile app, Please do not close this window or click the Back button on your browser!!
	      <img class="load_img" src="<?php echo $image ; ?>">
	  		</span>
  		</div>
  		
  		<style>
  		
	  .loading {
	    position: fixed;
	    z-index: 999;
	    height: 2em;
	    width: 2em;
	    overflow: show;
	    margin: auto;
	    top: 0;
	    left: 0;
	    bottom: 0;
	    right: 0;
	  }


	  .loading:before {
	    content: '';
	    display: block;
	    position: fixed;
	    top: 0;
	    left: 0;
	    width: 100%;
	    height: 100%;
	    background-color: rgba(0,0,0,0.3);
	  }

	  .loading:not(:required) {
	    font: 0/0 a;
	    color: transparent;
	    text-shadow: none;
	    background-color: transparent;
	    border: 0;
	  }

	  .loading:not(:required):after {
	    content: '';
	    display: block;
	    font-size: 10px;
	    width: 1em;
	    height: 1em;
	    margin-top: -0.5em;
	    -webkit-animation: spinner 1500ms infinite linear;
	    -moz-animation: spinner 1500ms infinite linear;
	    -ms-animation: spinner 1500ms infinite linear;
	    -o-animation: spinner 1500ms infinite linear;
	    animation: spinner 1500ms infinite linear;
	    border-radius: 0.5em;
	    -webkit-box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.5) -1.5em 0 0 0, rgba(0, 0, 0, 0.5) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
	    box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) -1.5em 0 0 0, rgba(0, 0, 0, 0.75) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
	  }

   

	  .loading span.loading-text {
	    color: #000;
	    display: block;
	    font-size: 17px !important;
	    left: -217px;
	    line-height: 20px;
	    margin-top: 38px;
	    position: absolute;
	    width: 514;
	  }

	
		.loading-text img.load_img {
		    left: 23%;
		    position: absolute;
		    top: -122px;
		    width: 180px;
		}

	  @-webkit-keyframes spinner {
	    0% {
	      -webkit-transform: rotate(0deg);
	      -moz-transform: rotate(0deg);
	      -ms-transform: rotate(0deg);
	      -o-transform: rotate(0deg);
	      transform: rotate(0deg);
	    }
	    100% {
	      -webkit-transform: rotate(360deg);
	      -moz-transform: rotate(360deg);
	      -ms-transform: rotate(360deg);
	      -o-transform: rotate(360deg);
	      transform: rotate(360deg);
	    }
	  }
	  @-moz-keyframes spinner {
	    0% {
	      -webkit-transform: rotate(0deg);
	      -moz-transform: rotate(0deg);
	      -ms-transform: rotate(0deg);
	      -o-transform: rotate(0deg);
	      transform: rotate(0deg);
	    }
	    100% {
	      -webkit-transform: rotate(360deg);
	      -moz-transform: rotate(360deg);
	      -ms-transform: rotate(360deg);
	      -o-transform: rotate(360deg);
	      transform: rotate(360deg);
	    }
	  }
	  @-o-keyframes spinner {
	    0% {
	      -webkit-transform: rotate(0deg);
	      -moz-transform: rotate(0deg);
	      -ms-transform: rotate(0deg);
	      -o-transform: rotate(0deg);
	      transform: rotate(0deg);
	    }
	    100% {
	      -webkit-transform: rotate(360deg);
	      -moz-transform: rotate(360deg);
	      -ms-transform: rotate(360deg);
	      -o-transform: rotate(360deg);
	      transform: rotate(360deg);
	    }
	  }
	  @keyframes spinner {
	    0% {
	      -webkit-transform: rotate(0deg);
	      -moz-transform: rotate(0deg);
	      -ms-transform: rotate(0deg);
	      -o-transform: rotate(0deg);
	      transform: rotate(0deg);
	    }
	    100% {
	      -webkit-transform: rotate(360deg);
	      -moz-transform: rotate(360deg);
	      -ms-transform: rotate(360deg);
	      -o-transform: rotate(360deg);
	      transform: rotate(360deg);
	    }
	  }
  		</style>

  		<?php

  		echo "<script src='//code.jquery.com/jquery-latest.js'></script>  
  		<script type='text/javascript'>
  			jQuery(document).ready(function() {
  				window.location.href = 'https://www.magentomobileshop.com/user/activating-app?app=".$final_data."' ;
  				
  			});	
  		</script>";
    	}

    }*/


