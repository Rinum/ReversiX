<?php

require_once CakePlugin::path('API') . 'Vendor' . DS . 'HTMLPurifier' . DS . 'library' . DS . 'HTMLPurifier.auto.php';

class GamesController extends APIAppController {

	//TODO: Lock tables
	
	public $name = 'Games';
	public $uses = array('User','Game','GameUser','GameMove');
	
	public function update(){
		$gameId = $this->request->data('GameId');
		
		$game = $this->Game->find('first',array(
			'conditions' => array(
				'Game.id' => $gameId
			),
			'recursive' => 2
		));
		
		$gamemoves = $this->GameMove->find('all',array(
			'conditions' => array(
				'GameMove.game_id' => $game['Game']['id']
			),
			'group' => 'GameMove.user_id'
		));
		$gbyu = array();
		foreach($gamemoves as $g){
			$gbyu[$g['GameMove']['user_id']] = $g['GameMove'];
		}
		$game['GameMove'] = $gbyu;		
		
		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH,'reversix');
		$socket->connect("tcp://localhost:5555");		
		$socket->send(json_encode(array('type' => 'game', 'data' => $game, 'topic' => $game['Game']['name'])));
	}
	
	public function bot(){
		//world isn't ready for RXBot
		return;
		
		$name = $this->request->data('games');
		foreach($name as $n){
			$name = $n;
			break;
		}
		
		//is this user a player?
		$game = $this->Game->find('first',array(
			'conditions' => array(
				'Game.name' => $name,
				'Game.ended IS NULL'
			),
			'recursive' => 2
		));
		
		if(!$game)
			return;
		
		$is_playing = false;
		foreach($game['GameUser'] as $g){
			if($g['user_id'] == $this->API['Session']['User']['id']){
				$is_playing = true;
				break;
			}
		}
		
		if(!$is_playing)
			return;
		
		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH,'reversix');
		$socket->connect("tcp://localhost:5555");		
		$socket->send(json_encode(array('type' => 'chat', 'user' => $this->API['Session']['User'],'data' => '<span style="color:skyblue">Hi, I\'m <strong>RXBot1000</strong>. I will be taking this user\'s place until he/she/it returns.</span>', 'topic' => $name)));		
	}
	
	public function chat(){
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.Allowed', '');
		$purifier = new HTMLPurifier($config);		
		
		$name = $this->request->data('name');
		$data = $purifier->purify($this->request->data('data'));
		
		if(!$data)
			return;
		
		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH,'reversix');
		$socket->connect("tcp://localhost:5555");		
		$socket->send(json_encode(array('type' => 'chat', 'user' => $this->API['Session']['User'],'data' => $data, 'topic' => $name)));
	}
	
	public function join(){
		$gameId = $this->request->data('GameId');
		
		//find game
		$game = $this->Game->find('first',array(
			'conditions' => array(
				'Game.id' => $gameId
			),
			'recursive' => 1
		));
		
		$num_players = count($game['GameUser']);

		//add player to game
		if($num_players < 4){
			//is the user already part of the game?
			$is_playing = 0;
			foreach($game['GameUser'] as $g){
				if($g['user_id'] == $this->API['Session']['User']['id']){
					$is_playing = 1;
					break;
				}
			}
			
			if(!$is_playing){
				$this->GameUser->create();
				$gu = $this->GameUser->save(array(
					'game_id' => $gameId,
					'user_id' => $this->API['Session']['User']['id']
				));
				
				$board = json_decode($game['Game']['board']);

				$changed = array();
				if($num_players == 0){
					$changed[] = array(9,7);$changed[] = array(7,7);$changed[] = array(7,9);$changed[] = array(9,9);
				} elseif($num_players == 1){
					$changed[] = array(6,7);$changed[] = array(8,7);$changed[] = array(8,9);$changed[] = array(6,9);
				} elseif($num_players == 2){
					$changed[] = array(7,6);$changed[] = array(7,8);$changed[] = array(9,8);$changed[] = array(9,6);
				} elseif($num_players == 3){
					$changed[] = array(6,8);$changed[] = array(8,8);$changed[] = array(8,6);$changed[] = array(6,6);
				}

				foreach($changed as $coord){
					$board[$coord[0]][$coord[1]] = (int)$this->API['Session']['User']['id'];
				}

				if($num_players == 3){
					$started = date('Y-m-d H:i:s');
				}

				//update board
				$this->Game->id = $game['Game']['id'];
				$this->Game->save(array(
					'board' => json_encode($board),
					'started' => @$started
				));
				
				if(@$started){
					//insert initial game moves
					$data = array();
					foreach($game['GameUser'] as $gu){
						$data[] = array(
							'game_id' => $game['Game']['id'],
							'user_id' => $gu['user_id'],
							'attempted' => $started
						);
					}
					$data[] = array(
						'game_id' => $game['Game']['id'],
						'user_id' => $this->API['Session']['User']['id'],
						'attempted' => $started
					);
					
					$this->GameMove->saveMany($data);
				}
				
				$this->update();
				
				return true;
			}
		}
		
		$this->update();
	}
	
	public function over($gameId){
		//update board
		$this->Game->id = $gameId;
		$this->Game->save(array(
			'ended' => date('Y-m-d H:i:s')
		));
		
		//let everyone know the game is over
		$game = $this->Game->find('first',array(
			'conditions' => array(
				'Game.id' => $gameId
			),
			'recursive' => -1
		));
		
		$board = json_decode($game['Game']['board']);
		$counts = array();
		
		foreach($board as $j){
			foreach($j as $k){
				if($k){
					$counts[$k] = (isset($counts[$k]) ? $counts[$k] : 0) + 1;
				}
			}
		}
		
		$winner = 0;
		$max = 0;
		foreach($counts as $key => $c){
			if($c > $max){
				$winner = $key;
				$max = $c;
			}
		}
		
		if($winner){
			$user = $this->User->find('first',array(
				'conditions' => array(
					'User.id' => $winner
				),
				'recursive' => -1
			));
			
			$username = $user['User']['username'];
			
			$context = new ZMQContext();
			$socket = $context->getSocket(ZMQ::SOCKET_PUSH,'reversix');
			$socket->connect("tcp://localhost:5555");
			$socket->send(json_encode(array('type' => 'chat', 'data' => '<div style="color:yellowgreen;margin-top:5px;margin-bottom:5px;font-weight:bold;text-align:center;font-size:14px;">Game over, '.$username.' wins!</div>', 'topic' => $game['Game']['name'])));
			$socket->send(json_encode(array('type' => 'chat', 'data' => '<div style="text-align:center;"><a href="http://reversix.us" style="color:skyblue;margin-top:5px;margin-bottom:5px;font-weight:bold;font-size:14px;">Find a new game?</a></div>', 'topic' => $game['Game']['name'])));
		}
			
		$this->update();
	}
	
	public function is_valid($board,$x,$y){
		$user_id = 0;
		//check North
		for($i=$y-1;$i>=0;$i--){
			if($board[$x][$i]){
				if( $user_id && $user_id != $board[$x][$i] ){
					return 1;		
				} else {
					$user_id = $board[$x][$i];
				}
			} else {
				break;
			}
		}

		$user_id = 0;

		//check South
		for($i=$y+1;$i<16;$i++){
			if( $board[$x][$i] ){
				if( $user_id && $user_id != $board[$x][$i] ){
					return 1;		
				} else {
					$user_id = $board[$x][$i];
				}				
			} else {
				break;
			}
		}

		$user_id = 0;

		//check East
		for($j=$x+1;$j<16;$j++){
			if( $board[$j][$y] ){
				if( $user_id && $user_id != $board[$j][$y] ){
					return 1;		
				} else {
					$user_id = $board[$j][$y];
				}		
			} else {
				break;
			}
		}

		$user_id = 0;

		//check West
		for($j=$x-1;$j>=0;$j--){
			if( $board[$j][$y] ){
				if( $user_id && $user_id != $board[$j][$y] ){
					return 1;		
				} else {
					$user_id = $board[$j][$y];
				}		
			} else {
				break;
			}
		}

		$user_id = 0;

		/////////////////////////////////////

		//check NorthEast
		for($z=1;$z<=min(15-$x,$y);$z++){
			if( $board[$x+$z][$y-$z] ){
				if( $user_id && $user_id != $board[$x+$z][$y-$z] ){
					return 1;		
				} else {
					$user_id = $board[$x+$z][$y-$z];
				}		
			} else {
				break;
			}
		}

		$user_id = 0;

		//check SouthEast
		for($z=1;$z<=min(15-$x,15-$y);$z++){
			if( $board[$x+$z][$y+$z] ){
				if( $user_id && $user_id != $board[$x+$z][$y+$z] ){
					return 1;		
				} else {
					$user_id = $board[$x+$z][$y+$z];
				}		
			} else {
				break;
			}
		}

		$user_id = 0;

		//check NorthWest
		for($z=1;$z<=min($x,$y);$z++){
			if( $board[$x-$z][$y-$z] ){
				if( $user_id && $user_id != $board[$x-$z][$y-$z] ){
					return 1;		
				} else {
					$user_id = $board[$x-$z][$y-$z];
				}		
			} else {
				break;
			}
		}

		$user_id = 0;

		//check SouthWest
		for($z=1;$z<=min($x,15-$y);$z++){
			if( $board[$x-$z][$y+$z] ){
				if( $user_id && $user_id != $board[$x-$z][$y+$z] ){
					return 1;		
				} else {
					$user_id = $board[$x-$z][$y+$z];
				}		
			} else {
				break;
			}
		}

		return 0;
	}
	
	public function attack(){
		$time = date('Y-m-d H:i:s');
		
		$x = intval($this->request->data('x'));
		$y = intval($this->request->data('y'));

		$gameId = $this->request->data('GameId');
		
		//find game
		$game = $this->Game->find('first',array(
			'conditions' => array(
				'Game.id' => $gameId
			),
			'recursive' => 1
		));
		
		$gamemoves = $this->GameMove->find('all',array(
			'conditions' => array(
				'GameMove.game_id' => $game['Game']['id']
			),
			'group' => 'GameMove.user_id'
		));
		$gbyu = array();
		foreach($gamemoves as $g){
			$gbyu[$g['GameMove']['user_id']] = $g['GameMove'];
		}
		$game['GameMove'] = $gbyu;		

		$start = $game['Game']['started'];

		//is the user part of the game?
		$is_playing = 0;
		foreach($game['GameUser'] as $g){
			if($g['user_id'] == @$this->API['Session']['User']['id']){
				$is_playing = 1;
				break;
			}
		}

		if( $is_playing && $start && 
			
			(
					!@$game['GameMove'][$this->API['Session']['User']['id']] 
				||
					(strtotime($time) - 5) >= strtotime($game['GameMove'][$this->API['Session']['User']['id']]['last_attempted'])
			)
			
			){
			$board = json_decode($game['Game']['board']);
			
			if( $board[$x][$y] === 0 ){
				$temp = array();
				$t = array();
				$changed = array();

				//check North
				for($i=$y-1;$i>=0;$i--){
					if( $board[$x][$i] == $this->API['Session']['User']['id'] ){
						$temp = $t;
						break;
					} else if(!$board[$x][$i]){
						$t = array();
						break;
					} else {
						$t[] = array($x,$i);
					}
				}
				$changed = array_merge($changed,$temp);
				$temp = array();$t = array();

				//check South
				for($i=$y+1;$i<16;$i++){
					if( $board[$x][$i] == $this->API['Session']['User']['id'] ){
						$temp = $t;
						break;
					} else if(!$board[$x][$i]){
						$t = array();
						break;
					} else {
						$t[] = array($x,$i);
					}
				}
				$changed = array_merge($changed,$temp);
				$temp = array();$t = array();

				//check East
				for($j=$x+1;$j<16;$j++){
					if( $board[$j][$y] == $this->API['Session']['User']['id'] ){
						$temp = $t;
						break;
					} else if(!$board[$j][$y]){
						$t = array();
						break;
					} else {
						$t[] = array($j,$y);
					}
				}
				$changed = array_merge($changed,$temp);
				$temp = array();$t = array();

				//check West
				for($j=$x-1;$j>=0;$j--){
					if( $board[$j][$y] == $this->API['Session']['User']['id'] ){
						$temp = $t;
						break;
					} else if(!$board[$j][$y]){
						$t = array();
						break;
					} else {
						$t[] = array($j,$y);
					}
				}
				$changed = array_merge($changed,$temp);
				$temp = array();$t = array();

				/////////////////////////////////////

				//check NorthEast
				for($z=1;$z<=min(15-$x,$y);$z++){
					if( $board[$x+$z][$y-$z] == $this->API['Session']['User']['id'] ){
						$temp = $t;
						break;
					} else if(!$board[$x+$z][$y-$z]){
						$t = array();
						break;
					} else {
						$t[] = array($x+$z,$y-$z);
					}
				}
				$changed = array_merge($changed,$temp);
				$temp = array();$t = array();

				//check SouthEast
				for($z=1;$z<=min(15-$x,15-$y);$z++){
					if( $board[$x+$z][$y+$z] == $this->API['Session']['User']['id'] ){
						$temp = $t;
						break;
					} else if(!$board[$x+$z][$y+$z]){
						$t = array();
						break;
					} else {
						$t[] = array($x+$z,$y+$z);
					}
				}
				$changed = array_merge($changed,$temp);
				$temp = array();$t = array();

				//check NorthWest
				for($z=1;$z<=min($x,$y);$z++){
					if( $board[$x-$z][$y-$z] == $this->API['Session']['User']['id'] ){
						$temp = $t;
						break;
					} else if(!$board[$x-$z][$y-$z]){
						$t = array();
						break;
					} else {
						$t[] = array($x-$z,$y-$z);
					}
				}
				$changed = array_merge($changed,$temp);
				$temp = array();$t = array();

				//check SouthWest
				for($z=1;$z<=min($x,15-$y);$z++){
					if( $board[$x-$z][$y+$z] == $this->API['Session']['User']['id'] ){
						$temp = $t;
						break;
					} else if(!$board[$x-$z][$y+$z]){
						$t = array();
						break;
					} else {
						$t[] = array($x-$z,$y+$z);
					}
				}
				$changed = array_merge($changed,$temp);
				$temp = array();$t = array();

			}

			if(!empty($changed)){
				$changed[] = array($x,$y);

				foreach($changed as $coord){
					$board[$coord[0]][$coord[1]] = (int)$this->API['Session']['User']['id'];
				}

				$this->Game->id = $game['Game']['id'];
				$this->Game->save(array(
					'board' => json_encode($board)
				));

				$this->GameMove->create();
				$this->GameMove->save(array(
					'game_id' => $game['Game']['id'],
					'user_id' => $this->API['Session']['User']['id'],
					'attempted' => $time
				));
				
				print 1;

				/* Check for game over */
				if( !$this->recursive_array_search(0, $board) ){
					$this->over($game['Game']['id']);
				} else {
					//check for valid moves... if no valid moves exist, gameover
					$valid = 0;
					for($i=0;$i<16;$i++){
						for($j=0;$j<16;$j++){
							if(!$board[$i][$j]){
								if( $this->is_valid($board,$i,$j) ){
									$valid = 1;
									break;		
								}
							}
						}
						if( $valid ){
							break;
						}
					}
					if(!$valid){
						$this->over($game['Game']['id']);
					}
				}
			}

		}
		
		$this->update();
	}
	
	public function recursive_array_search($needle,$haystack){
		foreach($haystack as $key=>$value) {
			$current_key=$key;
			if($needle===$value OR (is_array($value) && $this->recursive_array_search($needle,$value) !== false)) {
				return true;
			}
		}
		return false;
	}
	
}

?>