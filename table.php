<?php

class DifficultyTable{
    function getDifficulty($table_dir, $md5){
	    $header = file_get_contents($table_dir.'header.json');
	    $data = file_get_contents($table_dir.'data.json');
		$info = json_decode($header);
		$table = json_decode($data);
	
		for($i = 0; $i < count($table); $i++){
			if($table[$i]->md5 === $md5){
			    $str = $header[0]->name.$table[$i]->level;
				return $str;
			}
		}
    }
    
}
?>