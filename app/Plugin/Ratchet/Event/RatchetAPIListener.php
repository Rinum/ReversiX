<?php

App::uses('CakeEventListener', 'Event');
App::uses('ClassRegistry', 'Utilitty');
App::uses('AppController', 'Controller');
App::uses('Router', 'Routing');
App::uses('Dispatcher', 'Routing');

class RatchetAPIListener implements CakeEventListener {

	public function implementedEvents() {
		return array(
		    'Rachet.WampServer.Rpc.API.onClose' => 'onClose'
		);
	}

	public function onClose($event) {
		return;
		
		$request = new CakeRequest('/API/games/bot');
		$request->data['games'] = $event->data['conn']['subscriptions'];
		$request->data['API'] = Configure::read('API');
		$request->data['API']['Session'] = $event->data['conn']['session'];
		$dispatcher = new Dispatcher();
		ob_start();
		$dispatcher->dispatch($request, new CakeResponse());
		$result = ob_get_clean();
		Router::popRequest();
		return $result;		
	}

}