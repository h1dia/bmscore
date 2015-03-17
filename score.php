<html>

<head>
<style type="text/css"><!-- div,span,div img{position:absolute;}td,th{font-size:16px;}body{color:white;background:black} --></style>
<link type="text/css" rel="stylesheet" href="css/style.css"/>
</head>
<body>
<?php

header("Content-type: text/html; charset=utf-8");
// json2score

// isset

if(isset($_GET["md5"])){
	$md5 = $_GET["md5"];
	$md5_flag = true;
}
else{
	$md5_flag = false;
}
if(isset($_GET["h"])){
	$height = $_GET["h"];
}
else{
	$height = 128;
}

if($md5_flag){
	
	$json = file_get_contents("./json/".$md5.".json");
	$data = json_decode($json, true);
	echo '<title>'.$data["TITLE"].'</title>';
	
	if(isset($_GET["dbg"])){
		echo "<pre>";
		echo var_dump($data);
		echo "</pre>";
	}
	
	if($data["TITLE"] == null && $data["BPM"] == null){
		echo "このBMSファイルは存在しないか、壊れています。";
		exit();
	}
	
	$lgnote = $data["lgkey"];
	$lnnote = $data["lnkey"];
	
	$measure_len = $data["measure_length"];

	echo $data["TITLE"].", ";
	echo $data["GENRE"].", ";
	echo $data["ARTIST"].", ";

	for($i = 0; $i < count($data["difficulty"]); $i++){
		echo $data["difficulty"][$i].", ";
	}

	echo $data["notes"]."notes, ";
	echo $data["BPM"]."bpm";

	// 半角スペースのエスケープ

	echo '<a href="https://twitter.com/share" class="twitter-share-button" data-lang="ja">ツイート</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';
	// draw table:
	echo "<br /><table><tbody><tr>";

	for($i = 0; $i < ($data["measure"] + 1) / 4; $i++){
		echo '<td valign="bottom">';
		
		// if exist measure num 000
		if(0){
			$j = 3;
		}
		else{
			$j = 4;
		}
		
		for(; $j >= 1; $j--){
			$now_measure = ($i * 4 + $j);
			if($now_measure > $data["measure"])
				continue;
				
			if($measure_len[$now_measure] != null){
				$height_multiply = $measure_len[$now_measure];
			}
			else{
				$height_multiply = 1;
			}
			echo '<table cellpadding="0" cellspacing="0" width="168" style="border:1px white solid" height="'.$height * $height_multiply.'">';
			echo '<tbody><tr><td width="134" valign="top"><div style="width:134px;height:'.$height * $height_multiply.'">';
			
			for($bi = 3; $bi > 0; $bi--){
				echo '<img src="./pic/bar.gif" style="top:'.($height * $height_multiply * ($bi / 4) - 2).'px;left:0px">';
			}
			// draw score:

			for($key_num = 0; $key_num <= 7; $key_num++){
				for($mi = 0; $mi < count($lgnote[$key_num][$now_measure]); $mi++){
					
					//calc width:
					$scr_left = 0;
					// scr pic width:36px, key_width:13px
					$left = 28 + 1 + 13 * $key_num;

					//calc height:
					$top_pos = $lgnote[$key_num][$now_measure][$mi];
					//pic height:4px
					$top = (($height * $top_pos) * $height_multiply) - 5;
					
					// scr
					if($key_num == 0)
						echo '<img src="./pic/scr.gif" style="top:'.$top.'px;left:'.$scr_left.'px">';
					// white
					else if((int)$key_num % 2 == 1)
						echo '<img src="./pic/white.gif" style="top:'.$top.'px;left:'.$left.'px">';
					// blue
					else if((int)$key_num % 2 == 0)
						echo '<img src="./pic/blue.gif" style="top:'.$top.'px;left:'.$left.'px">';
				}
				
				for($mi = 0; $mi < count($lnnote[$key_num][$now_measure]); $mi++){
				
					
				}
			}

			// end draw score

			echo '</div></td>';
			// draw measure
			echo '<th width="32" bgcolor="gray">';
			if($height * $height_multiply >= 24){
				echo $now_measure;
			}
			
			echo '</th></tr></tbody></table>';
		}
		echo '</td>';
	}

	echo "</tr></tbody></table>";
}
else{
	echo "md5 is not specified.";
}
?>

</body>
</html>