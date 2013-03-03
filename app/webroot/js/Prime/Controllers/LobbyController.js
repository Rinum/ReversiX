LobbyController = function(parent){
	this.parent = parent;
	this.init();
}

LobbyController.prototype.init = function(){
	//Set controller viewport
	this.viewport = this.parent.viewport;
	
	//Create lobby object
	this.lobby_object = new Lobby(this);
	
	//Add object to Prime
	this.lobby_object_id = this.parent.add_object(this.lobby_object);
	
	//Initial logic
	this.lobby_drawn = false;
	this.lobby_undrawn = false;
}

LobbyController.prototype.is_playing = function(){
	//Is the user on this board?
	var is_playing = false;
	var players = this.parent.data.GameUser;
	
	for(var i in players){
		if(players[i]['user_id'] == this.parent.user_id){
			is_playing = true;
			break;
		}
	}
	
	if(is_playing){
		this.lobby_object.draw = false;
		this.lobby_object.undraw = true;
		this.lobby_undrawn = true;
	}
}

LobbyController.prototype.run = function(time){
	if(this.lobby_drawn)
		this.lobby_object.draw = false;
	
	this.lobby_drawn = true;
	
	if(this.lobby_undrawn)
		this.lobby_object.undraw = false;
	
	this.is_playing();
}

LobbyController.prototype.play_now = function(){
	//User wants to play a game
	Game.wamp.call('/API/games/join',{GameId:Game.data.Game.id});
}