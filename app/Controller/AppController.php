<?php

App::uses('CakeSession','Model/Datasource');

class AppController extends Controller {
	var $components = array('RequestHandler', 'Session');
	var $helpers = array('Html', 'Session');
        var $uses = array('User','Game','GameUser');
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		//$this->Session->id('0jvnjsrd4opj32odbjmqqb8521');
		//$memcache = new Memcache;
		//$memcache->connect('127.0.0.1', '11211');
		//debug($memcache->get('reversix_0jvnjsrd4opj32odbjmqqb8521'));
				
		$this->access();
	}

	public function afterFilter() {
		parent::afterFilter();
	}

	public function beforeRender() {
		parent::beforeRender();
	}
	
	public function access(){
		//is the user logged in?
		if(!$this->Session->read('User.id')){
			$this->create_user();
		} else {
			//already heading to a game?
			if($this->params['controller'] == 'games')
				return;
			
			$this->redirect('/games/index');
		}
	}
	
	public function create_user(){
		$user = null;
		
		while(!$user){
			$username = $this->User->generate_username();

			//check if this user exists
			$suffix = $this->User->find('count',array(
				'conditions' => array(
					'User.username LIKE' => $username.'%'
				)
			));
			
			if($suffix)
				$username .= ' '.$this->User->romanize($suffix + 1);
			
			try{
				$this->User->create();
				$this->User->set(array(
					'username' => $username 
				));
				$user = $this->User->save();
			}catch(Exception $e){}
		}
		
		$this->Session->write($user);
	}	
}

?>
