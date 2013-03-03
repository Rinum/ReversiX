<?php
	Router::connect('/api/:controller/:action/*', array('plugin' => 'API'));
	Router::connect('/API/:controller/:action/*', array('plugin' => 'API'));
?>
