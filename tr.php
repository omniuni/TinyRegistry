<?php
class tinyRegistry{

	private $registry = null;
	private $dbObject;
	
	function __construct(){
		try{
			$this->dbObject = new SQLite3("tinyregistry.db");
		}catch(PDOException $e){
			echo("Caught Exception: $e");
		}
	}

	function open($registry){
		$this->registry = $registry;
		// Open the Registry specified, set the private variable, create if it does not exist
		try{
			$this->dbObject->exec("CREATE TABLE {$this->registry}(id INTEGER PRIMARY KEY, key TEXT, value TEXT);");
		}catch(PDOException $e){
			echo("Caught Exception: $e");
		}
	}
	
	function registrylist(){
		$list = $this->dbObject->query("SELECT * FROM sqlite_master WHERE type='table' ORDER BY name;");
		$currentResult = $list->fetchArray();
		$results = null;
			while($currentResult['name'] != null){
				$results[] = $currentResult;
				$currentResult = $list->fetchArray();
			}
		return $results;
	}
	
	function registrydrop($registryname){
		$this->dbObject->exec("DROP TABLE $registryname;");
		return true;
	}

	function push($key, $value, $overwrite = true){
		$key = sqlite_escape_string($key);
		$value = sqlite_escape_string($value);
		if($this->registry == null) return false;
			$results = $this->dbObject->query("SELECT * FROM {$this->registry} WHERE key='$key';");
			$resultsArray = $results->fetchArray();
		if($overwrite === false){ //check if there is already a value
			if($resultsArray['id'] != null){
				return false;
			}else{
				$this->dbObject->exec("INSERT INTO {$this->registry} (key, value) VALUES ('$key','$value');");
				return true;
			}
		}else{
			if($resultsArray['id'] != null){
				$this->dbObject->exec("UPDATE {$this->registry} SET value='$value' WHERE key='$key';");
				return true;
			}else{
				$this->dbObject->exec("INSERT INTO {$this->registry} (key, value) VALUES ('$key','$value');");
				return true;
			} 
		}
	}

	function pull($result = true, $id=null, $filter='%', $order='DESC'){
		if($this->registry == null) return false;
		//if id is null, use filter otherwise use provided id
		//result true returns array, false, deletes items that match, string returns template		
		if(ctype_digit($id)){
			$pull = $this->dbObject->query("SELECT * FROM {$this->registry} WHERE id='$id';");
		}else{
			$pull = $this->dbObject->query("SELECT * FROM {$this->registry} WHERE key LIKE '$filter' UNION SELECT * FROM {$this->registry} WHERE value LIKE '$filter' ORDER BY key,value $order;");
		}
		$currentResult = $pull->fetchArray();
		while($currentResult['id'] != null){
			$results[] = $currentResult;
			$currentResult = $pull->fetchArray();
		}
		if($result === true){
			return $results;
		}elseif($result === false){
			if(ctype_digit($id)){
				$this->dbObject->exec("DELETE FROM {$this->registry} WHERE id=$id;"); return true;
			}
		}else{
			$returnString = null;
			foreach($results as $resultItem){
				$current = str_replace('%i%', $resultItem['id'], $result);
				$current = str_replace('%k%', $resultItem['key'], $current);
				$current = str_replace('%v%', $resultItem['value'], $current);
				$returnString.= $current;
			}
			return $returnString;
		}
	}
}
?>