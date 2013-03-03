<?php

App::uses('CakeEventListener', 'Event');
App::uses('ClassRegistry', 'Utilitty');
App::uses('AppController', 'Controller');
App::uses('Router', 'Routing');
App::uses('Dispatcher', 'Routing');

class ReversiBotShell extends AppShell {

	private $bots = array();

	public $uses = array('User','Game');

	public function run() {
		$this->out('ReversiX Bot');
		$this->hr();		
		
		//create users
		$this->create_users();
		
		//seed rand
		srand((float) time());
		
		while(true){
			//find open games that need filling
			$this->find_game();
			
			//don't want to make too many moves... sleep for a bit
			sleep(rand(5,10));			
			
			//make moves in games that have been joined
			$this->play();
		}
	}
	
	public function play(){
		foreach($this->bots as $bot_id => $b){			
			foreach($b['Games'] as $id => $g){
				$game = $this->Game->find('first',array(
					'conditions' => array(
						'Game.id' => $id
					),
					'recursive' => -1
				));
				
				if(!$game || $game['Game']['ended']){
					unset($this->bots[$bot_id]['Games'][$id]);
					continue;
				}		
				
				if(!$game['Game']['started'])
					continue;
				
				$board = json_decode($game['Game']['board']);
				
				$this->find_move($bot_id, $game['Game']['id'], $b, $board);
			}
			
			sleep(1);
		}
	}
	
	public function find_move($bot_id,$game_id,$user,$board){
		//find a suitable move
		foreach($board as $row => $r){
			foreach($r as $col => $p){
				//skip occupied squares
				if($p !== 0)
					continue;
				
				//50% chance we try another square to attack
				if(rand()%2)
					continue;
				
				//find an empty square that may be attacked
				$attack = false;
				$successful = false;
				
				$neighbors = array(
					'n' => @$board[$row + 1][$col],
					's' => @$board[$row - 1][$col],
					'e' => @$board[$row][$col + 1],
					'w' => @$board[$row][$col - 1],
					'ne' => @$board[$row + 1][$col + 1],
					'nw' => @$board[$row + 1][$col - 1],
					'se' => @$board[$row - 1][$col + 1],
					'sw' => @$board[$row - 1][$col - 1]
				);
				
				foreach($neighbors as $n){
					if($n){
						$attack = true;
						break;
					}
				}
				
				if($attack)
					$successful = $this->request('/API/games/attack', array('GameId' => $game_id, 'x' => $row, 'y' => $col), $user);
				
				if($successful)
					return;
			}
		}
	}
	
	public function find_game(){
		foreach($this->bots as &$b){
			//don't join more than this many games
			$max_games = @$this->args[1] ? $this->args[1] : 1;
			
			if(count($b['Games']) >= $max_games)
				continue;
			
			$game = $this->Game->find('first',array(
				'conditions' => array(
					'Game.started IS NULL'
				),
				'order' => 'Game.id ASC',
				'recursive' => -1
			));
			
			if($game && !isset($b['Games'][$game['Game']['id']])){
				//Try to join game
				$joined = $this->request('/API/games/join', array('GameId' => $game['Game']['id']), $b);
				if($joined)
					$b['Games'][$game['Game']['id']] = $game['Game']['id'];
			}
			
			//don't make it too obvious that these are bots
			sleep(rand(1,9));
		}
	}
	
	public function create_users(){
		$num_users = @$this->args[0] ? $this->args[0] : 1;
		
		for($i = 0; $i < $num_users; $i++){
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
			
			$this->bots[$user['User']['id']] = $user;
			$this->bots[$user['User']['id']]['Games'] = array();			
		}
	}
	
	public function request($url, $data, $user) {
		$request = new CakeRequest($url);
		$request->data = $data;
		$request->data['API'] = Configure::read('API');
		$request->data['API']['Session'] = $user;
		$dispatcher = new Dispatcher();
		ob_start();
		$dispatcher->dispatch($request, new CakeResponse());
		$result = ob_get_clean();
		Router::popRequest();
		return $result;
	}
	
}

?>
