<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <!-- Bootstrap -->
    <link href="css/score.css" rel="stylesheet">
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
    	
        <div class="navbar-header">
            <button type="button" class="navbar-toggle"
            data-toggle="collapse" data-target="#navbar-menu">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="./index.html">BMScore</a>
        </div>
    	
        <div class="collapse navbar-collapse" id="navbar-menu">
    	
        <!-- リストの配置 -->
        <ul class="nav navbar-nav">
            <li><a href="upload.html">Upload</a></li>
            <li><a href="#">Insane</a></li>
        </ul>
        
        <form class="navbar-form">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="キーワードを入力">
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
include_once('table.php');

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
	if(!file_exists("./json/".$md5.".json")){
		echo "この譜面はまだ登録されていません。";
		exit();
	}
	
	$json = file_get_contents("./json/".$md5.".json");
	$data = json_decode($json, true);
	
	echo '<title>'.$data["TITLE"].'</title>';
	
	if(isset($_GET["dbg"])){
		echo "<pre>";
		echo var_dump($data);
		echo "</pre>";
	}
	
	if($data["TITLE"] == null){
		echo "譜面が壊れているかもしれません。管理者に問い合わせるか、BMSファイルをアップロードし直してください。<br/>";
		echo "無効な譜面のmd5 : ".$md5;
		exit();
	}
	
	$lgnote = $data["lgkey"];
	$lnlength = $data["lnlength"];
	$measure_len = $data["measure_length"];

	// draw header data:
	$insane_difficulty = new DifficultyTable();
	
	echo $insane_difficulty->getDifficulty('./table/insane/', $md5)." ";
	echo '<span class="title">'.$data["TITLE"]."</span> ";
	echo $data["GENRE"].", ";
	echo $data["ARTIST"].", ";
	echo $data["notes"]."notes, ";
	echo $data["BPM"]."bpm";
	echo '<a href="https://twitter.com/share" class="twitter-share-button" data-lang="ja">ツイート</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';
	// end draw header data
	// draw table:
	echo "<br /><table><tbody><tr>";
	
	
	for($i = 0; $i < ($data["measure"] + 1) / 4; $i++){
		echo '<td class="s" valign="bottom" style="padding-top: 5px; padding-right: 5px;">';
		
		
		for($j = 4; $j >= 1; $j--){
			$now_measure = ($i * 4 + $j);
			
			// 現在位置が最大小節数よりオーバーしているときは描画しない
			if($now_measure > $data["measure"])
				continue;
			
			// 小数点変更命令処理
			if($measure_len[$now_measure] != null){
				$height_multiply = $measure_len[$now_measure];
			}
			else{
				$height_multiply = 1;
			}
			
			// テーブル描画
			echo '<table cellpadding="0" cellspacing="0" width="168" style="border:1px white solid" height="'.$height * $height_multiply.'">';
			echo '<tbody><tr><td class="s" width="134" valign="top"><div class="s" style="width:134px;height:'.$height * $height_multiply.'">';
			
			for($bi = 3; $bi > 0; $bi--){
				echo '<img class="s" src="./pic/bar.gif" style="top:'.($height * $height_multiply * ($bi / 4) - 2).'px;left:0px">';
			}
			
			// draw score:
			for($key_num = 0; $key_num <= 7; $key_num++){
				//calc width:
				$scr_left = 0;
				// scr pic width:36px, key_width:13px
				$left = 28 + 1 + 13 * $key_num;
				
				// long notes
				for($mi = 0; $mi < count($lnlength[$key_num][$now_measure]["start"]); $mi++){
					$top = (($height * $lnlength[$key_num][$now_measure]["start"][$mi]) * $height_multiply);
					
					// scr
					if($key_num == 0)
						echo '<img class="s" src="./pic/scr.gif" style="top:'.$top.'px;left:'.$scr_left.'px;height:'.($lnlength[$key_num][$now_measure]["end"][$mi] - $lnlength[$key_num][$now_measure]["start"][$mi]) * $height * $height_multiply.'px;width:33px">';
					// white
					else if((int)$key_num % 2 == 1)
						echo '<img class="s" src="./pic/white.gif" style="top:'.$top.'px;left:'.$left.'px;height:'.($lnlength[$key_num][$now_measure]["end"][$mi] - $lnlength[$key_num][$now_measure]["start"][$mi]) * $height * $height_multiply.'px;width:11px">';
					// blue
					else if((int)$key_num % 2 == 0)
						echo '<img class="s" src="./pic/blue.gif" style="top:'.$top.'px;left:'.$left.'px;height:'.($lnlength[$key_num][$now_measure]["end"][$mi] - $lnlength[$key_num][$now_measure]["start"][$mi]) * $height * $height_multiply.'px;width:11px">';
				}
				
				// legacy notes
				for($mi = 0; $mi < count($lgnote[$key_num][$now_measure]); $mi++){
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
	echo "md5が指定されていません。";
}
?>
</div>
</body>
</html>