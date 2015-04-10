<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
  </head>
  
  <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
        <!-- ヘッダ情報 -->
        <div class="navbar-header">
            <a class="navbar-brand" href="#">BMScore</a>
        </div>
        <!-- リストの配置 -->
        <ul class="nav navbar-nav">
            <li><a href="upload.html">Upload</a></li>
            <li><a href="#">Insane</a></li>
        </ul>
        
    <p class="navbar-text">Search</p>
        <form class="navbar-form">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="キーワード">
            </div>
            <button type="submit" class="btn btn-info">検索</button>
        </form>
        
    </div>
  </nav>
  
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    
    <style type="text/css">
	<!-- div.s,span.s,div.s img.s{position:absolute;}td,th{font-size:16px;}body{color:white;background:black;padding-top:70px;} -->
	</style>

<body>

<div class="container-fluid">

<?php
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
	
	// calc longnotes
	for($key_num = 0; $key_num <= 7; $key_num++){
		for($ci = 0; $ci < count($data["lnkey"][$key_num]); $ci++){
			if($ci % 2 == 0){
				$ln_start = $data["lnkey"][$key_num][$ci];
				$ln_start_flag = !$ln_start_flag;
			}
			else{
				$ln_end = $data["lnkey"][$key_num][$ci];
				
				$lnnote[$key_num][(int)$ln_start][]["start_pos"] = $ln_start - (int)$ln_start;
				$lnnote[$key_num][(int)$ln_start][]["length"] = $ln_end - $ln_start;
			}
		}
	}
	
	$lgnote = $data["lgkey"];
	
	$measure_len = $data["measure_length"];


	// draw header data:
	
	echo $data["TITLE"].", ";
	echo $data["GENRE"].", ";
	echo $data["ARTIST"].", ";

	for($i = 0; $i < count($data["difficulty"]); $i++){
		echo $data["difficulty"][$i].", ";
	}

	echo $data["notes"]."notes, ";
	echo $data["BPM"]."bpm";

	echo '<a href="https://twitter.com/share" class="twitter-share-button" data-lang="ja">ツイート</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';

	// end draw header data

	// draw table:
	echo "<br /><table><tbody><tr>";
	
	
	for($i = 0; $i < ($data["measure"] + 1) / 4; $i++){
		echo '<td class="s" valign="bottom" style="padding-top: 5px; padding-right: 5px;">';
		
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
			echo '<tbody><tr><td class="s" width="134" valign="top"><div class="s" style="width:134px;height:'.$height * $height_multiply.'">';
			
			for($bi = 3; $bi > 0; $bi--){
				echo '<img class="s" src="./pic/bar.gif" style="top:'.($height * $height_multiply * ($bi / 4) - 2).'px;left:0px">';
			}
			// draw score:

			for($key_num = 0; $key_num <= 7; $key_num++){
				// legacy notes
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
						echo '<img class="s" src="./pic/scr.gif" style="top:'.$top.'px;left:'.$scr_left.'px">';
					// white
					else if((int)$key_num % 2 == 1)
						echo '<img class="s" src="./pic/white.gif" style="top:'.$top.'px;left:'.$left.'px">';
					// blue
					else if((int)$key_num % 2 == 0)
						echo '<img class="s" src="./pic/blue.gif" style="top:'.$top.'px;left:'.$left.'px">';
				}
				// long notes
				
			}

			// end draw score

			echo '</div></td>';
			// draw measure
			echo '<th class="s" width="32" bgcolor="gray">';
			if($height * $height_multiply >= 24){
				echo '<center>'.$now_measure.'</center>';
			}
			
			echo '</th></tr></tbody></table>';
		}
		echo '</td>';
	}

	echo "</tr></tbody></table>";
	
	// end draw table
}
else{
	echo "md5 is not specified.";
}
?>
</div>
</body>
</html>