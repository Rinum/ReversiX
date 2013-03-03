<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<link type="text/css" rel="stylesheet" href="/css/style.css">
	<title>ReversiX.us - Reversi to the eXtreme!</title>

	<!-- Base -->
	<script src="/js/jquery-1.7.min.js"></script>
	<!--
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	-->
	
	<!-- Module Dependencies -->
	<script type="text/javascript" src="/ratchet/js/when/when.js"></script>
	<script type="text/javascript" src="/ratchet/js/autobahn/autobahn.js"></script>
	
	<!-- Modules -->
	<script type="text/javascript" src="/js/Prime/Modules/WAMP.js"></script>
	<script type="text/javascript" src="/js/Prime/Modules/xdate.js"></script>
	
	<!-- Libs -->
	<script type="text/javascript" src="/js/Prime/Libs/Polyfills.js"></script>
	<script type="text/javascript" src="/js/Prime/Libs/Prime.js"></script>
	
	<!-- Controllers -->
	<script type="text/javascript" src="/js/Prime/Controllers/PrimeController.js"></script>
	<script type="text/javascript" src="/js/Prime/Controllers/FPSController.js"></script>
	<script type="text/javascript" src="/js/Prime/Controllers/BoardController.js"></script>
	<script type="text/javascript" src="/js/Prime/Controllers/RankingController.js"></script>
	<script type="text/javascript" src="/js/Prime/Controllers/LobbyController.js"></script>
	<script type="text/javascript" src="/js/Prime/Controllers/PlayerController.js"></script>
	
	<!-- Objects -->
	<script type="text/javascript" src="/js/Prime/Objects/FPS.js"></script>
	<script type="text/javascript" src="/js/Prime/Objects/Board.js"></script>
	<script type="text/javascript" src="/js/Prime/Objects/Ranking.js"></script>
	<script type="text/javascript" src="/js/Prime/Objects/Lobby.js"></script>
	<script type="text/javascript" src="/js/Prime/Objects/Pieces.js"></script>
	<script type="text/javascript" src="/js/Prime/Objects/Player.js"></script>	
	
	<!-- Custom Lib Extensions -->
	<script type="text/javascript" src="/js/ReversiX.js"></script>
</head>
<body>
<div id="wrapper">
	<div id="content" align="center">
		<div id="canvascontainer">			
			<?php echo $content_for_layout; ?>
	        </div>
		<div id="footer">
			<span class="c0 normal f16 s1">&copy; 2012 RiversiX.us - Created By <a style="color:skyblue" href="http://rinum.com">Rinum</a></span>
		</div>
    	</div>
</div>
</body>
</html>