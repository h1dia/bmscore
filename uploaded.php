<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>BMScore</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap.css" rel="stylesheet">
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
  </head>
  
  <nav class="navbar navbar-default">
    <div class="container">
        <!-- モバイル表示 -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle"
            data-toggle="collapse" data-target="#navbar-menu">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="./index.html">BMScore</a>
        </div>

        <!-- ヘッダ情報 -->
        <div class="collapse navbar-collapse" id="navbar-menu">
          
        <!-- リストの配置 -->
        <ul class="nav navbar-nav">
            <li><a href="upload.html">Upload</a></li>
            <li class="active"><a href="uploaded.php">List</a></li>
        </ul>
        
          <form class="navbar-form">
            <div class="form-group">
               <input type="text" class="form-control" placeholder="キーワード">
            </div>
            <button type="submit" class="btn btn-info">検索</button>
          </form>
          
        </div>
        
    </div>
  </nav>
  
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    
    <body>
    <div class=container>
        
        <h1>Score List</h1>
        
			<div  class="container">
			    <table class="table">
			    <thead>
			      <tr>
			        <th>Num</th>
			        <th>Title</th>
			        <th>Artist</th>
			      </tr>
			    </thead>
			    <tbody>
					<?php
					
					$dbtemp = file_get_contents("./db.json");
					$out = json_decode($dbtemp, true);
					
					for($i = count($out) - 1; $i >= 0; $i--){
						echo "<tr>";
						echo "<td>".($i + 1)."</td>";
						echo "<td><a href=\""."./score.php?md5=".$out[$i]["md5"]."\">".$out[$i]["TITLE"]."</a></td>";
						echo "<td>".$out[$i]["ARTIST"]."</td>";
						
						echo "</tr>";
					}
					
					?>
			    </tbody>
			  </table>
			  </div>
        
        
        <div class="row">
        <p class="alert"></p>
        </div>
        
        </div>
    </body>
</html>