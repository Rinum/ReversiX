<?php
class Game extends AppModel{
	public $name = 'Game';
	
	public $hasMany = array(
		'GameUser' => array(
			'order' => 'GameUser.id ASC',
			'limit' => 4
		)
	);
}
?>
