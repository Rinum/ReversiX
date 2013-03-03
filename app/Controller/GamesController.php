<?php

class GamesController extends AppController {

	public $name = 'Games';
	public $uses = array('User','Game','GameUser','GameMove');
	
	public function index($name = '') {
		$game = $this->find_game($name);
		
		//get last game moves and organize by user
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
		
		//official game name (as seen in db)
		$name = $game['Game']['name'];
		
		$this->set('game',$game);
		$this->set('name',$name);
	}

	public function find_game($name){
		//did the user specify a game?
		if($name){
			$name = substr(preg_replace("/[^a-zA-Z0-9]/", "", $name),0,32);
			$game = $this->Game->find('first',array(
				'conditions' => array(
					'Game.name' => $name
				),
				'recursive' => 2
			));
			if($game) return $game;
			
			return $this->create($name);
		}

		//does the user belong to a game?
		/*
		$game = $this->Game->find('first',array(
			'conditions' => array(
				'Game.ended IS NULL',
				'Game.board LIKE' => '%'.$this->Session->read('User.id').'%' //does this user still have pieces in play?
			),
			'joins' => array(
				array(
					'table' => 'game_users',
					'alias' => 'GameUser',
					'type' => 'INNER',
					'conditions' => array(
						'Game.id = GameUser.game_id',
						'GameUser.user_id' => $this->Session->read('User.id')
					)
				)
			),
			'recursive' => 2
		));
		if($game) return $game;
		 * 
		 */
		
		//user doesn't belong to a game and no game specified... join any open game!
		$game = $this->Game->find('first',array(
			'conditions' => array(
				'Game.started IS NULL'
			),
			'order' => 'Game.id ASC',
			'recursive' => 2
		));
		if($game) return $game;
		
		//no open games... make one!
		return $this->create();
	}
	
	public function create($name = null){
		//create game name if not specified
		if(!$name) $name = substr(base_convert(rand(),10,36),0,32);
		
		//initialize empty board
		$board = array();
		
		for($i=0;$i<16;$i++)
			for($j=0;$j<16;$j++)
				$board[$i][$j] = 0;

		$board = json_encode($board);

		$this->Game->create();
		$game = $this->Game->save(array(
			'name' => $name,
			'board' => $board
		));
		
		$game['GameUser'] = array();
		
		return $game;
	}
	
}

?>