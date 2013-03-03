<?php
class GameUser extends AppModel{
	public $name = 'GameUser';
	
	public $belongsTo = array(
		'User' => array(
			'foreignKey' => 'user_id'
		)
	);
}
?>
