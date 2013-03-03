BoardController = function(parent){
	this.parent = parent;
	this.init();
}

BoardController.prototype.init = function(){
	/* Vars */
	this.board_drawn = false;
	this.board = {
		rows: 16,
		cols: 16
	}
	this.tile = {
		width: 40,
		height: 40,
		padding: 1
	}
	this.pieces = [];
	for(var i = 0; i < this.board.rows; i++){
		this.pieces[i] = [];
		
		for(var j = 0; j < this.board.cols; j++){
			this.pieces[i][j] = 0;
		}
	}
	this.num_players = 0;
	this.players = {};
	//this.colors = ["#B93B8F","#666666","#0087BD","#ff6600"];
	this.colors = ["#7E587E","#999999","#0087BD","#F88017"];
	
	//Set controller viewport
	this.viewport = this.parent.viewport;
	
	/* Board background */
	this.board_object = new Board(this);
	this.board_object_id = this.parent.add_object(this.board_object);
	
	/* Board pieces */
	this.pieces_object = new Pieces(this);
	this.pieces_object_id = this.parent.add_object(this.pieces_object);
	
	/* Players */	
	this.ranking_controller = new RankingController(this);
	this.parent.add_controller(this.ranking_controller);
}

BoardController.prototype.run = function(time){
	if(this.board_object.draw && this.board_drawn){
		this.board_object.draw = false;
	}
}

BoardController.prototype.set_players = function(players){
	//Make sure all players exist in this.players
	for(var i in players){
		var p = players[i];
		
		if(!this.players[p.user_id]){
			//create user
			this.players[p.user_id] = new PlayerController(this,p.User);
			this.players[p.user_id].fillStyle = this.colors[this.num_players];
			
			if(p.user_id == this.parent.user_id){
				this.players[p.user_id].init();
				this.parent.add_controller(this.players[p.user_id]);
			}
			
			this.num_players++;
		}
	}
}

BoardController.prototype.attack = function(x,y){
	Game.wamp.call('/API/games/attack',{GameId:Game.data.Game.id,x:x,y:y});
}