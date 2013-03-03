<?php

App::uses('Shell', 'Console');

class AppShell extends Shell {

	public function main() {
		try{
			$this->run();
		}catch(Exception $e){
			$this->err('No run function found!');
		}
	}

	function dump_array() {
		if (!class_exists('ConnectionManager') || Configure::read('debug') < 2) {
			return false;
		}
		$a = array();
		$noLogs = !isset($logs);
		if ($noLogs):
			$sources = ConnectionManager::sourceList();

			$logs = array();
			foreach ($sources as $source):
				$db = ConnectionManager::getDataSource($source);
				if (!method_exists($db, 'getLog')):
					continue;
				endif;
				$logs[$source] = $db->getLog();
			endforeach;
		endif;

		if ($noLogs || isset($_forced_from_dbo_)):
			foreach ($logs as $source => $logInfo):
				$text = $logInfo['count'] > 1 ? 'queries' : 'query';
				// Write the stats header
				$a[] = array('Database', 'Query Count', 'Query Type', 'Took');
				// Write the stats
				$p = array();
				$p[] = $source;
				$p[] = $logInfo['count'];
				$p[] = $text;
				$p[] = $logInfo['time'] . ' ms';
				$a[] = $p;
				// Write the second header
				$a[] = array('Num', 'Query', 'Affected', 'Num. rows', 'Took (ms)');
				foreach ($logInfo['log'] as $k => $i) :
					$p = array();
					$p[] = ($k + 1);
					$p[] = $i['query'];
					$p[] = $i['affected'];
					$p[] = $i['numRows'];
					$p[] = $i['took'];
					$a[] = $p;
				endforeach;
			endforeach;
		else:
			return false;
		endif;
		return $a;
	}

	/**
	 * Output SQL dump to a CSV file
	 *
	 * @param   string    $fileName       File name to dump to
	 * @return  boolean     True on success, otherwise false
	 */
	function dump_csv($fileName) {
		if (!$fileName) {
			return false;
		}
		$data = $this->dump_array();
		if (!$data) {
			return false;
		}
		$fp = fopen($fileName, 'w');
		if (!$fp) {
			return false;
		}
		foreach ($data as $fields) {
			fputcsv($fp, $fields);
		}
		fclose($fp);
		return true;
	}

}

?>