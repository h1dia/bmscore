<?php

class BmsParser{
	private $legacy_note;
	private $long_note;
	private $md5_hash;
	private $max_measure;
	
	private $header_data;
	private $command_data;
	
	private $lnflag;
	
	public function __construct($string){
		$md5_hash = md5($string);
		// convert
		$string = mb_convert_encoding($string, "UTF-8", "ASCII, SJIS, sjis-win, EUC-JP");
		$string = explode("\n", $string);
		// フラグ初期化
		$lnflag = array_fill(0, 10, false);
		
		for($i = 0; $i < count($string); $i++){
			// コマンドとして有効かチェック
			if(substr($string[$i], 0, 1) != "#"){
				continue 2;
			}
			else{
				$string[$i] = substr($string[$i], 1);
				$command = explode(":", $string[$i], 2);
				// CHANNEL文処理
				if(is_numeric($command[0])){
					parseCommand($string[$i]);
				}
				// HEADER文処理
				else{
					parseHeader($string[$i]);
				}
			}
		}
	}
	
	public function parseNotes(){
		// チャンネル文のソート
		foreach($command_data as $key => $row){
			$temp_arr[$key] = $row['note_pos'];
		}
		array_multisort($temp_arr, SORT_ASC, $command_data);
		
		// LNOBJの確認
		$lnobj = $header_data['LNOBJ'];
		// パース開始
		for($i = 0; $i < count($command_data); $i++){
			$this_keynum = getKeyNum($command_data[$i]['channel']);
			// 該当チャンネルかチェック
			if($this_keynum == null)
				continue;
			
			// 振り分け ロングノーツ
			if($command_data[$i]['channel'] >= 51 || $command_data[$i]['id'] == $lnobj){
				$long_note[$this_keynum][] = $command_data[$i]['note_pos'];
			}
			// レガシーノーツ
			else{
				$legacy_note[$this_keynum][] = $command_data[$i]['note_pos'];
			}
		}
		
		$max_measure = floor($command_data[count($command_data)]['note_pos']);
	}
	
	
	public function outputJson(){
		$total_notes = 0;
		
		// レガシーノーツ処理
		for($ki = 0; $ki < count($legacy_note); $ki++){
			for($i = 0; $i < count($legacy_note[$ki]); $i++){
				// $out_legacy[キーナンバー][該当小節][] = 小節内でのレガシーノートの位置(1未満)
				$out_legacy[$ki][(int)floor($legacy_note[$ki][$i])][] = $legacy_note[$ki][$i] - floor($legacy_note[$ki][$i]);
				$total_notes++;
			}
			
		}
		
		// エラー処理
		if($header_data["TITLE"] == null){
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
		if($header_data["RANDOM"] != null){
			echo '<div class="alert alert-danger">';
			echo "このBMSファイルには#RANDOMが存在したため、アップロードは行われませんでした。 ".date("H:i:s");
			echo "</div>";
			exit();
		}
		
		$output_json = array(
			"GENRE" => $header_data["GENRE"],
			"TITLE" => $header_data["TITLE"],
			"ARTIST" => $header_data["ARTIST"],
			"BPM" => $header_data["BPM"],
			"RANK" => $header_data["RANK"],
			"TOTAL" => $header_data["TOTAL"],
	
			"notes" => $total_notes,
			"measure" => $max_measure,
			"measure_length" => $measure_length,
	
			"lgkey" => $out_legacy,
			/*
			"lnkey" => $lnkey,
			"lnlength" => $ln_pos,
			*/
			/*
			"landmine" => ,
			*/
		);
		
		$file_name = "./json/".$md5_hash.".json";
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
	
	private function parseCommand($string){
		$channel = explode(":", $string[$i], 2);
		
		$this_measure = (int)substr($channel[0], 0, 3);
		$this_channel = (int)substr($channel[0], 3, 2);
		// -1 is delete return char
		$resolution = (strlen($channel[1]) - 1) / 2;
		
		
		// 特殊チャンネル処理
		// 02 : 小節点変更命令
		if($this_channel == 02){
			$measure_length[$now_measure] = (float)$channel[1];
		}
		else {
			for($step = 0; $step < $resolution; $step++){
				$this_id = substr($channel[1], $step * 2, 2);
				if($this_id === "00")
					continue;
					
				$command_data[] = array(
					'note_pos' => ($this_measure + (($resolution - $step) / $resolution)),
					'channel' => $this_channel,
					'id' => $this_id
					);
				
			}
		}
	}
	
	private function parseHeader($string){
		$header = explode(" ", $string, 2);
		$header_data[$header[0]] = $htmlspecialchars(substr($header[1], 0, -1), ENT_QUOTES, "UTF-8");
	}
	
	private function checkLn($channel, $id){
		// キー番号の確認
		$key_num = getKeyNum($channel);
		
		// TODO:混ぜ書き対応
		/*
		51 (LN) 51 51
		LNOBJは必ず終端、ひとつ前の5X,1Xを参照
		終端フラグ $lnflag
		*/
		
		// LN判定
		if($channel >= 51 && $channel <= 59){
			if($lnflag[$key_num]){
				
			}
			else{
				
			}
			//フラグ反転
			$lnflag[$key_num] = !$lnflag[$key_num]; 
		}
	}
	
	private function getKeyNum($channel){
		switch($channel){
			case 16:
				return 0;
				break;
			case 11:
				return 1;
				break;
			case 12:
				return 2;
				break;
			case 13:
				return 3;
				break;
			case 14:
				return 4;
				break;
			case 15:
				return 5;
				break;
			case 18:
				return 6;
				break;
			case 19:
				return 7;
				break;

			case 56:
				return 0;
				break;
			case 51:
				return 1;
				break;
			case 52:
				return 2;
				break;
			case 53:
				return 3;
				break;
			case 54:
				return 4;
				break;
			case 55:
				return 5;
				break;
			case 57:
				return 6;
				break;
			case 58:
				return 7;
				break;
			default:
				return null;
				break;
		}
	}
}


?>
