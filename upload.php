<?php
// bms2json
// TODO : 小節、BPM、STOP、LN
include_once('mysql.php');

$db = new ConnectMysql();

mb_internal_encoding("utf-8");
date_default_timezone_set('Asia/Tokyo');

if(is_uploaded_file($_FILES["file"]["tmp_name"])){
	$get_file = $_FILES["file"]["tmp_name"];
	
	// load bms file
	$bms_data = file_get_contents($get_file);
	$file_hash = md5($bms_data);

	// err_check
	if(!is_string($bms_data)){
		echo '<div class="alert alert-danger">';
		echo "ファイルがBMSじゃないような気がします。 ".date("H:i:s");
		echo "</div>";
		exit();
	}

	// convert utf8
	$bms_data = mb_convert_encoding($bms_data, "UTF-8", "ASCII, SJIS, sjis-win, EUC-JP");

	$bms_string_array = explode("\n", $bms_data);
	$file_name = "./json/".$file_hash.".json";

	$total_notes = 0;
	$max_measure = 0;
	
	// chk LNOBJ
	for($i = 0; $i < count($bms_string_array); $i++){
		if(substr($bms_string_array[$i], 0, 6) == "#LNOBJ"){
			$lnobj_array[substr($bms_string_array[$i], 7, 2)] = true;
		}
	}
	
	//parse bms2json
	for($i = 0; $i < count($bms_string_array); $i++){
		// chk command
		if(substr($bms_string_array[$i], 0, 1) != "#")
			continue;
			
		else{
			$temp_str = substr($bms_string_array[$i], 1);
			$channel = explode(":", $temp_str, 2);

			if(is_numeric($channel[0])){
				$before_measure = $now_measure;
				$now_measure = (int)substr($channel[0], 0, 3);
				$max_measure = (int)max($now_measure, $max_measure);
				
				// -1 is delete return char
				$resolution = (strlen($channel[1]) - 1) / 2;
				
				if((int)substr($channel[0], 3, 2) == 2){
					$measure_length[$now_measure] = (float)$channel[1];
				}
				
				for($step = 0; $step < $resolution; $step++){
					if(!(substr($channel[1], $step * 2, 2) === "00")){
						
						// if this command is visible note
						if(substr($channel[0], 3, 2) >= 11 && substr($channel[0], 3, 2) <= 59){
							
							$before_note_pos = $note_pos;
							$note_pos = (($resolution - $step) / $resolution);
							// if this id is not lnobj
							if($lnobj_array[substr($channel[1], $step * 2, 2)] != true){
								$total_notes++;
								
								switch (substr($channel[0], 3, 2)){
								case 16:
									$key[0][(int)$now_measure][] = $note_pos;
									continue 2;
								case 11:
									$key[1][(int)$now_measure][] = $note_pos;
									continue 2;
								case 12:
									$key[2][(int)$now_measure][] = $note_pos;
									continue 2;
								case 13:
									$key[3][(int)$now_measure][] = $note_pos;
									continue 2;
								case 14:
									$key[4][(int)$now_measure][] = $note_pos;
									continue 2;
								case 15:
									$key[5][(int)$now_measure][] = $note_pos;
									continue 2;
								case 18:
									$key[6][(int)$now_measure][] = $note_pos;
									continue 2;
								case 19:
									$key[7][(int)$now_measure][] = $note_pos;
									continue 2;
								case 56:
									$lnkey[0][] = (int)$now_measure + $note_pos;
									continue 2;
								case 51:
									$lnkey[1][]= (int)$now_measure + $note_pos;
									continue 2;
								case 52:
									$lnkey[2][] = (int)$now_measure + $note_pos;
									continue 2;
								case 53:
									$lnkey[3][] = (int)$now_measure + $note_pos;
									continue 2;
								case 54:
									$lnkey[4][] = (int)$now_measure + $note_pos;
				
									continue 2;
								case 55:
									$lnkey[5][] = (int)$now_measure + $note_pos;
									continue 2;
								case 58:
									$lnkey[6][] = (int)$now_measure + $note_pos;
									continue 2;
								case 59:
									$lnkey[7][] = (int)$now_measure + $note_pos;
									continue 2;
								}
							}
							// LNOBJ
							else{
								switch (substr($channel[0], 3, 2)){
								case 16:
									$lnkey[0][] = $before_note_pos + $before_measure;
									$lnkey[0][] = (int)$now_measure + $note_pos;
								continue 2;
								case 11:
									$lnkey[1][] = $before_note_pos + $before_measure;
									$lnkey[1][] = (int)$now_measure + $note_pos;
									continue 2;
								case 12:
									$lnkey[2][] = $before_note_pos + $before_measure;
									$lnkey[2][] = (int)$now_measure + $note_pos;
									continue 2;
								case 13:
									$lnkey[3][] = $before_note_pos + $before_measure;
									$lnkey[3][] = (int)$now_measure + $note_pos;
									continue 2;
								case 14:
									$lnkey[4][] = $before_note_pos + $before_measure;
									$lnkey[4][] = (int)$now_measure + $note_pos;
									continue 2;
								case 15:
									$lnkey[5][] = $before_note_pos + $before_measure;
									$lnkey[5][] = (int)$now_measure + $note_pos;
									continue 2;
								case 18:
									$lnkey[6][] = $before_note_pos + $before_measure;
									$lnkey[6][] = (int)$now_measure + $note_pos;
									continue 2;
								case 19:
									$lnkey[7][] = $before_note_pos + $before_measure;
									$lnkey[7][] = (int)$now_measure + $note_pos;
									continue 2;
								}
							}
						}
						
					}
				}


			}
			// HEADER 処理
			else{
				$header = explode(" ", $temp_str, 2);
				$header_map[$header[0]] = htmlspecialchars(substr($header[1], 0, -1), ENT_QUOTES, "UTF-8");
			}
		}
	}

	if($header_map["TITLE"] == null){
		echo '<div class="alert alert-danger">';
		echo "このBMSファイルには#TITLEが存在しなかったため、アップロードは行われませんでした。 ".date("H:i:s");
		echo "</div>";
		exit();
	}
	if($total_notes <= 0){
		echo '<div class="alert alert-danger">';
		echo "このBMSファイルには可視ノーツが1つも存在しなかったため、アップロードは行われませんでした。 ".date("H:i:s");
		echo "</div>";
		exit();
	}
	if($header_map["RANDOM"] != null){
		echo '<div class="alert alert-danger">';
		echo "このBMSファイルには#RANDOMが存在したため、アップロードは行われませんでした。 ".date("H:i:s");
		echo "</div>";
		exit();
	}
	
	for($key_num = 0; $key_num <= 7; $key_num++){
		sort($lnkey[$key_num]);
		
		for($i = 0; $i < count($lnkey[$key_num]); $i += 2){
			$ln_start_measure = floor($lnkey[$key_num][$i]);
			$ln_end_measure = floor($lnkey[$key_num][$i + 1]);
			
			for($now_measure = $ln_start_measure; $now_measure <= $ln_end_measure; $now_measure++){
				if($ln_start_measure != $now_measure && $ln_end_measure != $now_measure){
					$ln_pos[$key_num][$now_measure]["start"][] = 0.0;
					$ln_pos[$key_num][$now_measure]["end"][] = 1.0;
				}
				else if($ln_start_measure == $now_measure && $ln_end_measure != $now_measure){
					(double)$ln_pos[$key_num][$now_measure]["start"][] = (double)($lnkey[$key_num][$i] - floor($lnkey[$key_num][$i]));
					(double)$ln_pos[$key_num][$now_measure]["end"][] = 1.0;
				}
				else if($ln_start_measure != $now_measure && $ln_end_measure == $now_measure){
					(double)$ln_pos[$key_num][$now_measure]["start"][] = 0.0;
					(double)$ln_pos[$key_num][$now_measure]["end"][] = (double)($lnkey[$key_num][$i + 1] - (double)floor($lnkey[$key_num][$i + 1]));
				}
				else if($ln_start_measure == $now_measure && $ln_end_measure == $now_measure){
					(double)$ln_pos[$key_num][$now_measure]["start"][] = (double)($lnkey[$key_num][$i] - (double)floor($lnkey[$key_num][$i]));
					(double)$ln_pos[$key_num][$now_measure]["end"][] = (double)($lnkey[$key_num][$i + 1] - (double)floor($lnkey[$key_num][$i + 1]));
				}
			}
		}
	}
	
	//out json
	$output_json = array(
		"GENRE" => $header_map["GENRE"],
		"TITLE" => $header_map["TITLE"],
		"ARTIST" => $header_map["ARTIST"],
		"BPM" => $header_map["BPM"],
		"RANK" => $header_map["RANK"],
		"TOTAL" => $header_map["TOTAL"],

		"notes" => $total_notes,
		"measure" => $max_measure,
		"measure_length" => $measure_length,

		"lgkey" => $key,
		"lnkey" => $lnkey,
		"lnlength" => $ln_pos,
		
		/*
		"landmine" => ,
		*/

		"difficulty" => $difficulty,
	);
	
	// db
	
	$dbtemp = file_get_contents("./db.json");
	$out = json_decode($dbtemp, true);
	
	$table_json = array(
		"TITLE" => $header_map["TITLE"],
		"ARTIST" => $header_map["ARTIST"],
		"md5" => $file_hash,
	);
	
	$table_cnt = 0;
	$table_flag = false;
	while($out[$table_cnt]["md5"] != null){
		if($out[$table_cnt]["md5"] == $file_hash){
			$table_flag = true;
			break;
		}
	}
	
	if($table_flag)
		$out[$table_cnt] = $table_json;
	else
		$out[] = $table_json;
		
	file_put_contents("./db.json", json_encode($out));
	
	// end of db

	$file_upload_success = file_put_contents($file_name, json_encode($output_json));
	if(!($file_upload_success === FALSE)){
		echo '<div class="alert alert-success">';
		echo "アップロードに成功しました。 : ".$header_map["TITLE"].", ".date("H:i:s")."<br />";
		echo "<a href=\""."./score.php?md5=".$file_hash."\">"."Score"."</a>";
		echo '</div>';
	}
	else{
		echo '<div class="alert alert-danger">';
		echo "譜面のアップロードに失敗しました。<br />しばらく時間をおいてからもう一度試してみてください。 ".date("H:i:s");
		echo '</div>';
	}
}

else{
	echo '<div class="alert alert-danger">';
	echo "ファイルが選択されていません";
	echo "</div>";
}

?>
