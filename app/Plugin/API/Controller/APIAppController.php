<?php

class APIAppController extends AppController {

	var $API = array();
	
	public function beforeFilter() {
		$this->autoRender = false;
		$this->autoLayout = false;
		
		$this->access();
	}
	
	public function access(){		
		//Is an app trying to access the API?
		if(Configure::read('API.key') == $this->request->data('API.key')){
			//Set API data			
			$this->API = $this->request->data('API');
			
			return;
		}
		
		throw new NotFoundException();
	}

}