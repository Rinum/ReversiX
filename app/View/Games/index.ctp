<div style="position:absolute;z-index:2;width:185px;height:235px;top:418px;margin-left:660px;background:rgba(0,0,0,0.5);border:#0C1526 solid 1px;border-radius:5px;" id="chatbox" align="left">	
	<div style="padding:5px;height:190px;overflow-y:auto;color:#FFFFFF;word-wrap:break-word;" id="messages">
		<div style="text-align: center;font-size:14px;font-weight: bold;margin-bottom: 10px;" class="message">
			You are on board <span style="color:#b4bdff;">Reversi</span><span style="color:#ffcc00;">X.us</span>/<span style="color:#FF6600;"><?php echo $game['Game']['name'];?></span>
		</div>
		<?php
			if(@$game['Game']['ended']){
				$board = json_decode($game['Game']['board']);
				$counts = array();

				foreach($board as $j){
					foreach($j as $k){
						if($k){
							$counts[$k] = (isset($counts[$k]) ? $counts[$k] : 0) + 1;
						}
					}
				}

				$winner = 0;
				$max = 0;
				foreach($counts as $key => $c){
					if($c > $max){
						$winner = $key;
						$max = $c;
					}
				}
	
				$username = '';
				
				foreach($game['GameUser'] as $u){
					if($u['user_id'] == $winner){
						$username = $u['User']['username'];
						break;
					}
				}
				
				if($username)
					echo '<div style="color:yellowgreen;margin-top:5px;margin-bottom:5px;font-weight:bold;text-align:center;font-size:14px;">Game over, '.$username.' wins!</div>';

				echo '<div style="text-align:center;"><a href="http://reversix.us" style="color:skyblue;margin-top:5px;margin-bottom:5px;font-weight:bold;font-size:14px;">Find a new game?</a></div>';
			}
		?>
	</div>
	<input id="chatinput" style="position:absolute;color:#ffffff;bottom:0px;background:rgba(255,255,255,0.25);font-size:16px;width:170px;height:20px;padding: 8px;border-bottom-left-radius: 5px;border-bottom-right-radius: 5px;border:none;" type="text" placeholder="Chat Here; Press Enter." value="" />	
</div>
<div id="viewport" style="width:840px;height:641px;position:relative"></div>

<script type="text/javascript">
$(document).ready(function(){
	//cheap way to get user clock offset
	offset = new XDate(true).getTime() - 1000 * <?php echo microtime(true); ?>;
	
	//create game
	Game = new ReversiX();
	Game.user_id = <?php echo $this->Session->read('User.id');?>;
	Game.set_data(<?php echo json_encode($game);?>);
	
	//change url
	history.pushState(null,null,'/<?php echo $game['Game']['name'];?>');
	
	//load WAMP module
	Game.wamp = new WAMP("ws://reversix.us:8080");
	Game.wamp.subscribe('<?php echo $name;?>',function(topic, event){
		switch(event.type){
			case 'chat':
				var scroll = false;
				var div = document.getElementById('messages');
				if(div.scrollTop >= div.scrollHeight - div.clientHeight - 10)
					scroll = true;
				
				if(event.user){
					var username = event.user.username;
					var user_id = event.user.id;
					
					//is this user in the game?
					var p = Game.BoardController.players[user_id];
					
					if(p){
						event.data = '<span style="color:'+p.fillStyle+'" class="player">'+username+':</span> ' + event.data;
					} else {
						event.data = '<span class="nonplayer">'+username+':</span> ' + event.data;
					}
				}
				
				var msg = $('<div>').addClass('message').html(event.data);
				$('#messages').append(msg);
				
				if(scroll)
					div.scrollTop = div.scrollHeight;
				break;
			case 'game':
				Game.set_data(event.data);
				break;
		}
	});
	Game.wamp.call('/API/games/update',{GameId:Game.data.Game.id},function(){},true);
	
	//start gameloop
	Game.run();
	
	$('#chatinput').keyup(function(e){
		if(e.keyCode == 13) {
			Game.wamp.call('/API/games/chat',{name:'<?php echo $name; ?>',data:$(this).val()});
			$(this).val('');
		}
	});
	
	$('#chatinput').focus();
});	
</script>