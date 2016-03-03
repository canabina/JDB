<?php 

class Jdb
{

	public $hashId;

	public $currentWaitLine;

	public $config;

	function __construct($config = [])
	{
		if ($config) {

			if (!file_exists($config['dbFile'])){
				$this->afterExec();
				throw new Exception("File db is not define", 1);		
			}
			else{
				$this->config = $config;
				$this->hashId = md5(time().rand(0, 9999999));
				$this->db = json_decode(file_get_contents($config['dbFile']));
			}


			$this->beforeExec();
		}else{
			$this->afterExec();
			throw new Exception("Config is empty", 1);
		}
	}

	public function beforeExec(){
		$handle = fopen(__DIR__.'/exec_line', 'a');
		fwrite($handle, $this->hashId.'|');
		$break = true;
		while ($break) {
			$t = explode('|', file_get_contents(__DIR__.'/exec_line'));
			if ($t[0] == $this->hashId) 
				return true;
		}
	}

	public function afterExec(){
		$t = explode('|', file_get_contents(__DIR__.'/exec_line'));
		unset($t[0]);
		file_put_contents(__DIR__.'/exec_line', implode('|', $t));
	}

	public function getJsonArray(){
		return json_decode(file_get_contents($this->config['dbFile']));
	}

	public function getTable($tableName){
		return isset($this->db->$tableName) ? $this->db->$tableName : false; 
	}

	public function insert($tableName = false, $dataInsert = false){

		if ($tableName && $dataInsert) {
			$data = $this->getTable($tableName);
			if ($data) {
				$error = false;
				foreach ($dataInsert as $key => $value) {
					if (!isset($data->columns->$key)) 
						$error = true;
				}

				if ($error) {
					$this->afterExec();
					throw new Exception("Columns is not correct", 1);
				}
				else{
					$dataInsert['id'] = !$data->data ? 1 : max($data->data)->id++;
					$data->data[] = $dataInsert;
				}
			}
		}
	}

	public function select($tableName = false, $where = []){
		if ($tableName) {
			$table = $this->getTable($tableName);
			if ($table) {
				if ( count($where))  {
					$returnData = [];
					foreach ($where as $key => $value) {
						$glue = explode('[', $key);
						$expression = explode(']', $glue[1])[0];
						if ($expression) {
							foreach ($table->data as $tableKey => $tableValue) {
								if ($expression == '==') {
									if ($tableValue->$glue[0] == $value) {
										$returnData[] = $tableValue;
									}
								}
								if ($expression == '!=') {
									if ($tableValue->$glue[0] != $value) {
										$returnData[] = $tableValue;
									}
								}
							}	
						}
					}
					asort($returnData);
					return $returnData;
				}else{
					echo "string";
					return isset($table->data) ? $table->data : false;
				}
			}
		}
	}

	public function delete($tableName = false, $where = []){
		if ($tableName) {
			$table = $this->getTable($tableName);
			if ($table) {
				if ( count($where))  {
					$returnData = [];
					foreach ($where as $key => $value) {
						$glue = explode('[', $key);
						$expression = explode(']', $glue[1])[0];
						if ($expression) {
							foreach ($table->data as $tableKey => $tableValue) {
								if ($expression == '==') {
									if ($tableValue->$glue[0] != $value) {
										$returnData[] = $tableValue;
									}
								}
								if ($expression == '!=') {
									if ($tableValue->$glue[0] == $value) {
										$returnData[] = $tableValue;
									}
								}
							}	
						}
					}
					asort($returnData);
					$this->db->$tableName->data = $returnData;
				}else{
					$this->db->$tableName->data = [];
				}
			}
		}
	}

	public function update($tableName = false, $update = [], $where = []){
		if ($tableName) {
			$table = $this->getTable($tableName);
			if ($table && count($update) ) {
				if ( count($where) )  {
					$returnData = [];
					foreach ($where as $key => $value) {
						$glue = explode('[', $key);
						$expression = explode(']', $glue[1])[0];
						if ($expression) {
							foreach ($table->data as $tableKey => $tableValue) {
								if ($expression == '==') {
									if ($tableValue->$glue[0] == $value) {
										foreach ($update as $updateKey => $updateValue) {
											if ( $tableValue->$updateKey ) {
												$tableValue->$updateKey = $updateValue;
											}
										}
										$returnData[] = $tableValue;
									}
								}
								if ($expression == '!=') {
									if ($tableValue->$glue[0] != $value) {
										foreach ($update as $updateKey => $updateValue) {
											if ( $tableValue->$updateKey ) {
												$tableValue->$updateKey = $updateValue;
											}
										}
										$returnData[] = $tableValue;
									}
								}
							}	
						}
					}
					$this->db->$tableName->data = $table->data;
				}else{
					foreach ($table->data as $tableKey => $tableValue) {
						foreach ($update as $updateKey => $updateValue) {
							if ( $tableValue->$updateKey ) {
								$tableValue->$updateKey = $updateValue;
							}
						}
					}
					$this->db->$tableName->data = $table->data;
				}
			}
		}
	}

	public function createTable($tableData){
		$table = $this->db;
		if (isset($tableData['tableName'])) {
			if (!isset($table[$tableData['tableName']])) {
				$table[$tableData['tableName']]['columns'] = array_flip($tableData['columns']); 
			}
		}
		$this->db = $table;
	}

	public function updateJson($array){
		file_put_contents($this->config['dbFile'], json_encode($array));
	}

	function __destruct(){
		$this->updateJson($this->db);
		$this->afterExec();
	}
}
