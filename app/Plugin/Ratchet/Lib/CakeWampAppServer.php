<?php

use Ratchet\ConnectionInterface as Conn;

class CakeWampAppServer implements Ratchet\Wamp\WampServerInterface {

	private $shell;
	private $loop;
	protected $connections = array();
	protected $topics = array();

	public function __construct($shell, $loop) {
		$this->shell = $shell;
		$this->loop = $loop;
		
		/*
		CakeEventManager::instance()->dispatch(new CakeEvent('Rachet.WampServer.construct', $this, array(
			'loop' => $this->loop,
		)));
		 * 
		 */
	}

	public function onPublish(Conn $conn, $topic, $event, array $exclude, array $eligible) {
		return;
		
		if($this->topics[$topic->getId()])
			$this->topics[$topic->getId()]->broadcast($event);
	}
	
	public function update($event){
		$event = json_decode($event,true);
		if($event['topic'] && isset($this->topics[$event['topic']]))
			$this->topics[$event['topic']]->broadcast($event);
	}

	public function onCall(Conn $conn, $id, $topic, array $params) {
		CakeEventManager::instance()->dispatch(new CakeEvent('Rachet.WampServer.Rpc.' . $topic->getId(), $this, array(
		    'connection' => $conn,
		    'id' => $id,
		    'topic' => $topic,
		    'params' => $params,
		    'connectionData' => $this->connections[$conn->WAMP->sessionId],
		)));
	}

	public function onSubscribe(Conn $conn, $topic) {
		if (!isset($this->topics[$topic->getId()])) {
			$this->topics[$topic->getId()] = $topic;
		}

		//are there any other connections with the same user id?
		$sess = $conn->Session->all();
		$num = 0;
		foreach($this->connections as $c){
			if(!@$c['subscriptions'][$topic->getId()])
				continue;
			
			if($c['session']['User']['id'] == $sess['User']['id'])
				$num++;
			
			if($num > 0)
				break;
		}
		
		if($num < 1){

			$this->topics[$topic->getId()]->broadcast(array(
				'type' => 'chat',
				'data' => '<strong>'.$sess['User']['username'].'</strong> is here.'
			));
			
		}
		
		$this->connections[$conn->WAMP->sessionId]['subscriptions'][$topic->getId()] = $topic->getId();
	}

	public function onUnSubscribe(Conn $conn, $topic) {
		if ($this->topics[$topic->getId()]->count() > 0) {
			unset($this->topics[$topic->getId()]);
		}
		
		unset($this->connections[$conn->WAMP->sessionId]['subscriptions'][$topic->getId()]);
	}

	public function onOpen(Conn $conn) {
		if(!$conn->Session->all()){
			//disconnect user
			$conn->close();
		}
		
		$this->connections[$conn->WAMP->sessionId] = array(
		    'session' => $conn->Session->all(),
		    'conn' => $conn,
		    'subscriptions' => array()
		);
		
		/*
		CakeEventManager::instance()->dispatch(new CakeEvent('Rachet.WampServer.construct', $this, array(
			'loop' => $this->loop,
		)));
		 * 
		 */
	}

	public function onClose(Conn $conn) {
		//are there any other connections with the same user id?
		$sess = $conn->Session->all();
		foreach($this->connections[$conn->WAMP->sessionId]['subscriptions'] as $s){
			$topic = $s;
			break;
		}
		$num = 0;
		foreach($this->connections as $c){
			if(!@$c['subscriptions'][@$topic])
				continue;
			
			if($c['session']['User']['id'] == $sess['User']['id'])
				$num++;
			
			if($num > 1)
				break;
		}
		
		if($num < 2){
			CakeEventManager::instance()->dispatch(new CakeEvent('Rachet.WampServer.Rpc.API.onClose', $this, array(
				    'conn' => $this->connections[$conn->WAMP->sessionId]
			)));

			foreach($this->connections[$conn->WAMP->sessionId]['subscriptions'] as $k => $v){
				if($this->topics[$k]){
					$this->topics[$k]->broadcast(array(
						'type' => 'chat',
						'data' => '<strong>'.$sess['User']['username'].'</strong> has left.'
					));				
				}
			}
		}

		unset($this->connections[$conn->WAMP->sessionId]);
	}

	public function onError(Conn $conn, \Exception $e) {
		
	}

}