<?php

class AppModel extends Model {
	
	function lockTableRW() {

		$this->lockTable('READ');

		$this->lockTable('WRITE');

	}

	function lockTable($type = 'READ') {

		$dbo = $this->getDataSource();

		$dbo->execute('LOCK TABLES '.$this->table.' '.$type);

	}

	function unlockTables() {

		$dbo = $this->getDataSource();

		$dbo->execute('UNLOCK TABLES');

	}

}

?>