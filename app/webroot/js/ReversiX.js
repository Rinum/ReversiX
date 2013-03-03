ReversiX = Prime;

ReversiX.prototype.load = function(){	
	//Create initial controllers
	var controllers = [];
	controllers.push(new FPSController(this));
	this.BoardController = new BoardController(this);
	controllers.push(this.BoardController);
	controllers.push(new LobbyController(this));
	
	for(var i in controllers){
		this.add_controller(controllers[i]);
	}
}

ReversiX.prototype.set_data = function(data){
	this.data = data;
	this.BoardController.pieces = JSON.parse(this.data.Game.board);
	this.BoardController.set_players(this.data.GameUser);
}