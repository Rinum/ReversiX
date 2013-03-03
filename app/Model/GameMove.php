<?php
class GameMove extends AppModel{
	public $name = 'GameMove';
	public $virtualFields = array(
		'last_attempted' => 'MAX(GameMove.attempted)'
	);
}
?>
